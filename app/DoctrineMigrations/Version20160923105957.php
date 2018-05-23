<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160923105957 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C05FB297 ON user (confirmation_token)');
        $this->addSql('ALTER TABLE event ADD video_url VARCHAR(255) DEFAULT NULL, ADD ticket_purchase_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE occurrence ADD ticket_price_range VARCHAR(255) DEFAULT NULL, ADD event_status_text VARCHAR(255) DEFAULT NULL, ADD event_sales_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE place ADD video_url VARCHAR(255) DEFAULT NULL, ADD disability_access VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP video_url, DROP ticket_purchase_url');
        $this->addSql('ALTER TABLE occurrence DROP ticket_price_range, DROP event_status_text, DROP event_sales_status');
        $this->addSql('ALTER TABLE place DROP video_url, DROP disability_access');
        $this->addSql('DROP INDEX UNIQ_8D93D649C05FB297 ON user');
    }
}
