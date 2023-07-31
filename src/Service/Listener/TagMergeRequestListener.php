<?php

namespace App\Service\Listener;

use App\Params\Event\EventName;
use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Event\MergeRequestRejected;
use App\Params\Event\MergeRequestUpdated;
use App\Params\Gitlab\MergeRequest;
use App\Params\Gitlab\Project;
use App\Repository\GitlabProjectRepository;
use App\Service\Label\MergeRequestLabelService;
use App\Service\Label\MergeRequestStatusLabelService;
use Gitlab\Client;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class TagMergeRequestListener
{
    /** @var MergeRequestLabelService[] */
    private readonly array $labelServices;

    public function __construct(private readonly Client                  $gitlabClient,
                                private readonly GitlabProjectRepository $projectRepository)
    {
        $this->labelServices = [
            new MergeRequestStatusLabelService(),
        ];
    }

    #[AsEventListener(event: MergeRequestOpened::class)]
    public function onMergeRequestOpened(MergeRequestOpened $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::OPENED);
    }

    #[AsEventListener(event: MergeRequestApproved::class)]
    public function onMergeRequestApproved(MergeRequestApproved $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::APPROVED);
    }

    #[AsEventListener(event: MergeRequestMerged::class)]
    public function onMergeRequestMerged(MergeRequestMerged $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::MERGED);
    }

    #[AsEventListener(event: MergeRequestClosed::class)]
    public function onMergeRequestClosed(MergeRequestClosed $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::CLOSED);
    }

    #[AsEventListener(event: MergeRequestRejected::class)]
    public function onMergeRequestRejected(MergeRequestRejected $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::REJECTED);
    }

    #[AsEventListener(event: MergeRequestUpdated::class)]
    public function onMergeRequestUpdated(MergeRequestUpdated $event): void
    {
        $this->applyLabels($event->project, $event->mergeRequest, EventName::UPDATED);
    }

    private function applyLabels(Project $project, MergeRequest $mergeRequest, EventName $eventName): void
    {
        // Find project configuration
        $gitlabProject = $this->projectRepository->findByGitlabId($project->id);

        // Compute list of labels to apply
        $labels = [];
        foreach ($this->labelServices as $labelService) {
            $labels = $labels + $labelService->getLabels($gitlabProject, $mergeRequest, $eventName);
        }

        if (count($labels) === 0) {
            return;
        }

        // Apply labels (only if not same ones)
        $mergeRequestLabels = $mergeRequest->getLabelTitles();
        if ($mergeRequestLabels !== $labels) {
            $this->gitlabClient->mergeRequests()->update($project->id, $mergeRequest->iid, [
                'labels' => join(',', $labels)
            ]);
        }
    }
}