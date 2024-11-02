<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102102849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE participant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE talk_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE messenger_participant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE messenger_talk_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE messenger_participant (id INT NOT NULL, talk_id INT NOT NULL, messenger_id INT NOT NULL, is_deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1192D01A6F0601D5 ON messenger_participant (talk_id)');
        $this->addSql('CREATE INDEX IDX_1192D01A676C7AF5 ON messenger_participant (messenger_id)');
        $this->addSql('CREATE TABLE messenger_talk (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE messenger_participant ADD CONSTRAINT FK_1192D01A6F0601D5 FOREIGN KEY (talk_id) REFERENCES messenger_talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger_participant ADD CONSTRAINT FK_1192D01A676C7AF5 FOREIGN KEY (messenger_id) REFERENCES messenger (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT fk_d79f6b116f0601d5');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT fk_d79f6b11676c7af5');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE talk');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE messenger_participant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE messenger_talk_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE participant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE talk_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE participant (id INT NOT NULL, talk_id INT NOT NULL, messenger_id INT NOT NULL, is_deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d79f6b11676c7af5 ON participant (messenger_id)');
        $this->addSql('CREATE INDEX idx_d79f6b116f0601d5 ON participant (talk_id)');
        $this->addSql('CREATE TABLE talk (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT fk_d79f6b116f0601d5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT fk_d79f6b11676c7af5 FOREIGN KEY (messenger_id) REFERENCES messenger (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger_participant DROP CONSTRAINT FK_1192D01A6F0601D5');
        $this->addSql('ALTER TABLE messenger_participant DROP CONSTRAINT FK_1192D01A676C7AF5');
        $this->addSql('DROP TABLE messenger_participant');
        $this->addSql('DROP TABLE messenger_talk');
    }
}
