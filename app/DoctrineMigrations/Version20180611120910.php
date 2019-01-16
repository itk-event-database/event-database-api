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

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180611120910 extends AbstractMigration
{
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $eventBeforeStatement = $this->connection->executeQuery('SELECT COUNT(*) AS `c` FROM `event`');
        $eventBeforeResult = $eventBeforeStatement->fetch();

        $databaseName = $this->connection->getDatabase();

        $this->addSql('ALTER DATABASE `'.$databaseName.'` DEFAULT CHARACTER SET `utf8mb4`');

        $this->addSql('ALTER TABLE `event` CHARACTER SET = utf8mb4');
        $this->addSql('
          ALTER TABLE `event`
            CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
            CHANGE `name` `name` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
            CHANGE `excerpt` `excerpt` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL;
        ');

        $this->addSql('ALTER TABLE `place` CHARACTER SET = utf8mb4');
        $this->addSql('
          ALTER TABLE `place`
            CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 DEFAULT NULL,
            CHANGE `name` `name` VARCHAR(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
            CHANGE `address_country` `address_country` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
            CHANGE `address_locality` `address_locality` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
            CHANGE `address_region` `address_region` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
            CHANGE `street_address` `street_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL;
        ');

        $this->addSql('ALTER TABLE `tag` CHARACTER SET = utf8mb4');
        $this->addSql('
          ALTER TABLE `tag`
            CHANGE `name` `name` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL;
        ');

        $this->addSql('ALTER TABLE `unknown_tag` CHARACTER SET = utf8mb4');
        $this->addSql('
          ALTER TABLE `unknown_tag`
            CHANGE `name` `name` VARCHAR(50) COLLATE utf8mb4_general_ci NOT NULL;
        ');

        $eventAfterStatement = $this->connection->executeQuery('SELECT COUNT(*) AS `c` FROM `event`');
        $eventAfterResult = $eventAfterStatement->fetch();

        $this->abortIf($eventBeforeResult !== $eventAfterResult, 'Migration failed. Number of events changed.');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
