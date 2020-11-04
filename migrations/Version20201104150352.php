<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104150352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party_card (party_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_9B75900213C1059 (party_id), INDEX IDX_9B759004ACC9A20 (card_id), PRIMARY KEY(party_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE party_player (party_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_DE6F013C213C1059 (party_id), INDEX IDX_DE6F013C99E6F5DF (player_id), PRIMARY KEY(party_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, beginning_card_id INT DEFAULT NULL, ending_card_id INT DEFAULT NULL, id_firebase VARCHAR(255) NOT NULL, pseudo VARCHAR(255) NOT NULL, INDEX IDX_98197A6564FFE1AA (beginning_card_id), INDEX IDX_98197A658197CD3D (ending_card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE party_card ADD CONSTRAINT FK_9B75900213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_card ADD CONSTRAINT FK_9B759004ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6564FFE1AA FOREIGN KEY (beginning_card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A658197CD3D FOREIGN KEY (ending_card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE party ADD creator_id INT NOT NULL, ADD cards_hidden TINYINT(1) NOT NULL, ADD code VARCHAR(255) NOT NULL, ADD last_turn VARCHAR(255) NOT NULL, ADD number_of_players INT NOT NULL, ADD started TINYINT(1) NOT NULL, ADD turn VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE061220EA6 FOREIGN KEY (creator_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_89954EE061220EA6 ON party (creator_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE061220EA6');
        $this->addSql('ALTER TABLE party_player DROP FOREIGN KEY FK_DE6F013C99E6F5DF');
        $this->addSql('DROP TABLE party_card');
        $this->addSql('DROP TABLE party_player');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP INDEX IDX_89954EE061220EA6 ON party');
        $this->addSql('ALTER TABLE party DROP creator_id, DROP cards_hidden, DROP code, DROP last_turn, DROP number_of_players, DROP started, DROP turn');
    }
}
