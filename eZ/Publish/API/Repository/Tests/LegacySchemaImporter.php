<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use EzSystems\DoctrineSchema\API\Exception\InvalidConfigurationException;
use EzSystems\DoctrineSchema\Importer\SchemaImporter;
use RuntimeException;

/**
 * Legacy database Schema Importer for database integration tests.
 *
 * @uses \EzSystems\DoctrineSchema\Importer\SchemaImporter
 *
 * @internal For internal use by the Repository test cases.
 */
final class LegacySchemaImporter
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Import database schema from Doctrine Schema Yaml configuration file.
     *
     * @param string $schemaFilePath Yaml schema configuration file path
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function importSchema(string $schemaFilePath): void
    {
        if (!file_exists($schemaFilePath)) {
            throw new RuntimeException("The schema file path {$schemaFilePath} does not exist");
        }

        $this->connection->beginTransaction();
        $importer = new SchemaImporter();
        try {
            $databasePlatform = $this->connection->getDatabasePlatform();
            $schema = $importer->importFromFile($schemaFilePath);
            $statements = array_merge(
                $this->getDropSqlStatementsForExistingSchema(
                    $schema,
                    $databasePlatform,
                    $this->connection
                ),
                // generate schema DDL queries
                $schema->toSql($databasePlatform)
            );

            foreach ($statements as $statement) {
                $this->connection->exec($statement);
            }

            $this->connection->commit();
        } catch (InvalidConfigurationException | DBALException $e) {
            $this->connection->rollBack();
            throw new RuntimeException($e->getMessage(), 1, $e);
        }
    }

    /**
     * @return string[]
     */
    private function getDropSqlStatementsForExistingSchema(
        DoctrineSchema $newSchema,
        AbstractPlatform $databasePlatform,
        Connection $connection
    ): array {
        $existingSchema = $connection->getSchemaManager()->createSchema();
        $statements = [];
        // reverse table order for clean-up (due to FKs)
        $tables = array_reverse($newSchema->getTables());
        // cleanup pre-existing database
        foreach ($tables as $table) {
            if ($existingSchema->hasTable($table->getName())) {
                $statements[] = $databasePlatform->getDropTableSQL($table);
            }
        }

        return $statements;
    }
}
