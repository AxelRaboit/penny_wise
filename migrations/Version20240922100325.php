<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922100325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction_transaction_tag (transaction_id INT NOT NULL, transaction_tag_id INT NOT NULL, PRIMARY KEY(transaction_id, transaction_tag_id))');
        $this->addSql('CREATE INDEX IDX_98EEC8972FC0CB0F ON transaction_transaction_tag (transaction_id)');
        $this->addSql('CREATE INDEX IDX_98EEC897CCAF1151 ON transaction_transaction_tag (transaction_tag_id)');
        $this->addSql('ALTER TABLE transaction_transaction_tag ADD CONSTRAINT FK_98EEC8972FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transaction (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction_transaction_tag ADD CONSTRAINT FK_98EEC897CCAF1151 FOREIGN KEY (transaction_tag_id) REFERENCES transaction_tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction_transaction_tag DROP CONSTRAINT FK_98EEC8972FC0CB0F');
        $this->addSql('ALTER TABLE transaction_transaction_tag DROP CONSTRAINT FK_98EEC897CCAF1151');
        $this->addSql('DROP TABLE transaction_transaction_tag');
    }
}
