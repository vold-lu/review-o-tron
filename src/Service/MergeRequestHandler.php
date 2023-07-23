<?php

namespace App\Service;

use App\Params\Event\MergeRequestOpened;

/**
 * The merge request handler. It is where the core logic of the application is stored.
 */
class MergeRequestHandler
{
    public function __construct(private readonly MicrosoftTeamsConnector $teamsConnector)
    {
    }

    public function handleOpened(MergeRequestOpened $params): void
    {
        // Dispatch Microsoft Teams notification
        $this->teamsConnector->sendMessage(
            sprintf('%s opened a new merge request', $params->user->name),
            sprintf('On project %s', $params->project->name),
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png'
        );
    }
}