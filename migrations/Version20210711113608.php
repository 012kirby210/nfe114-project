<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210711113608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alignement invitation et conversation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation_profile (conversation_id INT NOT NULL, profile_id INT NOT NULL, INDEX IDX_1A01A4059AC0396 (conversation_id), INDEX IDX_1A01A405CCFA12B8 (profile_id), PRIMARY KEY(conversation_id, profile_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversation_profile ADD CONSTRAINT FK_1A01A4059AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_profile ADD CONSTRAINT FK_1A01A405CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E93A37CE5C');
        $this->addSql('DROP INDEX IDX_8A8E26E93A37CE5C ON conversation');
        $this->addSql('ALTER TABLE conversation ADD update_date_time VARCHAR(255) NOT NULL, DROP related_invitations_id');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2FE142757');
        $this->addSql('DROP INDEX IDX_F11D61A2FE142757 ON invitation');
        $this->addSql('ALTER TABLE invitation CHANGE conversations_id conversation_id INT NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A29AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX IDX_F11D61A29AC0396 ON invitation (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE conversation_profile');
        $this->addSql('ALTER TABLE conversation ADD related_invitations_id INT DEFAULT NULL, DROP update_date_time');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E93A37CE5C FOREIGN KEY (related_invitations_id) REFERENCES invitation (id)');
        $this->addSql('CREATE INDEX IDX_8A8E26E93A37CE5C ON conversation (related_invitations_id)');
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A29AC0396');
        $this->addSql('DROP INDEX IDX_F11D61A29AC0396 ON invitation');
        $this->addSql('ALTER TABLE invitation CHANGE conversation_id conversations_id INT NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2FE142757 FOREIGN KEY (conversations_id) REFERENCES conversation (id)');
        $this->addSql('CREATE INDEX IDX_F11D61A2FE142757 ON invitation (conversations_id)');
    }
}
