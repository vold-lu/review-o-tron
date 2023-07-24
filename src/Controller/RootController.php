<?php

namespace App\Controller;

use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Gitlab\MergeRequestAction;
use App\Params\Gitlab\MergeRequestEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController extends AbstractController
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher,
                                private readonly string                   $gitlabSecretToken)
    {
    }

    #[Route('/', name: 'app_root')]
    public function index(Request $request): Response
    {
        // Validate that request is legit
        if ($request->headers->get('X-Gitlab-Token') !== $this->gitlabSecretToken) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->toArray();
        if (empty($data)) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        $mergeRequestEvent = MergeRequestEvent::fromJson($data);

        // Handle webhook events
        $event = $this->resolveEventFromAction($mergeRequestEvent);
        if ($event !== null) {
            $this->eventDispatcher->dispatch($event);
        }

        return new Response();
    }

    /**
     * Find which app event we should dispatch based on gitlab merge request event action
     *
     * @param MergeRequestEvent $event
     * @return MergeRequestApproved|MergeRequestClosed|MergeRequestMerged|MergeRequestOpened|null
     */
    private function resolveEventFromAction(MergeRequestEvent $event): MergeRequestApproved|MergeRequestMerged|MergeRequestClosed|MergeRequestOpened|null
    {
        return match ($event->object_attributes->action) {
            MergeRequestAction::OPEN => MergeRequestOpened::fromEvent($event),
            MergeRequestAction::CLOSE => MergeRequestClosed::fromEvent($event),
            MergeRequestAction::APPROVED => MergeRequestApproved::fromEvent($event),
            MergeRequestAction::MERGE => MergeRequestMerged::fromEvent($event),
            default => null,
        };
    }
}
