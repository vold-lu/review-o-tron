<?php

namespace App\Service\Listener;

use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Gitlab\MergeRequest;
use App\Service\MicrosoftTeamsConnector;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class NotifyMergeRequestListener
{
    public function __construct(private readonly MicrosoftTeamsConnector $teamsConnector)
    {
    }

    #[AsEventListener(event: MergeRequestOpened::class)]
    public function onMergeRequestOpened(MergeRequestOpened $event): void
    {
        $this->teamsConnector->sendMessage(
            sprintf('%s opened MR "%s"', $event->user->name, $event->mergeRequest->title),
            sprintf('%s: (%s) -> (%s)', $event->project->path_with_namespace, $event->mergeRequest->source_branch, $event->mergeRequest->target_branch),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            $this->buildFacts($event->assignees, $event->reviewers),
            $this->buildActions($event->mergeRequest)
        );
    }

    #[AsEventListener(event: MergeRequestMerged::class)]
    public function onMergeRequestMerged(MergeRequestMerged $event): void
    {
        $this->teamsConnector->sendMessage(
            sprintf('%s merged MR "%s"', $event->user->name, $event->mergeRequest->title),
            sprintf('%s: (%s) -> (%s)', $event->project->path_with_namespace, $event->mergeRequest->source_branch, $event->mergeRequest->target_branch),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
        );
    }

    private function buildFacts(array $assignees, array $reviewers): array
    {
        $facts = [];

        if (count($assignees) > 0) {
            $facts[] = [
                'name' => 'Assigned to',
                'value' => $assignees[0]->name
            ];
        }
        if (count($reviewers) > 0) {
            $facts[] = [
                'name' => 'Reviewer',
                'value' => $reviewers[0]->name
            ];
        }

        return $facts;
    }

    private function buildActions(MergeRequest $mergeRequest): array
    {
        return [
            [
                '@type' => 'OpenUri',
                'name' => 'Add a comment',
                'targets' => [
                    [
                        'os' => 'default',
                        'uri' => $mergeRequest->url,
                    ]
                ]
            ]
        ];
    }
}