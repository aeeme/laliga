<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626125149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Coaches DROP FOREIGN KEY fk_club_id');
        $this->addSql('ALTER TABLE Players DROP FOREIGN KEY fk_players_teams');
        $this->addSql('DROP TABLE Coaches');
        $this->addSql('DROP TABLE Players');
        $this->addSql('DROP TABLE Teams');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Coaches (club_id INT DEFAULT NULL, idCoaches INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, salario DOUBLE PRECISION NOT NULL, INDEX idx_club_id (club_id), PRIMARY KEY(idCoaches)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Players (club_id INT DEFAULT NULL, idPlayers INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, salario NUMERIC(10, 2) NOT NULL, INDEX fk_players_teams (club_id), PRIMARY KEY(idPlayers)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE Teams (idTeams INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(45) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, presupuesto_actual NUMERIC(10, 2) NOT NULL, PRIMARY KEY(idTeams)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE Coaches ADD CONSTRAINT fk_club_id FOREIGN KEY (club_id) REFERENCES Teams (idTeams) ON UPDATE CASCADE ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Players ADD CONSTRAINT fk_players_teams FOREIGN KEY (club_id) REFERENCES Teams (idTeams) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
