<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161002211217 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE place DROP FOREIGN KEY FK_741D53CDF5B7AF75');
        $this->addSql('DROP TABLE postal_address');
        $this->addSql('DROP INDEX IDX_741D53CDF5B7AF75 ON place');
        $this->addSql('ALTER TABLE place ADD address_country VARCHAR(255) DEFAULT NULL, ADD address_locality VARCHAR(255) DEFAULT NULL, ADD address_region VARCHAR(255) DEFAULT NULL, ADD postal_code VARCHAR(255) DEFAULT NULL, ADD street_address VARCHAR(255) DEFAULT NULL, DROP address_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE postal_address (id INT AUTO_INCREMENT NOT NULL, address_country VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, address_locality VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, address_region VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, postal_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, post_office_box_number VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, street_address VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE place ADD address_id INT DEFAULT NULL, DROP address_country, DROP address_locality, DROP address_region, DROP postal_code, DROP street_address');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CDF5B7AF75 FOREIGN KEY (address_id) REFERENCES postal_address (id)');
        $this->addSql('CREATE INDEX IDX_741D53CDF5B7AF75 ON place (address_id)');
    }
}
