<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102102318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE participant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE talk_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE participant (id INT NOT NULL, talk_id INT NOT NULL, messenger_id INT NOT NULL, is_deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D79F6B116F0601D5 ON participant (talk_id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11676C7AF5 ON participant (messenger_id)');
        $this->addSql('CREATE TABLE talk (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116F0601D5 FOREIGN KEY (talk_id) REFERENCES talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11676C7AF5 FOREIGN KEY (messenger_id) REFERENCES messenger (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE participant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE talk_id_seq CASCADE');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT FK_D79F6B116F0601D5');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT FK_D79F6B11676C7AF5');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE talk');
        $this->addSql('CREATE SEQUENCE messenger_id_seq');
        $this->addSql('SELECT setval(\'messenger_id_seq\', (SELECT MAX(id) FROM messenger))');
        $this->addSql('ALTER TABLE messenger ALTER id SET DEFAULT nextval(\'messenger_id_seq\')');
    }
}
