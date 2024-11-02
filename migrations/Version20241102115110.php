<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102115110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE messenger_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE messenger_message (id INT NOT NULL, talk_id INT NOT NULL, sender_id INT NOT NULL, content TEXT NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F9F0C976F0601D5 ON messenger_message (talk_id)');
        $this->addSql('CREATE INDEX IDX_8F9F0C97F624B39D ON messenger_message (sender_id)');
        $this->addSql('COMMENT ON COLUMN messenger_message.sent_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messenger_message ADD CONSTRAINT FK_8F9F0C976F0601D5 FOREIGN KEY (talk_id) REFERENCES messenger_talk (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE messenger_message ADD CONSTRAINT FK_8F9F0C97F624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE messenger_message_id_seq CASCADE');
        $this->addSql('ALTER TABLE messenger_message DROP CONSTRAINT FK_8F9F0C976F0601D5');
        $this->addSql('ALTER TABLE messenger_message DROP CONSTRAINT FK_8F9F0C97F624B39D');
        $this->addSql('DROP TABLE messenger_message');
    }
}
