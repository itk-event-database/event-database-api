<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190119133421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE daily_occurrence (id INT AUTO_INCREMENT NOT NULL, occurrence_id INT DEFAULT NULL, event_id INT DEFAULT NULL, place_id INT DEFAULT NULL, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, room VARCHAR(255) DEFAULT NULL, ticket_price_range VARCHAR(255) DEFAULT NULL, event_status_text VARCHAR(255) DEFAULT NULL, event_sales_status VARCHAR(255) DEFAULT NULL, INDEX IDX_431E7C9030572FAC (occurrence_id), INDEX IDX_431E7C9071F7E88B (event_id), INDEX IDX_431E7C90DA6A219 (place_id), INDEX IDX_OCCURRENCE_DATES (start_date, end_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9030572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id)');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9071F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C90DA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE daily_occurrence');
    }
}
