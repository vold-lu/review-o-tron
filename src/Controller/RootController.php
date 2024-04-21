<?php

namespace App\Controller;

use App\Entity\GitlabProject;
use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Event\MergeRequestRejected;
use App\Params\Event\MergeRequestUpdated;
use App\Params\Gitlab\MergeRequestAction;
use App\Params\Gitlab\MergeRequestEvent;
use App\Params\Gitlab\NoteEvent;
use App\Params\Gitlab\Project;
use App\Repository\GitlabProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher,
                                private readonly GitlabProjectRepository  $projectRepository,
                                private readonly string                   $defaultGitlabSecretToken)
    {
    }

    #[Route('/', name: 'app_root')]
    public function index(Request $request): Response
    {
        $data = $request->toArray();
        if (empty($data)) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $project = Project::fromJson($data['project']);

        // Validate that request is legit
        $gitlabProject = $this->projectRepository->findByGitlabId($project->id);

        $expectedGitlabSecretToken = $this->getExpectedGitlabSecretToken($gitlabProject);
        if ($request->headers->get('X-Gitlab-Token') !== $expectedGitlabSecretToken) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        // Delegate handling based on object type
        switch ($data['object_kind']) {
            case 'merge_request':
                $this->resolveMergeRequestEvent($data);
                break;
            case 'note':
                $this->resolveNoteEvent($data);
                break;
            default:
                break;
        }

        // Increase hits counter
        if ($gitlabProject !== null) {
            $gitlabProject->setHits($gitlabProject->getHits() + 1);
            $this->projectRepository->save($gitlabProject);
        }

        return new Response();
    }

    #[Route('/ping', name: 'ping')]
    public function ping(): Response
    {
        return new Response('ok');
    }

    private function resolveMergeRequestEvent(array $data): void
    {
        $mergeRequestEvent = MergeRequestEvent::fromJson($data);

        // Handle webhook events
        $event = match ($mergeRequestEvent->object_attributes->action) {
            MergeRequestAction::OPEN => MergeRequestOpened::fromEvent($mergeRequestEvent),
            MergeRequestAction::CLOSE => MergeRequestClosed::fromEvent($mergeRequestEvent),
            MergeRequestAction::APPROVED => MergeRequestApproved::fromEvent($mergeRequestEvent),
            MergeRequestAction::MERGE => MergeRequestMerged::fromEvent($mergeRequestEvent),
            MergeRequestAction::UPDATE => MergeRequestUpdated::fromEvent($mergeRequestEvent),
            default => null,
        };

        if ($event !== null) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    private function resolveNoteEvent(array $data): void
    {
        $noteEvent = NoteEvent::fromJson($data);
        if (!$noteEvent->merge_request->blocking_discussions_resolved) {
            $this->eventDispatcher->dispatch(MergeRequestRejected::fromEvent($noteEvent));
        }
    }

    private function getExpectedGitlabSecretToken(?GitlabProject $gitlabProject): string
    {
        if ($gitlabProject === null) {
            return $this->defaultGitlabSecretToken;
        }

        return $gitlabProject->getGitlabSecretToken() ?? $this->defaultGitlabSecretToken;
    }
}
