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
    public function onMergeRequestOpened(MergeRequestOpened $params): void
    {
        // Dispatch Microsoft Teams notification
        $facts = [];

        if (count($params->assignees) > 0) {
            $facts[] = [
                'name' => 'Assigned to',
                'value' => $params->assignees[0]->name
            ];
        }
        if (count($params->reviewers) > 0) {
            $facts[] = [
                'name' => 'Reviewer',
                'value' => $params->reviewers[0]->name
            ];
        }

        $this->teamsConnector->sendMessage(
            sprintf('%s opened a new merge request', $params->user->name),
            sprintf('On project %s', $params->project->name),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            $facts
        );
    }
}