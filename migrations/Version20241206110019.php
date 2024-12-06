<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206110019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video ADD channel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C72F5A1AA FOREIGN KEY (channel_id) REFERENCES youtube_channel (id)');
        $this->addSql('CREATE INDEX IDX_7CC7DA2C72F5A1AA ON video (channel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C72F5A1AA');
        $this->addSql('DROP INDEX IDX_7CC7DA2C72F5A1AA ON video');
        $this->addSql('ALTER TABLE video DROP channel_id');
    }
}
