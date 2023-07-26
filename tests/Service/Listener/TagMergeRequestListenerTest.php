<?php

namespace App\Tests\Service\Listener;

use App\Entity\GitlabProject;
use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Event\MergeRequestRejected;
use App\Params\Event\MergeRequestUpdated;
use App\Params\Gitlab\MergeRequestEvent;
use App\Params\Gitlab\NoteEvent;
use App\Repository\GitlabProjectRepository;
use App\Service\Listener\TagMergeRequestListener;
use Gitlab\Api\MergeRequests;
use Gitlab\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TagMergeRequestListenerTest extends KernelTestCase
{
    public function testMergeRequestOpenedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestOpenedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestOpenedWithRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Ready-For-Review'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestApprovedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/approved-merge-request.json");

        $listener->onMergeRequestApproved(
            MergeRequestApproved::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestApprovedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/approved-merge-request.json");

        $listener->onMergeRequestApproved(
            MergeRequestApproved::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestApprovedWithRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Approved'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelApproved('Approved');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/approved-merge-request.json");

        $listener->onMergeRequestApproved(
            MergeRequestApproved::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestMergedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/merged-merge-request.json");

        $listener->onMergeRequestMerged(
            MergeRequestMerged::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestMergedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/merged-merge-request.json");

        $listener->onMergeRequestMerged(
            MergeRequestMerged::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestMergedWithRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Approved'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelApproved('Approved');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/merged-merge-request.json");

        $listener->onMergeRequestMerged(
            MergeRequestMerged::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestClosedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/closed-merge-request.json");

        $listener->onMergeRequestClosed(
            MergeRequestClosed::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestClosedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/closed-merge-request.json");

        $listener->onMergeRequestClosed(
            MergeRequestClosed::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestClosedWithRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Unapproved'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelRejected('Unapproved');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/closed-merge-request.json");

        $listener->onMergeRequestClosed(
            MergeRequestClosed::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestRejectedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/rejected-merge-request.json");

        $listener->onMergeRequestRejected(
            MergeRequestRejected::fromEvent(NoteEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestRejectedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/rejected-merge-request.json");

        $listener->onMergeRequestRejected(
            MergeRequestRejected::fromEvent(NoteEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestRejectedWithRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 209, ['labels' => 'Unapproved'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelRejected('Unapproved');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/rejected-merge-request.json");

        $listener->onMergeRequestRejected(
            MergeRequestRejected::fromEvent(NoteEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestUpdatedNoRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn(null);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/updated-merge-request.json");

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestUpdatedWithEmptyRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = file_get_contents("tests/Fixtures/updated-merge-request.json");

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson(json_decode($json, true)))
        );
    }

    public function testMergeRequestRUpdatedWithRuleAlreadyOpened()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelRejected('Unapproved');
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['labels'] = [
            [
                'title' => 'Ready-For-Review'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestRUpdatedWithRuleNotOpened()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Ready-For-Review'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelRejected('Unapproved');
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['labels'] = [
            [
                'title' => 'Unapproved'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }
}
