<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003170355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change wallet account_id to not null';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wallet ALTER COLUMN account_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE wallet ALTER COLUMN account_id DROP NOT NULL');
    }
}
