<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702100850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Coaches DROP INDEX idx_club_id, ADD UNIQUE INDEX UNIQ_BAE2EF961190A32 (club_id)');
        $this->addSql('ALTER TABLE Coaches MODIFY idCoaches INT NOT NULL');
        $this->addSql('ALTER TABLE Coaches DROP FOREIGN KEY fk_club_id_c');
        $this->addSql('DROP INDEX `primary` ON Coaches');
        $this->addSql('ALTER TABLE Coaches CHANGE nombre nombre VARCHAR(45) NOT NULL, CHANGE idCoaches id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Coaches ADD CONSTRAINT FK_BAE2EF961190A32 FOREIGN KEY (club_id) REFERENCES Teams (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Coaches ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE Players DROP INDEX idx_club_id, ADD UNIQUE INDEX UNIQ_E9F37A3A61190A32 (club_id)');
        $this->addSql('ALTER TABLE Players MODIFY idPlayers INT NOT NULL');
        $this->addSql('ALTER TABLE Players DROP FOREIGN KEY fk_club_id_p');
        $this->addSql('DROP INDEX `primary` ON Players');
        $this->addSql('ALTER TABLE Players CHANGE idPlayers id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Players ADD CONSTRAINT FK_E9F37A3A61190A32 FOREIGN KEY (club_id) REFERENCES Teams (id)');
        $this->addSql('ALTER TABLE Players ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE Teams MODIFY idTeams INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON Teams');
        $this->addSql('ALTER TABLE Teams CHANGE idTeams id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Teams ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Coaches DROP INDEX UNIQ_BAE2EF961190A32, ADD INDEX idx_club_id (club_id)');
        $this->addSql('ALTER TABLE Coaches MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE Coaches DROP FOREIGN KEY FK_BAE2EF961190A32');
        $this->addSql('DROP INDEX `PRIMARY` ON Coaches');
        $this->addSql('ALTER TABLE Coaches CHANGE nombre nombre VARCHAR(100) NOT NULL, CHANGE id idCoaches INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Coaches ADD CONSTRAINT fk_club_id_c FOREIGN KEY (club_id) REFERENCES Teams (idTeams) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Coaches ADD PRIMARY KEY (idCoaches)');
        $this->addSql('ALTER TABLE Players DROP INDEX UNIQ_E9F37A3A61190A32, ADD INDEX idx_club_id (club_id)');
        $this->addSql('ALTER TABLE Players MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE Players DROP FOREIGN KEY FK_E9F37A3A61190A32');
        $this->addSql('DROP INDEX `PRIMARY` ON Players');
        $this->addSql('ALTER TABLE Players CHANGE id idPlayers INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Players ADD CONSTRAINT fk_club_id_p FOREIGN KEY (club_id) REFERENCES Teams (idTeams) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Players ADD PRIMARY KEY (idPlayers)');
        $this->addSql('ALTER TABLE Teams MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON Teams');
        $this->addSql('ALTER TABLE Teams CHANGE id idTeams INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Teams ADD PRIMARY KEY (idTeams)');
    }
}
