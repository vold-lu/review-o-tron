<?php

namespace App\Listener\Release;

use App\Params\Event\Release\ReleaseCreated;
use App\Params\Gitlab\Project;
use App\Repository\GitlabProjectRepository;
use App\Service\MicrosoftTeamsConnector;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class NotifyReleaseListener
{
    public function __construct(private readonly MicrosoftTeamsConnector $teamsConnector,
                                private readonly GitlabProjectRepository $projectRepository,
                                private readonly string                  $defaultTeamsWebhookUrl)
    {
    }

    #[AsEventListener(event: ReleaseCreated::class)]
    public function onReleaseCreated(ReleaseCreated $event): void
    {
        $this->teamsConnector->sendMessage(
            $this->getTeamsWebhookUrl($event->project),
            sprintf('%s released %s "%s"', $event->user->name, $event->project->name, $event->tag),
            $event->description,
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            '#27ae60',
            [],
            $this->buildActions($event->url)
        );
    }

    private function buildActions(string $releaseUrl): array
    {
        return [
            [
                '@type' => 'OpenUri',
                'name' => 'View online',
                'targets' => [
                    [
                        'os' => 'default',
                        'uri' => $releaseUrl,
                    ]
                ]
            ]
        ];
    }

    // TODO service?
    private function getTeamsWebhookUrl(Project $project): string
    {
        $gitlabProject = $this->projectRepository->findByGitlabId($project->id);
        if ($gitlabProject === null) {
            return $this->defaultTeamsWebhookUrl;
        }

        return $gitlabProject->getTeamsWebhookUrl() ?? $this->defaultTeamsWebhookUrl;
    }
}