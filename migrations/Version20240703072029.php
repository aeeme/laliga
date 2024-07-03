<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240703072029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Coaches (id INT AUTO_INCREMENT NOT NULL, club_id INT DEFAULT NULL, nombre VARCHAR(45) NOT NULL, salario DOUBLE PRECISION NOT NULL, email VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_BAE2EF961190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Players (id INT AUTO_INCREMENT NOT NULL, club_id INT DEFAULT NULL, nombre VARCHAR(100) NOT NULL, salario DOUBLE PRECISION NOT NULL, email VARCHAR(100) NOT NULL, INDEX IDX_E9F37A3A61190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Teams (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, presupuesto_actual DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Coaches ADD CONSTRAINT FK_BAE2EF961190A32 FOREIGN KEY (club_id) REFERENCES Teams (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE Players ADD CONSTRAINT FK_E9F37A3A61190A32 FOREIGN KEY (club_id) REFERENCES Teams (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Coaches DROP FOREIGN KEY FK_BAE2EF961190A32');
        $this->addSql('ALTER TABLE Players DROP FOREIGN KEY FK_E9F37A3A61190A32');
        $this->addSql('DROP TABLE Coaches');
        $this->addSql('DROP TABLE Players');
        $this->addSql('DROP TABLE Teams');
    }
}
