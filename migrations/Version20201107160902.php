<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201107160902 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, target_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5A108564158E0B66 (target_id), UNIQUE INDEX UNIQ_5A108564A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_party (vote_id INT NOT NULL, party_id INT NOT NULL, INDEX IDX_D8FC2C2B72DCDAFC (vote_id), INDEX IDX_D8FC2C2B213C1059 (party_id), PRIMARY KEY(vote_id, party_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564158E0B66 FOREIGN KEY (target_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE vote_party ADD CONSTRAINT FK_D8FC2C2B72DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote_party ADD CONSTRAINT FK_D8FC2C2B213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote_party DROP FOREIGN KEY FK_D8FC2C2B72DCDAFC');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE vote_party');
    }
}
