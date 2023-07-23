<?php

namespace App\Service\Listener;

use App\Params\Event\MergeRequestOpened;
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
        $facts = $this->buildFacts($event);

        $this->teamsConnector->sendMessage(
            sprintf('%s opened a new merge request', $event->user->name),
            sprintf('On project %s', $event->project->name),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            $facts
        );
    }

    private function buildFacts(MergeRequestOpened $event): array
    {
        $facts = [];

        if (count($event->assignees) > 0) {
            $facts[] = [
                'name' => 'Assigned to',
                'value' => $event->assignees[0]->name
            ];
        }
        if (count($event->reviewers) > 0) {
            $facts[] = [
                'name' => 'Reviewer',
                'value' => $event->reviewers[0]->name
            ];
        }

        return $facts;
    }
}