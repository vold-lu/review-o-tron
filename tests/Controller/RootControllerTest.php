<?php

namespace App\Tests\Controller;

use App\Entity\GitlabProject;
use App\Listener\NotifyMergeRequestListener;
use App\Listener\TagMergeRequestListener;
use App\Repository\GitlabProjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RootControllerTest extends WebTestCase
{
    public function testEmptyRequestFails(): void
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testRequestWithWrongSecretTokenAndNoOverrideFails(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->never())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->never())->method('onMergeRequestOpened');

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");
        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'foo',
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRequestWithGoodSecretTokenAndNoOverrideDispatchEvents(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

        $json = file_get_contents("tests/Fixtures/new-merge-request.json");
        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'test',
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testRequestWithWrongSecretTokenAndOverrideFails(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->never())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->never())->method('onMergeRequestOpened');

        // Mock existing override
        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabSecretToken('testtest');
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);


        $json = file_get_contents("tests/Fixtures/new-merge-request.json");
        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'test',
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testRequestWithGoodSecretTokenAndOverrideDispatchEvents(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabSecretToken('testtest');
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);


        $json = file_get_contents("tests/Fixtures/new-merge-request.json");
        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'testtest',
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNoteRequestWithGoodSecretTokenAndOverrideDontDispatchEventsIfResolved(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

        // Mock existing override
        $tagMergeRequestListenerMock->expects($this->never())->method('onMergeRequestRejected');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabSecretToken('testtest');
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);


        $json = json_decode(file_get_contents("tests/Fixtures/rejected-merge-request.json"), true);
        $json['merge_request']['blocking_discussions_resolved'] = true;

        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'testtest',
        ], json_encode($json));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNoteRequestWithGoodSecretTokenAndOverrideDispatchEvents(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $gitlabProjectRepositoryMock = $this->createMock(GitlabProjectRepository::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

        // Mock existing override
        $tagMergeRequestListenerMock->expects($this->once())->method('onMergeRequestRejected');

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabSecretToken('testtest');
        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('findByGitlabId')
            ->with(42518399)
            ->willReturn($gitlabProject);

        $gitlabProjectRepositoryMock->expects($this->once())
            ->method('save')
            ->with($gitlabProject);

        $this->assertEquals(0, $gitlabProject->getHits());

        $json = file_get_contents("tests/Fixtures/rejected-merge-request.json");
        $crawler = $client->request('POST', '/', [], [], [
            'HTTP_X_GITLAB_TOKEN' => 'testtest',
        ], $json);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertEquals(1, $gitlabProject->getHits());
    }

    public function testRequestWithNoReviewersSet(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

        $json = json_decode(file_get_contents("tests/Fixtures/new-merge-request.json"), true);
        unset($json['reviewers']);

        $crawler = $client->jsonRequest('POST', '/', $json, [
            'HTTP_X_GITLAB_TOKEN' => 'test',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testRequestWithNoAssigneesSet(): void
    {
        $client = static::createClient();

        // Mock dependencies
        $notifyMergeRequestListenerMock = $this->createMock(NotifyMergeRequestListener::class);
        $tagMergeRequestListenerMock = $this->createMock(TagMergeRequestListener::class);

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(TagMergeRequestListener::class, $tagMergeRequestListenerMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');
        $tagMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

        $json = json_decode(file_get_contents("tests/Fixtures/new-merge-request.json"), true);
        unset($json['assignees']);
        $json['object_attributes']['assignee_id'] = null;

        $crawler = $client->jsonRequest('POST', '/', $json, [
            'HTTP_X_GITLAB_TOKEN' => 'test',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPingEndpoint(): void
    {
        $client = static::createClient();

        $crawler = $client->request('OPTIONS', '/ping');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
