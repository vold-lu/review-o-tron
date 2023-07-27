<?php

namespace App\Command;

use App\Repository\GitlabProjectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-repo',
    description: 'List configured Gitlab repository',
)]
class ListRepoCommand extends Command
{
    public function __construct(private readonly GitlabProjectRepository $projectRepository)
    {
        parent::__construct(self::getDefaultName());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $gitlabProjects = $this->projectRepository->findAll();

        $rows = [];

        foreach ($gitlabProjects as $gitlabProject) {
            $rows []= [
                $gitlabProject->getId(),
                $gitlabProject->getName(),
                $gitlabProject->getGitlabId(),
                strlen($gitlabProject->getTeamsWebhookUrl() ?? '') > 45 ? substr($gitlabProject->getTeamsWebhookUrl(), 0, 45) . '...' : $gitlabProject->getTeamsWebhookUrl(),
                $gitlabProject->getGitlabLabelOpened(),
                $gitlabProject->getGitlabLabelApproved(),
                $gitlabProject->getGitlabLabelRejected(),
                $gitlabProject->getHits() ?? 0,
            ];
        }

        $io->table(
            [
                'Internal ID',
                'Name',
                'Gitlab ID',
                'MS Teams Webhook URL',
                'Opened MR label',
                'Approved MR label',
                'Rejected MR label',
                'Hits'
            ],
            $rows,
        );

        return Command::SUCCESS;
    }
}
