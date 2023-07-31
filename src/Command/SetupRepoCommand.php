<?php

namespace App\Command;

use App\Entity\GitlabProject;
use App\Repository\GitlabProjectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'repo:add',
    description: 'Setup a Gitlab repository',
)]
class SetupRepoCommand extends Command
{
    public function __construct(private readonly GitlabProjectRepository $projectRepository)
    {
        parent::__construct(self::getDefaultName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $gitlabProject = new GitlabProject();
        $gitlabProject->setGitlabId(intval($io->ask('Gitlab project ID')));

        if (empty($gitlabProject->getGitlabId())) {
            $io->error('Gitlab project ID should be an integer');
            return Command::FAILURE;
        }

        $previousGitlabProject = $this->projectRepository->findByGitlabId($gitlabProject->getGitlabId());

        if ($previousGitlabProject !== null) {
            $io->warning('Gitlab project is already registered. Reconfiguring.');
            $gitlabProject = $previousGitlabProject;
        }

        $gitlabProject->setName(
            $io->ask('Project display name', $gitlabProject->getName())
        );
        $gitlabProject->setTeamsWebhookUrl(
            $io->ask('MS Teams Webhook URL', $gitlabProject->getTeamsWebhookUrl())
        );
        $gitlabProject->setGitlabLabelOpened(
            $io->ask('Name of the label to apply on opened MR', $gitlabProject->getGitlabLabelOpened())
        );
        $gitlabProject->setGitlabLabelDraft(
            $io->ask('Name of the label to apply on draft MR', $gitlabProject->getGitlabLabelDraft())
        );
        $gitlabProject->setGitlabLabelApproved(
            $io->ask('Name of the label to apply on approved MR', $gitlabProject->getGitlabLabelApproved())
        );
        $gitlabProject->setGitlabLabelRejected(
            $io->ask('Name of the label to apply on rejected MR', $gitlabProject->getGitlabLabelRejected())
        );
        $gitlabProject->setGitlabLabelSmallChanges(
            $io->ask('Name of the label to apply on small MR', $gitlabProject->getGitlabLabelSmallChanges())
        );
        $gitlabProject->setGitlabLabelMediumChanges(
            $io->ask('Name of the label to apply on medium MR', $gitlabProject->getGitlabLabelMediumChanges())
        );
        $gitlabProject->setGitlabLabelLargeChanges(
            $io->ask('Name of the label to apply on large MR', $gitlabProject->getGitlabLabelLargeChanges())
        );
        $gitlabProject->setGitlabLabelExtraLargeChanges(
            $io->ask('Name of the label to apply on extra large MR', $gitlabProject->getGitlabLabelExtraLargeChanges())
        );

        // Display confirmation
        $io->horizontalTable(
            [
                'Name',
                'Gitlab ID',
                'MS Teams Webhook URL',
                'Opened MR label',
                'Draft MR label',
                'Approved MR label',
                'Rejected MR label',
                'Small changes label',
                'Medium changes label',
                'Large changes label',
                'Extra large changes label',
            ],
            [
                [
                    $gitlabProject->getName(),
                    $gitlabProject->getGitlabId(),
                    $gitlabProject->getTeamsWebhookUrl(),
                    $gitlabProject->getGitlabLabelOpened(),
                    $gitlabProject->getGitlabLabelDraft(),
                    $gitlabProject->getGitlabLabelApproved(),
                    $gitlabProject->getGitlabLabelRejected(),
                    $gitlabProject->getGitlabLabelSmallChanges(),
                    $gitlabProject->getGitlabLabelMediumChanges(),
                    $gitlabProject->getGitlabLabelLargeChanges(),
                    $gitlabProject->getGitlabLabelExtraLargeChanges(),
                ]
            ]
        );

        if (!$io->confirm('Dou you want to apply the current settings?', false)) {
            $io->info('Aborting');
            return Command::SUCCESS;
        }

        $this->projectRepository->save($gitlabProject);

        $io->success('Repository settings has been saved. You can now register your webhook on Gitlab');

        return Command::SUCCESS;
    }
}
