<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241101122142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE login_attempt_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE login_attempt (id INT NOT NULL, user_id INT NOT NULL, attempts INT NOT NULL, last_attempt_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_blocked BOOLEAN NOT NULL, blocked_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C11C1BA76ED395 ON login_attempt (user_id)');
        $this->addSql('ALTER TABLE login_attempt ADD CONSTRAINT FK_8C11C1BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE login_attempt_id_seq CASCADE');
        $this->addSql('ALTER TABLE login_attempt DROP CONSTRAINT FK_8C11C1BA76ED395');
        $this->addSql('DROP TABLE login_attempt');
    }
}
