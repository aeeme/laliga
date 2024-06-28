<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240628063451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email columns to players and coaches tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Players ADD email VARCHAR(100) NOT NULL');

        $this->addSql('ALTER TABLE Coaches ADD email VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Players DROP email');

        $this->addSql('ALTER TABLE Coaches DROP email');

    }
}
