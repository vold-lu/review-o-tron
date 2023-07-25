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

    #[ORM\Column(unique: true)]
    private ?int $gitlab_id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $teams_webhook_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_secret_token = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_opened = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_approved = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_unapproved = null;

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

    public function getGitlabLabelOpened(): ?string
    {
        return $this->gitlab_label_opened;
    }

    public function setGitlabLabelOpened(?string $gitlab_label_opened): static
    {
        $this->gitlab_label_opened = $gitlab_label_opened;

        return $this;
    }

    public function getGitlabLabelApproved(): ?string
    {
        return $this->gitlab_label_approved;
    }

    public function setGitlabLabelApproved(?string $gitlab_label_approved): static
    {
        $this->gitlab_label_approved = $gitlab_label_approved;

        return $this;
    }

    public function getGitlabLabelUnapproved(): ?string
    {
        return $this->gitlab_label_unapproved;
    }

    public function setGitlabLabelUnapproved(?string $gitlab_label_unapproved): static
    {
        $this->gitlab_label_unapproved = $gitlab_label_unapproved;

        return $this;
    }
}
