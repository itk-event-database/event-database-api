<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170307190528 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feed ADD user_id INT DEFAULT NULL');
        $this->addSql('UPDATE feed SET user_id = created_by_id');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044ABA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_234044ABA76ED395 ON feed (user_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feed DROP FOREIGN KEY FK_234044ABA76ED395');
        $this->addSql('DROP INDEX IDX_234044ABA76ED395 ON feed');
        $this->addSql('ALTER TABLE feed DROP user_id');
    }
}
