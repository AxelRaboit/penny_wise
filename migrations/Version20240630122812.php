<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240630122812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, individual_id INT NOT NULL, year INT NOT NULL, month INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, currency VARCHAR(255) NOT NULL, start_balance NUMERIC(10, 2) NOT NULL, left_to_spend NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73F2F77BAE271C0D ON budget (individual_id)');
        $this->addSql('COMMENT ON COLUMN budget.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN budget.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BAE271C0D FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BAE271C0D');
        $this->addSql('DROP TABLE budget');
    }
}
