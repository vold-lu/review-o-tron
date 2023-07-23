<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * A Microsoft Teams connector used to abstract interaction with MS Teams.
 */
class MicrosoftTeamsConnector
{
    public function __construct(private readonly string $webhookUrl)
    {
    }

    public function sendMessage(
        string  $title,
        ?string $subtitle = null,
        ?string $imageUrl = null,
        array   $facts = [],
        array   $actions = []
    ): void
    {
        $message = [
            '@type' => '',
            '@context' => '',
            'themeColor' => '',
            'summary' => $title,
            'sections' => [
                [
                    'activityTitle' => $title,
                    'activitySubTitle' => $subtitle,
                    'activityImage' => $imageUrl,
                    'facts' => $facts
                ],
            ],
            'potentialAction' => $actions
        ];

        $client = new Client();
        $client->post($this->webhookUrl, [
            RequestOptions::JSON => $message
        ]);
    }
}