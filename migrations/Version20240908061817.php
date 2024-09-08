<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240908061817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link ADD individual_id INT NOT NULL');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F1AE271C0D FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_36AC99F1AE271C0D ON link (individual_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE link DROP CONSTRAINT FK_36AC99F1AE271C0D');
        $this->addSql('DROP INDEX IDX_36AC99F1AE271C0D');
        $this->addSql('ALTER TABLE link DROP individual_id');
    }
}
