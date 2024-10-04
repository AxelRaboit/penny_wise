<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003161610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE wallet ADD account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921F9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7C68921F9B6B5FBA ON wallet (account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE wallet DROP CONSTRAINT FK_7C68921F9B6B5FBA');
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP INDEX IDX_7C68921F9B6B5FBA');
        $this->addSql('ALTER TABLE wallet DROP account_id');
    }
}
