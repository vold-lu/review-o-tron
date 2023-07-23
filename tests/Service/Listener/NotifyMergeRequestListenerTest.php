<?php

namespace App\Tests\Service\Listener;

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
            'Aloïs Micard opened a new merge request',
            'On project Demo Repository',
            'https://about.gitlab.com/images/press/logo/png/gitlab-logo-500.png',
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
                    'name' => 'Add a comment',
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
}
