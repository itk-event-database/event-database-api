<?php

declare(strict_types=1);

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230323094647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__partner_organizers (event_id INT NOT NULL, organizer_id INT NOT NULL, INDEX IDX_9FF4084171F7E88B (event_id), INDEX IDX_9FF40841876C4DDA (organizer_id), PRIMARY KEY(event_id, organizer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event__partner_organizers ADD CONSTRAINT FK_9FF4084171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event__partner_organizers ADD CONSTRAINT FK_9FF40841876C4DDA FOREIGN KEY (organizer_id) REFERENCES organizer (id)');
        $this->addSql('DROP TABLE event__additional_organizers');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event__additional_organizers (event_id INT NOT NULL, organizer_id INT NOT NULL, INDEX IDX_6F3655A5876C4DDA (organizer_id), INDEX IDX_6F3655A571F7E88B (event_id), PRIMARY KEY(event_id, organizer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE event__additional_organizers ADD CONSTRAINT FK_6F3655A571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event__additional_organizers ADD CONSTRAINT FK_6F3655A5876C4DDA FOREIGN KEY (organizer_id) REFERENCES organizer (id)');
        $this->addSql('DROP TABLE event__partner_organizers');
    }
}
