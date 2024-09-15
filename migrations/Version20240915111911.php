<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915111911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note DROP CONSTRAINT fk_cfbdfa1436aba6b8');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT fk_723705d136aba6b8');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE wallet_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE wallet (id INT NOT NULL, individual_id INT NOT NULL, year INT NOT NULL, month INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, currency VARCHAR(255) NOT NULL, start_balance NUMERIC(10, 2) NOT NULL, remaining_balance NUMERIC(10, 2) NOT NULL, spending_limit NUMERIC(10, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7C68921FAE271C0D ON wallet (individual_id)');
        $this->addSql('COMMENT ON COLUMN wallet.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN wallet.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FAE271C0D FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT fk_73f2f77bae271c0d');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP INDEX idx_cfbdfa1436aba6b8');
        $this->addSql('ALTER TABLE note RENAME COLUMN budget_id TO wallet_id');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CFBDFA14712520F3 ON note (wallet_id)');
        $this->addSql('DROP INDEX idx_723705d136aba6b8');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN budget_id TO wallet_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D1712520F3 ON transaction (wallet_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA14712520F3');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1712520F3');
        $this->addSql('DROP SEQUENCE wallet_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, individual_id INT NOT NULL, year INT NOT NULL, month INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, currency VARCHAR(255) NOT NULL, start_balance NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, remaining_balance NUMERIC(10, 2) NOT NULL, spending_limit NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_73f2f77bae271c0d ON budget (individual_id)');
        $this->addSql('COMMENT ON COLUMN budget.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN budget.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT fk_73f2f77bae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wallet DROP CONSTRAINT FK_7C68921FAE271C0D');
        $this->addSql('DROP TABLE wallet');
        $this->addSql('DROP INDEX IDX_CFBDFA14712520F3');
        $this->addSql('ALTER TABLE note RENAME COLUMN wallet_id TO budget_id');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT fk_cfbdfa1436aba6b8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_cfbdfa1436aba6b8 ON note (budget_id)');
        $this->addSql('DROP INDEX IDX_723705D1712520F3');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN wallet_id TO budget_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT fk_723705d136aba6b8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_723705d136aba6b8 ON transaction (budget_id)');
    }
}
