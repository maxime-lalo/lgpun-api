<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201107122337 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD fake_turn_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE01C36DB8E FOREIGN KEY (fake_turn_id) REFERENCES card (id)');
        $this->addSql('CREATE INDEX IDX_89954EE01C36DB8E ON party (fake_turn_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE01C36DB8E');
        $this->addSql('DROP INDEX IDX_89954EE01C36DB8E ON party');
        $this->addSql('ALTER TABLE party DROP fake_turn_id');
    }
}
