<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190215035039 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_occurrence DROP FOREIGN KEY FK_431E7C9030572FAC');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9030572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE daily_occurrence DROP FOREIGN KEY FK_431E7C9030572FAC');
        $this->addSql('ALTER TABLE daily_occurrence ADD CONSTRAINT FK_431E7C9030572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id)');
    }
}
