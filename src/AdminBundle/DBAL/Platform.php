<?php

/**
 * @file
 * @TODO: Missing description.
 */

namespace AdminBundle\DBAL;

use Doctrine\DBAL\Platforms\MySqlPlatform;

/**
 * Class Platform
 * @package AppBundle\DBAL
 *
 * Problem: On MySQL >= 5.5.7 fixtures bundle cannot truncate tables because of foreign key constraints
 *
 * Fixes https://github.com/doctrine/data-fixtures/pull/127
 * Using https://coderwall.com/p/staybw/workaround-for-1701-cannot-truncate-a-table-referenced-in-a-foreign-key-constraint-using-doctrine-fixtures-load-purge-with-truncate
 */
class Platform extends MySqlPlatform
{

  /**
   * {@inheritdoc}
   */
    public function getTruncateTableSQL($tableName, $cascade = false)
    {
        return sprintf('SET foreign_key_checks = 0;TRUNCATE %s;SET foreign_key_checks = 1;', $tableName);
    }
}
