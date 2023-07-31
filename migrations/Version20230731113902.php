<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731113902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gitlab_project ADD COLUMN gitlab_label_small_changes VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE gitlab_project ADD COLUMN gitlab_label_medium_changes VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE gitlab_project ADD COLUMN gitlab_label_large_changes VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE gitlab_project ADD COLUMN gitlab_label_extra_large_changes VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__gitlab_project AS SELECT id, gitlab_id, teams_webhook_url, gitlab_secret_token, gitlab_label_opened, gitlab_label_approved, gitlab_label_rejected, gitlab_label_draft, name, hits FROM gitlab_project');
        $this->addSql('DROP TABLE gitlab_project');
        $this->addSql('CREATE TABLE gitlab_project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, gitlab_id INTEGER NOT NULL, teams_webhook_url VARCHAR(255) DEFAULT NULL, gitlab_secret_token VARCHAR(255) DEFAULT NULL, gitlab_label_opened VARCHAR(255) DEFAULT NULL, gitlab_label_approved VARCHAR(255) DEFAULT NULL, gitlab_label_rejected VARCHAR(255) DEFAULT NULL, gitlab_label_draft VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, hits INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO gitlab_project (id, gitlab_id, teams_webhook_url, gitlab_secret_token, gitlab_label_opened, gitlab_label_approved, gitlab_label_rejected, gitlab_label_draft, name, hits) SELECT id, gitlab_id, teams_webhook_url, gitlab_secret_token, gitlab_label_opened, gitlab_label_approved, gitlab_label_rejected, gitlab_label_draft, name, hits FROM __temp__gitlab_project');
        $this->addSql('DROP TABLE __temp__gitlab_project');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BE261A7ADAC3051D ON gitlab_project (gitlab_id)');
    }
}
