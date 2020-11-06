<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201106162018 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party ADD doppel_card_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE0611EBC29 FOREIGN KEY (doppel_card_id) REFERENCES card (id)');
        $this->addSql('CREATE INDEX IDX_89954EE0611EBC29 ON party (doppel_card_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE0611EBC29');
        $this->addSql('DROP INDEX IDX_89954EE0611EBC29 ON party');
        $this->addSql('ALTER TABLE party DROP doppel_card_id');
    }
}
