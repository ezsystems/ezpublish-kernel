<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;

/**
 * Database fixture importer.
 *
 * @internal for internal use by Repository test setup
 */
final class FixtureImporter
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var string[] */
    private static $resetSequenceStatements;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function import(Fixture $fixture): void
    {
        $data = $fixture->load();

        $tablesList = array_keys($data);
        // truncate all tables, even the ones initially empty (some tests are affected by this)
        $this->truncateTables(array_reverse($tablesList));

        $nonEmptyTablesData = array_filter(
            $data,
            function ($tableData) {
                return !empty($tableData);
            }
        );
        foreach ($nonEmptyTablesData as $table => $rows) {
            $firstRow = current($rows);
            $query = $this->connection->createQueryBuilder();
            $columns = array_keys($firstRow);
            $query
                ->insert($table)
                ->values(
                    array_combine(
                        $columns,
                        array_map(
                            function (string $columnName) {
                                return ":{$columnName}";
                            },
                            $columns
                        )
                    )
                );

            foreach ($rows as $row) {
                $query->setParameters($row);
                $query->execute();
            }
        }

        if ($this->connection->getDatabasePlatform()->supportsSequences()) {
            $this->resetSequences($tablesList);
        }
    }

    /**
     * @param string[] $tables a list of table names
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function truncateTables(array $tables)
    {
        $dbPlatform = $this->connection->getDatabasePlatform();

        foreach ($tables as $table) {
            try {
                // Cleanup before inserting (using TRUNCATE for speed, however not possible to rollback)
                $this->connection->executeUpdate(
                    $dbPlatform->getTruncateTableSql($this->connection->quoteIdentifier($table))
                );
            } catch (DBALException | PDOException $e) {
                // Fallback to DELETE if TRUNCATE failed (because of FKs for instance)
                $this->connection->createQueryBuilder()->delete($table)->execute();
            }
        }
    }

    /**
     * Reset database sequences, if needed.
     *
     * @param string[] $affectedTables
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function resetSequences(array $affectedTables): void
    {
        foreach ($this->getSequenceResetStatements($affectedTables) as $statement) {
            $this->connection->exec($statement);
        }
    }

    /**
     * Obtain SQL statements for resetting sequences associated with affected tables.
     *
     * Note: current implementation is probably not the best way to do it.
     *       It should be DBMS-specific, but let's avoid over-engineering it (more) until needed.
     *
     * @param array $affectedTables the list of tables which need their sequences reset
     *
     * @return string[] the list of SQL statement
     */
    private function getSequenceResetStatements(array $affectedTables): array
    {
        // return in-memory cache for performance reasons
        if (null !== self::$resetSequenceStatements) {
            return self::$resetSequenceStatements;
        }

        // generate & cache statements
        self::$resetSequenceStatements = [];

        // note: prepared statements don't work for table names
        $queryTemplate = 'SELECT setval(\'%s\', MAX(%s)) FROM %s';

        $schemaManager = $this->connection->getSchemaManager();
        foreach ($affectedTables as $tableName) {
            $columns = $schemaManager->listTableColumns($tableName);
            foreach ($columns as $column) {
                if (!$column->getAutoincrement()) {
                    continue;
                }

                $columnName = $column->getName();
                $sequenceName = "{$tableName}_{$columnName}_seq";

                self::$resetSequenceStatements[] = sprintf(
                    $queryTemplate,
                    $sequenceName,
                    $this->connection->quoteIdentifier($columnName),
                    $this->connection->quoteIdentifier($tableName)
                );
            }
        }

        return self::$resetSequenceStatements;
    }
}
