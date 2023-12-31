<?php

namespace App\Tests\Service\Listener;

use App\Entity\GitlabProject;
use App\Listener\TagMergeRequestListener;
use App\Params\Event\MergeRequestApproved;
use App\Params\Event\MergeRequestClosed;
use App\Params\Event\MergeRequestMerged;
use App\Params\Event\MergeRequestOpened;
use App\Params\Event\MergeRequestRejected;
use App\Params\Event\MergeRequestUpdated;
use App\Params\Gitlab\MergeRequestEvent;
use App\Params\Gitlab\NoteEvent;
use App\Repository\GitlabProjectRepository;
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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

    public function testDraftMergeRequestOpenedWithEmptyRule()
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

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testDraftMergeRequestOpenedWithRuleAndNoDraftTag()
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testDraftMergeRequestOpenedWithRuleAndDraftTag()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Draft'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelDraft('Draft');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestOpened(
            MergeRequestOpened::fromEvent(MergeRequestEvent::fromJson($json))
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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

    public function testMergeRequestApprovedWithRuleButAlreadyRejected()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/approved-merge-request.json"), true);
        $json['object_attributes']['blocking_discussions_resolved'] = false;
        $json['object_attributes']['labels'] = [
            [
                'title' => 'Rejected'
            ],
        ];

        $listener->onMergeRequestApproved(
            MergeRequestApproved::fromEvent(MergeRequestEvent::fromJson($json))
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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
            42518399, 1, ['labels' => 'Rejected'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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
            42518399, 209, ['labels' => 'Rejected'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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

    public function testMergeRequestUpdatedWithRuleAlreadyOpened()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);

        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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

    public function testMergeRequestUpdatedWithRuleNotOpened()
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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
                'title' => 'Rejected'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithRuleConflictsNotResolved()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['blocking_discussions_resolved'] = false;
        $json['object_attributes']['labels'] = [
            [
                'title' => 'Rejected'
            ],
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedAfterApproval()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

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
                'title' => 'Approved'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedAfterApprovalWithSizeLabels()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->exactly(1))
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('changes')
            ->willReturn([
                'changes' => [
                    [
                        'diff' => "\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+"
                    ]
                ],
            ]);

        $mergeRequestsMock->expects($this->never())->method('update');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabId(42)
            ->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelSmallChanges('Size|S')
            ->setGitlabLabelMediumChanges('Size|M')
            ->setGitlabLabelLargeChanges('Size|L')
            ->setGitlabLabelExtraLargeChanges('Size|XL');

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
                'title' => 'Approved'
            ],
            [
                'title' => 'Size|S'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithRuleInDraftAlreadyInDraft()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);

        $gitlabClientMock->expects($this->never())
            ->method('mergeRequests');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelDraft('Draft');

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
                'title' => 'Draft'
            ]
        ];
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithRuleInDraftNotAlreadyInDraft()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Draft'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelDraft('Draft');

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
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithRuleStillInDraftNoDraftConfigured()
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
        $gitlabProject->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['work_in_progress'] = true;

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithOnlyRuleSize()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->exactly(2))
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('changes')
            ->willReturn([
                'changes' => [
                    [
                        'diff' => "\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+"
                    ]
                ],
            ]);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Size|S'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabId(42)
            ->setGitlabLabelSmallChanges('Size|S')
            ->setGitlabLabelMediumChanges('Size|M')
            ->setGitlabLabelLargeChanges('Size|L')
            ->setGitlabLabelExtraLargeChanges('Size|XL');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithSizeAndStatusRule()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->exactly(2))
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('changes')
            ->willReturn([
                'changes' => [
                    [
                        'diff' => "\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+"
                    ]
                ],
            ]);

        $mergeRequestsMock->expects($this->once())->method('update')->with(
            42518399, 1, ['labels' => 'Ready-For-Review,Size|S'],
        );

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabId(42)
            ->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelSmallChanges('Size|S')
            ->setGitlabLabelMediumChanges('Size|M')
            ->setGitlabLabelLargeChanges('Size|L')
            ->setGitlabLabelExtraLargeChanges('Size|XL');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }

    public function testMergeRequestUpdatedWithSizeAndStatusRuleRejected()
    {
        self::bootKernel();

        // Mock dependencies
        $gitlabClientMock = $this->createMock(Client::class);
        $mergeRequestsMock = $this->createMock(MergeRequests::class);
        $gitlabClientMock->expects($this->once())
            ->method('mergeRequests')
            ->willReturn($mergeRequestsMock);

        $mergeRequestsMock->expects($this->once())->method('changes')
            ->willReturn([
                'changes' => [
                    [
                        'diff' => "\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+\n+"
                    ]
                ],
            ]);

        $mergeRequestsMock->expects($this->never())->method('update');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabId(42)
            ->setGitlabLabelOpened('Ready-For-Review')
            ->setGitlabLabelApproved('Approved')
            ->setGitlabLabelRejected('Rejected')
            ->setGitlabLabelSmallChanges('Size|S')
            ->setGitlabLabelMediumChanges('Size|M')
            ->setGitlabLabelLargeChanges('Size|L')
            ->setGitlabLabelExtraLargeChanges('Size|XL');

        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        // Run actual test
        $listener = new TagMergeRequestListener($gitlabClientMock, $gitlabProjectRepositoryMock);

        $json = json_decode(file_get_contents("tests/Fixtures/updated-merge-request.json"), true);
        $json['object_attributes']['blocking_discussions_resolved'] = false;
        $json['object_attributes']['labels'] = [
            [
                'title' => 'Rejected'
            ],
            [
                'title' => 'Size|S'
            ]
        ];

        $listener->onMergeRequestUpdated(
            MergeRequestUpdated::fromEvent(MergeRequestEvent::fromJson($json))
        );
    }
}
