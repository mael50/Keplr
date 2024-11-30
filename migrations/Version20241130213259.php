<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241130213259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rssfeed ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rssfeed ADD CONSTRAINT FK_D75B60BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D75B60BBA76ED395 ON rssfeed (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rssfeed DROP FOREIGN KEY FK_D75B60BBA76ED395');
        $this->addSql('DROP INDEX IDX_D75B60BBA76ED395 ON rssfeed');
        $this->addSql('ALTER TABLE rssfeed DROP user_id');
    }
}
