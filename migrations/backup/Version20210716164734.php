<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716164734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout état de l\'invitation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation CHANGE archived archived TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD etat VARCHAR(64) NOT NULL DEFAULT \'pending\' ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation CHANGE archived archived TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE invitation DROP etat');
    }
}
