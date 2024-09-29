<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240929094329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove default value from individual_id column in transaction table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ALTER individual_id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ALTER individual_id SET DEFAULT 23');
    }
}
