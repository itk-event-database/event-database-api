<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160729190350 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE place (id INT AUTO_INCREMENT NOT NULL, feed_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_741D53CD51A5BC03 (feed_id), INDEX IDX_741D53CDB03A8386 (created_by_id), INDEX IDX_741D53CD896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CD51A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id)');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CD896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE place');
    }
}
