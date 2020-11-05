<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105225749 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party_not_used_card (party_id INT NOT NULL, not_used_card_id INT NOT NULL, INDEX IDX_E715FD7B213C1059 (party_id), INDEX IDX_E715FD7B883911A3 (not_used_card_id), PRIMARY KEY(party_id, not_used_card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE party_not_used_card ADD CONSTRAINT FK_E715FD7B213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_not_used_card ADD CONSTRAINT FK_E715FD7B883911A3 FOREIGN KEY (not_used_card_id) REFERENCES not_used_card (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE party_not_used_card');
    }
}
