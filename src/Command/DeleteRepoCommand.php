<?php

namespace App\Command;

use App\Repository\GitlabProjectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'repo:rm',
    description: 'Delete configured Gitlab repository',
)]
class DeleteRepoCommand extends Command
{
    public function __construct(private readonly GitlabProjectRepository $projectRepository)
    {
        parent::__construct(self::getDefaultName());
    }

    public function configure(): void
    {
        $this->addArgument('project-id', InputArgument::REQUIRED, 'The Gitlab ID of the project to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $gitlabProject = $this->projectRepository->findByGitlabId($input->getArgument('project-id'));

        if ($gitlabProject === null) {
            $io->error('Gitlab project does not exists.');
            return Command::FAILURE;
        }

        // Display confirmation
        $io->horizontalTable(
            [
                'Gitlab ID',
                'MS Teams Webhook URL',
                'Opened MR label',
                'Approved MR label',
                'Rejected MR label'
            ],
            [
                [
                    $gitlabProject->getGitlabId(),
                    $gitlabProject->getTeamsWebhookUrl(),
                    $gitlabProject->getGitlabLabelOpened(),
                    $gitlabProject->getGitlabLabelApproved(),
                    $gitlabProject->getGitlabLabelRejected()
                ]
            ]
        );

        if (!$io->confirm('Dou you want to delete the project?', false)) {
            $io->info('Aborting');
            return Command::SUCCESS;
        }

        $this->projectRepository->delete($gitlabProject);

        $io->success('Repository settings has been deleted.');

        return Command::SUCCESS;
    }
}
