<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915212333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note ADD expiration TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d6494575ee58');
        $this->addSql('DROP INDEX uniq_8d93d6494575ee58');
        $this->addSql('ALTER TABLE "user" DROP user_information_id');
        $this->addSql('ALTER TABLE user_information ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN user_information.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_information ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN user_information.updated_at IS NULL');
        $this->addSql('ALTER TABLE note DROP expiration');
        $this->addSql('ALTER TABLE "user" ADD user_information_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d6494575ee58 FOREIGN KEY (user_information_id) REFERENCES user_information (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d6494575ee58 ON "user" (user_information_id)');
    }
}
