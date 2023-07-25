<?php

namespace App\Tests\Controller;

use App\Entity\GitlabProject;
use App\Repository\GitlabProjectRepository;
use App\Service\Listener\NotifyMergeRequestListener;
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
        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

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
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

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

        $this->getContainer()->set(NotifyMergeRequestListener::class, $notifyMergeRequestListenerMock);
        $this->getContainer()->set(GitlabProjectRepository::class, $gitlabProjectRepositoryMock);

        // Mock existing override
        $notifyMergeRequestListenerMock->expects($this->once())->method('onMergeRequestOpened');

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
}
