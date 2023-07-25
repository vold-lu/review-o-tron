<?php

namespace App\Entity;

use App\Repository\GitlabProjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitlabProjectRepository::class)]
class GitlabProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $gitlab_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teams_webhook_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_secret_token = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlab_id;
    }

    public function setGitlabId(int $gitlab_id): static
    {
        $this->gitlab_id = $gitlab_id;

        return $this;
    }

    public function getTeamsWebhookUrl(): ?string
    {
        return $this->teams_webhook_url;
    }

    public function setTeamsWebhookUrl(?string $teams_webhook_url): static
    {
        $this->teams_webhook_url = $teams_webhook_url;

        return $this;
    }

    public function getGitlabSecretToken(): ?string
    {
        return $this->gitlab_secret_token;
    }

    public function setGitlabSecretToken(?string $gitlab_secret_token): static
    {
        $this->gitlab_secret_token = $gitlab_secret_token;

        return $this;
    }
}
