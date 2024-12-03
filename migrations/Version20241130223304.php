<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241130223304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE github_repository ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE github_repository ADD CONSTRAINT FK_7A865C28A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7A865C28A76ED395 ON github_repository (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE github_repository DROP FOREIGN KEY FK_7A865C28A76ED395');
        $this->addSql('DROP INDEX IDX_7A865C28A76ED395 ON github_repository');
        $this->addSql('ALTER TABLE github_repository DROP user_id');
    }
}
