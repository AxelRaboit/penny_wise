<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016172557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE user_settings_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_settings DROP CONSTRAINT fk_5c844c5ae271c0d');
        $this->addSql('DROP TABLE user_settings');
        $this->addSql('ALTER TABLE user_information DROP CONSTRAINT fk_8062d116ae271c0d');
        $this->addSql('DROP INDEX uniq_8062d116ae271c0d');
        $this->addSql('ALTER TABLE user_information RENAME COLUMN individual_id TO user_id');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT FK_8062D116A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8062D116A76ED395 ON user_information (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE user_settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_settings (id INT NOT NULL, individual_id INT NOT NULL, is_side_menu_collapse BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_5c844c5ae271c0d ON user_settings (individual_id)');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT fk_5c844c5ae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_information DROP CONSTRAINT FK_8062D116A76ED395');
        $this->addSql('DROP INDEX UNIQ_8062D116A76ED395');
        $this->addSql('ALTER TABLE user_information RENAME COLUMN user_id TO individual_id');
        $this->addSql('ALTER TABLE user_information ADD CONSTRAINT fk_8062d116ae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8062d116ae271c0d ON user_information (individual_id)');
    }
}
