<?php

namespace App\Listener\MergeRequest;

use App\Params\Event\MergeRequest\MergeRequestMerged;
use App\Params\Event\MergeRequest\MergeRequestOpened;
use App\Params\Gitlab\MergeRequest;
use App\Params\Gitlab\Project;
use App\Repository\GitlabProjectRepository;
use App\Service\MicrosoftTeamsConnector;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class NotifyMergeRequestListener
{
    public function __construct(private readonly MicrosoftTeamsConnector $teamsConnector,
                                private readonly GitlabProjectRepository $projectRepository,
                                private readonly string                  $defaultTeamsWebhookUrl)
    {
    }

    #[AsEventListener(event: MergeRequestOpened::class)]
    public function onMergeRequestOpened(MergeRequestOpened $event): void
    {
        $this->teamsConnector->sendMessage(
            $this->getTeamsWebhookUrl($event->project),
            sprintf('%s opened MR "%s"', $event->user->name, $event->mergeRequest->title),
            sprintf('%s: (%s) -> (%s)', $event->project->path_with_namespace, $event->mergeRequest->source_branch, $event->mergeRequest->target_branch),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            '#2980b9',
            $this->buildFacts($event->assignees, $event->reviewers),
            $this->buildActions($event->mergeRequest)
        );
    }

    #[AsEventListener(event: MergeRequestMerged::class)]
    public function onMergeRequestMerged(MergeRequestMerged $event): void
    {
        $this->teamsConnector->sendMessage(
            $this->getTeamsWebhookUrl($event->project),
            sprintf('%s merged MR "%s"', $event->user->name, $event->mergeRequest->title),
            sprintf('%s: (%s) -> (%s)', $event->project->path_with_namespace, $event->mergeRequest->source_branch, $event->mergeRequest->target_branch),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            '#27ae60',
            [],
            $this->buildActions($event->mergeRequest)
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
                'name' => 'View online',
                'targets' => [
                    [
                        'os' => 'default',
                        'uri' => $mergeRequest->url,
                    ]
                ]
            ]
        ];
    }

    private function getTeamsWebhookUrl(Project $project): string
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($project->id);
        if ($gitlabProject === null) {
            return $this->defaultTeamsWebhookUrl;
        }

        return $gitlabProject->getTeamsWebhookUrl() ?? $this->defaultTeamsWebhookUrl;
    }
}