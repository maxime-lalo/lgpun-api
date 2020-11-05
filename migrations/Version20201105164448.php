<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201105164448 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE party_player');
        $this->addSql('DROP INDEX UNIQ_89954EE077153098 ON party');
        $this->addSql('ALTER TABLE party ADD turn_id INT DEFAULT NULL, DROP last_turn, DROP number_of_players, DROP started, DROP turn');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE01F4F9889 FOREIGN KEY (turn_id) REFERENCES player (id)');
        $this->addSql('CREATE INDEX IDX_89954EE01F4F9889 ON party (turn_id)');
        $this->addSql('ALTER TABLE player ADD party_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('CREATE INDEX IDX_98197A65213C1059 ON player (party_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party_player (party_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_DE6F013C213C1059 (party_id), INDEX IDX_DE6F013C99E6F5DF (player_id), PRIMARY KEY(party_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE01F4F9889');
        $this->addSql('DROP INDEX IDX_89954EE01F4F9889 ON party');
        $this->addSql('ALTER TABLE party ADD last_turn VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD number_of_players INT NOT NULL, ADD started TINYINT(1) NOT NULL, ADD turn VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP turn_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_89954EE077153098 ON party (code)');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65213C1059');
        $this->addSql('DROP INDEX IDX_98197A65213C1059 ON player');
        $this->addSql('ALTER TABLE player DROP party_id');
    }
}
