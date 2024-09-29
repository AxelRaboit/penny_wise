<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240929054041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add use_default_color column to transaction_tag table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE transaction_tag ADD use_default_color BOOLEAN NOT NULL DEFAULT 'true'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction_tag DROP use_default_color');
    }
}
