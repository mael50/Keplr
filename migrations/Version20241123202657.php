<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241123202657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE push_subscription ADD user_id INT DEFAULT NULL, ADD expiration_time VARCHAR(255) DEFAULT NULL, ADD p256dh VARCHAR(255) NOT NULL, ADD auth VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP auth_key, DROP p256dh_key, CHANGE endpoint endpoint VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE push_subscription ADD CONSTRAINT FK_562830F3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_562830F3A76ED395 ON push_subscription (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE push_subscription DROP FOREIGN KEY FK_562830F3A76ED395');
        $this->addSql('DROP INDEX IDX_562830F3A76ED395 ON push_subscription');
        $this->addSql('ALTER TABLE push_subscription ADD auth_key VARCHAR(255) NOT NULL, ADD p256dh_key VARCHAR(255) NOT NULL, DROP user_id, DROP expiration_time, DROP p256dh, DROP auth, DROP created_at, CHANGE endpoint endpoint VARCHAR(255) NOT NULL');
    }
}
