<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210601161641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Generate the user table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL,
         email VARCHAR(180) NOT NULL,
         roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\',
         uuid VARCHAR(36) NOT NULL,
         password VARCHAR(255) NOT NULL,
         create_datetime DATETIME NOT NULL,
         update_datetime DATETIME NOT NULL,
         UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
         UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid),
         PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
    }
}
