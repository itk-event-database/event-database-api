<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323133448 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__partner_organizers (event_id INT NOT NULL, organizer_id INT NOT NULL, INDEX IDX_9FF4084171F7E88B (event_id), INDEX IDX_9FF40841876C4DDA (organizer_id), PRIMARY KEY(event_id, organizer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__partner_organizers ADD CONSTRAINT FK_9FF4084171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event__partner_organizers ADD CONSTRAINT FK_9FF40841876C4DDA FOREIGN KEY (organizer_id) REFERENCES organizer (id)');
        $this->addSql('ALTER TABLE organizer CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX name_soft_unique ON organizer (name, deleted_at)');
        $this->addSql('CREATE UNIQUE INDEX email_soft_unique ON organizer (email, deleted_at)');
        $this->addSql('CREATE UNIQUE INDEX url_soft_unique ON organizer (url, deleted_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event__partner_organizers');
        $this->addSql('DROP INDEX name_soft_unique ON organizer');
        $this->addSql('DROP INDEX email_soft_unique ON organizer');
        $this->addSql('DROP INDEX url_soft_unique ON organizer');
        $this->addSql('ALTER TABLE organizer CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8mb3_unicode_ci, CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8mb3_unicode_ci');
    }
}
