<?php

namespace App\Service\Listener;

use App\Entity\GitlabProject;
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
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelOpened());
    }

    #[AsEventListener(event: MergeRequestApproved::class)]
    public function onMergeRequestApproved(MergeRequestApproved $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelApproved());
    }

    #[AsEventListener(event: MergeRequestMerged::class)]
    public function onMergeRequestMerged(MergeRequestMerged $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelApproved());
    }

    #[AsEventListener(event: MergeRequestClosed::class)]
    public function onMergeRequestClosed(MergeRequestClosed $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelRejected());
    }

    #[AsEventListener(event: MergeRequestRejected::class)]
    public function onMergeRequestRejected(MergeRequestRejected $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelRejected());
    }

    #[AsEventListener(event: MergeRequestUpdated::class)]
    public function onMergeRequestUpdated(MergeRequestUpdated $event): void
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($event->project->id);
        if (!$this->isTagFeatureEnabled($gitlabProject)) {
            return;
        }

        // Update can allow happens if merge request is ready to be merged, other case are handled by other handlers
        if (!$event->mergeRequest->blocking_discussions_resolved) {
            return;
        }

        // Prevent from updating the same tag in loop (settings tags call webhook again)
        // after onMergeRequestUpdated is run it will be fired again because of tag changes
        // therefore we need to determinate if the tag who want to apply is the same as current one, if so, discard
        $mergeRequestLabels = array_map(fn($label) => $label['title'], $event->mergeRequest->labels);
        if (in_array($gitlabProject->getGitlabLabelOpened(), $mergeRequestLabels)) {
            return;
        }

        // Prevent from running if event is triggered from approval request (onMergeRequestMerged)
        // after onMergeRequestMerged handler is run onMergeRequestUpdated will be fired because of tag changes
        // therefore we must be able to detect that tag being applied is the approved one, and therefore discard event
        if (in_array($gitlabProject->getGitlabLabelApproved(), $mergeRequestLabels)) {
            return;
        }

        $this->applyLabel($event->project->id, $event->mergeRequest->iid, $gitlabProject->getGitlabLabelOpened());
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

    /**
     * Determinate if tag feature is enabled for given gitlab project
     *
     * @param GitlabProject|null $gitlabProject the project to check for
     * @return bool true if the gitlab project use the tag feature. false otherwise
     */
    private function isTagFeatureEnabled(?GitlabProject $gitlabProject): bool
    {
        return $gitlabProject !== null &&
            $gitlabProject->getGitlabLabelOpened() &&
            $gitlabProject->getGitlabLabelApproved() &&
            $gitlabProject->getGitlabLabelRejected();
    }
}