<?php

namespace AdminBundle\EventListener;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * IgnoreTablesListener class
 */
class IgnoreTablesListener
{
    private $ignoredEntities = [
        'CraueConfigBundle:Setting'
    ];

    /**
     * Remove ignored tables /entities from Schema
     *
     * @param GenerateSchemaEventArgs $args
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();
        $em = $args->getEntityManager();

        $ignoredTables = [];

        foreach ($this->ignoredEntities as $entityName) {
            $ignoredTables[] = $em->getClassMetadata($entityName)->getTableName();
        }

        foreach ($schema->getTableNames() as $table) {
            $split = explode('.', $table);
            $tableName = end($split);
            if (in_array($tableName, $ignoredTables, true)) {
                // remove table from schema
                $schema->dropTable($table);
            }

        }
    }

}