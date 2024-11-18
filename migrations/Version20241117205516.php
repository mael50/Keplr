<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117205516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `release` ADD github_repo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `release` ADD CONSTRAINT FK_9E47031DB23C03A9 FOREIGN KEY (github_repo_id) REFERENCES github_repository (id)');
        $this->addSql('CREATE INDEX IDX_9E47031DB23C03A9 ON `release` (github_repo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `release` DROP FOREIGN KEY FK_9E47031DB23C03A9');
        $this->addSql('DROP INDEX IDX_9E47031DB23C03A9 ON `release`');
        $this->addSql('ALTER TABLE `release` DROP github_repo_id');
    }
}
