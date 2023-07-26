<?php

namespace App\Service\Listener;

use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Event\MergeRequestRejected;
use App\Params\Event\MergeRequestUpdated;
use App\Repository\GitlabProjectRepository;
use Gitlab\Client;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class TagMergeRequestListener
{
    public function __construct(private readonly Client                  $gitlabClient,
                                 private readonly GitlabProjectRepository $projectRepository)
    {
    }

    #[AsEventListener(event: MergeRequestOpened::class)]
    public function onMergeRequestOpened(MergeRequestOpened $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject !== null && $gitlabProject->getGitlabLabelOpened() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelOpened());
        }
    }

    #[AsEventListener(event: MergeRequestApproved::class)]
    public function onMergeRequestApproved(MergeRequestApproved $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject !== null && $gitlabProject->getGitlabLabelApproved() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelApproved());
        }
    }

    #[AsEventListener(event: MergeRequestMerged::class)]
    public function onMergeRequestMerged(MergeRequestMerged $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject !== null && $gitlabProject->getGitlabLabelApproved() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelApproved());
        }
    }

    #[AsEventListener(event: MergeRequestClosed::class)]
    public function onMergeRequestClosed(MergeRequestClosed $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject !== null && $gitlabProject->getGitlabLabelRejected() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelRejected());
        }
    }

    #[AsEventListener(event: MergeRequestRejected::class)]
    public function onMergeRequestRejected(MergeRequestRejected $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject !== null && $gitlabProject->getGitlabLabelRejected() !== null) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelRejected());
        }
    }

    #[AsEventListener(event: MergeRequestUpdated::class)]
    public function onMergeRequestUpdated(MergeRequestUpdated $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if ($gitlabProject === null) {
            return;
        }

        if ($event->mergeRequest->blocking_discussions_resolved && $gitlabProject->getGitlabLabelOpened()) {
            $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelOpened());
        }
    }

    /**
     * Set merge request current label to given one.
     *
     * @param int $projectId the id of the gitlab project
     * @param int $mergeRequestIid the id of the merge request
     * @param string $label the label to apply
     * @return void
     */
    private function applyLabel(int $projectId, int $mergeRequestIid, string $label): void
    {
        $this->gitlabClient->mergeRequests()->update($projectId, $mergeRequestIid, ['labels' => $label]);
    }
}