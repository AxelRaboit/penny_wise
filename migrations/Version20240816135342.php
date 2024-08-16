<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240816135342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT fk_723705d112469de2');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP INDEX idx_723705d112469de2');
        $this->addSql('ALTER TABLE transaction DROP category_id');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN category_custom TO category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE transaction ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN category TO category_custom');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT fk_723705d112469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_723705d112469de2 ON transaction (category_id)');
    }
}
