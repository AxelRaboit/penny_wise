<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922111852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction_tag ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction_tag ADD CONSTRAINT FK_F8CD024AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F8CD024AA76ED395 ON transaction_tag (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction_tag DROP CONSTRAINT FK_F8CD024AA76ED395');
        $this->addSql('DROP INDEX IDX_F8CD024AA76ED395');
        $this->addSql('ALTER TABLE transaction_tag DROP user_id');
    }
}
