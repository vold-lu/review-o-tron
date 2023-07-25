<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230725063602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gitlab_project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, gitlab_id INTEGER NOT NULL, teams_webhook_url VARCHAR(255) DEFAULT NULL, gitlab_secret_token VARCHAR(255) DEFAULT NULL, gitlab_label_opened VARCHAR(255) DEFAULT NULL, gitlab_label_approved VARCHAR(255) DEFAULT NULL, gitlab_label_unapproved VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BE261A7ADAC3051D ON gitlab_project (gitlab_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE gitlab_project');
    }
}
