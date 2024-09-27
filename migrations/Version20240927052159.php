<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927052159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update transaction table to add highlight and budget_defined_trough_amount columns';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD highlight BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE transaction ALTER budget_defined_trough_amount SET DEFAULT true');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction DROP highlight');
        $this->addSql('ALTER TABLE transaction ALTER budget_defined_trough_amount DROP DEFAULT');
    }
}
