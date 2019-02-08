<?php

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
class Version20170228091107 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        // Create temporary tables.
        $this->addSql('
CREATE TABLE `tmp_event` (
  `id` int(11) NOT NULL,
  `feed_id` int(11) DEFAULT NULL,
  `created_by_id` int(11) DEFAULT NULL,
  `updated_by_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feed_event_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `original_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `langcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_purchase_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `excerpt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL
);');

        $this->addSql('
CREATE TABLE `tmp_occurrence` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `place_id` int(11) DEFAULT NULL,
  `ticket_price_range` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_status_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_sales_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
);');

        // Copy data to temporary tables.
        $this->addSql('INSERT INTO `tmp_event` (select * from event where feed_id is null);');
        $this->addSql('INSERT INTO `tmp_occurrence` (select * from occurrence where event_id in (select id from event where feed_id is null) and deleted_at is null);');

        // Drop tables.
        $this->addSql('DROP TABLE `occurrence`');
        $this->addSql('DROP TABLE `event`');

        // Create tables.
        $this->addSql('CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) DEFAULT NULL,
  `created_by_id` int(11) DEFAULT NULL,
  `updated_by_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feed_event_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `original_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `langcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ticket_purchase_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `excerpt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3BAE0AA751A5BC03` (`feed_id`),
  KEY `IDX_3BAE0AA7B03A8386` (`created_by_id`),
  KEY `IDX_3BAE0AA7896DBBDE` (`updated_by_id`),
  CONSTRAINT `FK_3BAE0AA751A5BC03` FOREIGN KEY (`feed_id`) REFERENCES `feed` (`id`),
  CONSTRAINT `FK_3BAE0AA7896DBBDE` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_3BAE0AA7B03A8386` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        $this->addSql('CREATE TABLE `occurrence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `place_id` int(11) DEFAULT NULL,
  `ticket_price_range` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_status_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_sales_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BEFD81F371F7E88B` (`event_id`),
  KEY `IDX_BEFD81F3DA6A219` (`place_id`),
  CONSTRAINT `FK_BEFD81F371F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  CONSTRAINT `FK_BEFD81F3DA6A219` FOREIGN KEY (`place_id`) REFERENCES `place` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');

        // Copy data from temporary tables.
        $this->addSql('INSERT INTO `event` (select * from tmp_event);');
        $this->addSql('INSERT INTO `occurrence` (select * from tmp_occurrence);');

        // Drop temporary tables.
        $this->addSql('DROP TABLE `tmp_occurrence`');
        $this->addSql('DROP TABLE `tmp_event`');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
