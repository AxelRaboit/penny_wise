<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915143558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_information_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_information (id INT NOT NULL, user_id INT NOT NULL, avatar_name VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8062D116A76ED395 ON user_information (user_id)');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT FK_8062D116A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD user_information_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP avatar');
        $this->addSql('ALTER TABLE "user" DROP avatar_name');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6494575EE58 FOREIGN KEY (user_information_id) REFERENCES user_information (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6494575EE58 ON "user" (user_information_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6494575EE58');
        $this->addSql('DROP SEQUENCE user_information_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_information DROP CONSTRAINT FK_8062D116A76ED395');
        $this->addSql('DROP TABLE user_information');
        $this->addSql('DROP INDEX UNIQ_8D93D6494575EE58');
        $this->addSql('ALTER TABLE "user" ADD avatar VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD avatar_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP user_information_id');
    }
}
