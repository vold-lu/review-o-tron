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
    private ?string $gitlab_label_rejected = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_draft = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_small_changes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_medium_changes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_large_changes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gitlab_label_extra_large_changes = null;

    #[ORM\Column(nullable: true)]
    private ?int $hits = null;

    public function __construct()
    {
        $this->hits = 0;
    }

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

    public function getGitlabLabelRejected(): ?string
    {
        return $this->gitlab_label_rejected;
    }

    public function setGitlabLabelRejected(?string $gitlab_label_rejected): static
    {
        $this->gitlab_label_rejected = $gitlab_label_rejected;

        return $this;
    }

    public function getGitlabLabelDraft(): ?string
    {
        return $this->gitlab_label_draft;
    }

    public function setGitlabLabelDraft(?string $gitlab_label_draft): static
    {
        $this->gitlab_label_draft = $gitlab_label_draft;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGitlabLabelSmallChanges(): ?string
    {
        return $this->gitlab_label_small_changes;
    }

    public function setGitlabLabelSmallChanges(?string $gitlab_label_small_changes): static
    {
        $this->gitlab_label_small_changes = $gitlab_label_small_changes;

        return $this;
    }

    public function getGitlabLabelMediumChanges(): ?string
    {
        return $this->gitlab_label_medium_changes;
    }

    public function setGitlabLabelMediumChanges(?string $gitlab_label_medium_changes): static
    {
        $this->gitlab_label_medium_changes = $gitlab_label_medium_changes;

        return $this;
    }

    public function getGitlabLabelLargeChanges(): ?string
    {
        return $this->gitlab_label_large_changes;
    }

    public function setGitlabLabelLargeChanges(?string $gitlab_label_large_changes): static
    {
        $this->gitlab_label_large_changes = $gitlab_label_large_changes;

        return $this;
    }

    public function getGitlabLabelExtraLargeChanges(): ?string
    {
        return $this->gitlab_label_extra_large_changes;
    }

    public function setGitlabLabelExtraLargeChanges(?string $gitlab_label_extra_large_changes): static
    {
        $this->gitlab_label_extra_large_changes = $gitlab_label_extra_large_changes;

        return $this;
    }

    public function getHits(): ?int
    {
        return $this->hits;
    }

    public function setHits(int $hits): static
    {
        $this->hits = $hits;

        return $this;
    }
}
