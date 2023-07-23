<?php

namespace App\Tests\Service\Listener;

use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Gitlab\MergeRequestEvent;
use App\Service\Listener\NotifyMergeRequestListener;
use App\Service\MicrosoftTeamsConnector;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotifyMergeRequestListenerTest extends KernelTestCase
{
    public function testOnMergeRequestOpened(): void
    {
        self::bootKernel();

        // Mock dependencies
        $microsoftTeamConnectorMock = $this->getMockBuilder(MicrosoftTeamsConnector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendMessage'])
            ->getMock();

        // Perform assertions
        $microsoftTeamConnectorMock->expects($this->once())->method('sendMessage')->with(
            'Aloïs Micard opened MR "Update requirements.txt"',
            'creekorful/demo-repository: (devel) -> (main)',
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            '#2980b9',
            [
                [
                    'name' => 'Assigned to',
                    'value' => 'Aloïs Micard',
                ],
                [
                    'name' => 'Reviewer',
                    'value' => 'Aloïs Micard',
                ]
            ],
            [
                [
                    '@type' => 'OpenUri',
                    'name' => 'View online',
                    'targets' => [
                        [
                            'os' => 'default',
                            'uri' => 'https://gitlab.com/creekorful/demo-repository/-/merge_requests/1',
                        ]
                    ]
                ]
            ]
        );

        // Run actual test
        $listener = new NotifyMergeRequestListener($microsoftTeamConnectorMock);

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testOnMergeRequestMerged(): void
    {
        self::bootKernel();

        // Mock dependencies
        $microsoftTeamConnectorMock = $this->getMockBuilder(MicrosoftTeamsConnector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendMessage'])
            ->getMock();

        // Perform assertions
        $microsoftTeamConnectorMock->expects($this->once())->method('sendMessage')->with(
            'Aloïs Micard merged MR "Update requirements.txt"',
            'creekorful/demo-repository: (devel) -> (main)',
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
            '#27ae60',
            [],
            [
                [
                    '@type' => 'OpenUri',
                    'name' => 'View online',
                    'targets' => [
                        [
                            'os' => 'default',
                            'uri' => 'https://gitlab.com/creekorful/demo-repository/-/merge_requests/1',
                        ]
                    ]
                ]
            ]
        );

        // Run actual test
        $listener = new NotifyMergeRequestListener($microsoftTeamConnectorMock);

        $json = file_get_contents("tests/Fixtures/merged-merge-request.json");

        $listener->onMergeRequestMerged(
            MergeRequestMerged::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }
}
