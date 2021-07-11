<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210614174655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Define the invitation as an associative class';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation 
            (id INT AUTO_INCREMENT NOT NULL,
            host_id INT NOT NULL,
            guest_id INT NOT NULL,
            conversation_id INT NOT NULL,
            create_datetime VARCHAR(255) DEFAULT NULL,
            update_datetime VARCHAR(255) NOT NULL,
            commentaires VARCHAR(512) DEFAULT NULL,
            INDEX IDX_F11D61A21FB8D185 (host_id),
            INDEX IDX_F11D61A29A4AA658 (guest_id),
            INDEX IDX_F11D61A2FE142757 (conversation_id),
            PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invitation
            ADD CONSTRAINT FK_F11D61A21FB8D185 FOREIGN KEY (host_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE invitation
            ADD CONSTRAINT FK_F11D61A29A4AA658 FOREIGN KEY (guest_id) REFERENCES profile (id)');
        $this->addSql('ALTER TABLE invitation
            ADD CONSTRAINT FK_F11D61A2FE142757 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE conversation
            CHANGE archived archived TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE invitation');
        $this->addSql('ALTER TABLE conversation CHANGE archived archived TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
