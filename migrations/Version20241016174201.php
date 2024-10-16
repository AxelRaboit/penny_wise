<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016174201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_settings (id INT NOT NULL, user_id INT NOT NULL, is_side_menu_collapse BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C844C5A76ED395 ON user_settings (user_id)');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT FK_5C844C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT fk_7d3656a4ae271c0d');
        $this->addSql('DROP INDEX idx_7d3656a4ae271c0d');
        $this->addSql('ALTER TABLE account RENAME COLUMN individual_id TO user_id');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7D3656A4A76ED395 ON account (user_id)');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT fk_723705d1ae271c0d');
        $this->addSql('DROP INDEX idx_723705d1ae271c0d');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN individual_id TO user_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)');
        $this->addSql('ALTER TABLE wallet DROP CONSTRAINT fk_7c68921fae271c0d');
        $this->addSql('DROP INDEX idx_7c68921fae271c0d');
        $this->addSql('ALTER TABLE wallet RENAME COLUMN individual_id TO user_id');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7C68921FA76ED395 ON wallet (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_settings_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_settings DROP CONSTRAINT FK_5C844C5A76ED395');
        $this->addSql('DROP TABLE user_settings');
        $this->addSql('ALTER TABLE wallet DROP CONSTRAINT FK_7C68921FA76ED395');
        $this->addSql('DROP INDEX IDX_7C68921FA76ED395');
        $this->addSql('ALTER TABLE wallet RENAME COLUMN user_id TO individual_id');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT fk_7c68921fae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7c68921fae271c0d ON wallet (individual_id)');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A4A76ED395');
        $this->addSql('DROP INDEX IDX_7D3656A4A76ED395');
        $this->addSql('ALTER TABLE account RENAME COLUMN user_id TO individual_id');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT fk_7d3656a4ae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_7d3656a4ae271c0d ON account (individual_id)');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1A76ED395');
        $this->addSql('DROP INDEX IDX_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction RENAME COLUMN user_id TO individual_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT fk_723705d1ae271c0d FOREIGN KEY (individual_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_723705d1ae271c0d ON transaction (individual_id)');
    }
}
