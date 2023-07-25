<?php

namespace App\Service\Listener;

use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestOpened;
use App\Repository\GitlabProjectRepository;
use Gitlab\Client;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class TagMergeRequestListener
{
    private function __construct(private readonly Client                  $gitlabClient,
                                 private readonly GitlabProjectRepository $projectRepository)
    {
    }

    #[AsEventListener(event: MergeRequestOpened::class)]
    public function onMergeRequestOpened(MergeRequestOpened $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject->getGitlabLabelOpened() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->id, $gitlabProject->getGitlabLabelOpened());
        }
    }

    #[AsEventListener(event: MergeRequestApproved::class)]
    public function onMergeRequestApproved(MergeRequestApproved $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject->getGitlabLabelApproved() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->id, $gitlabProject->getGitlabLabelApproved());
        }
    }

    private function applyLabel(int $projectId, int $mergeRequestIid, string $label): void
    {
        $this->gitlabClient->mergeRequests()->update($projectId, $mergeRequestIid, ['labels' => [$label]]);
    }
}