<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * A Microsoft Teams connector used to abstract interaction with MS Teams.
 */
class MicrosoftTeamsConnector
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function sendMessage(
        string  $webhookUrl,
        string  $title,
        ?string $subtitle = null,
        ?string $imageUrl = null,
        ?string $themeColor = null,
        array   $facts = [],
        array   $actions = []
    ): void
    {
        $message = [
            '@type' => '',
            '@context' => '',
            'themeColor' => $themeColor,
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

        $this->client->request('POST', $webhookUrl, [
            'json' => $message
        ]);
    }
}