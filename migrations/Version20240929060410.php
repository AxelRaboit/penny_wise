<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240929060410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a relation between transaction and user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction ADD individual_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1AE271C0D FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D1AE271C0D ON transaction (individual_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1AE271C0D');
        $this->addSql('DROP INDEX IDX_723705D1AE271C0D');
        $this->addSql('ALTER TABLE transaction DROP individual_id');
    }
}
