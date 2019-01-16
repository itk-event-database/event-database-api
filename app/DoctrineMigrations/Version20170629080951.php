<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use AppBundle\Entity\Place;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170629080951 extends AbstractMigration
{
    /**
     * Convert serialized Places in event.repeating_occurrences to just the Place id.
     *
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $updateSql = 'update event set repeating_occurrences = :repeating_occurrences where id = :id';
        $updateStmt = $this->connection->prepare($updateSql);

        $sql = 'select id, repeating_occurrences from event where repeating_occurrences != :empty_array';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['empty_array' => serialize([])]);
        while ($row = $stmt->fetch()) {
            echo 'event: '.$row['id'];
            $data = unserialize($row['repeating_occurrences']);
            if (isset($data['place']) && $data['place'] instanceof Place) {
                $data['place'] = $data['place']->getId();
                $row['repeating_occurrences'] = serialize($data);

                $updateStmt->execute([
            'id' => $row['id'],
            'repeating_occurrences' => $row['repeating_occurrences'],
          ]);
                echo '; place: '.$data['place'];
            }
            echo PHP_EOL;
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // There is no going back …
    }
}
