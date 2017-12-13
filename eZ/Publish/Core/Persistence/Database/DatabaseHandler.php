<?php

/**
 * File containing an interface for the database abstractions.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Database;

use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

/**
 * Interface for relational database abstractions supported by eZ.
 *
 * @deprecated Since 6.13, please use Doctrine DBAL instead (@ezpublish.persistence.connection)
 *             it provides richer and more powerful DB abstraction which is also easier to use.
 */
interface DatabaseHandler extends EzcDbHandler
{
    /**
     * Name of the database technology.
     *
     * @return string
     */
    public function getName();

    /**
     * Begin a transaction.
     */
    public function beginTransaction();

    /**
     * Commit a transaction.
     */
    public function commit();

    /**
     * Rollback a transaction.
     */
    public function rollBack();

    /**
     * Check for sequence based driver or not.
     *
     * @return bool
     */
    public function useSequences();

    /**
     * Retrieve the last auto incremet or sequence id.
     *
     * @param string $sequenceName
     *
     * @return string
     */
    public function lastInsertId($sequenceName = null);

    /**
     * Execute a query against the database.
     *
     * @param string $query
     */
    public function exec($query);

    /**
     * Prepare and return a statement.
     *
     * Statements are ducktyped, but need to work like PDOStatement.
     *
     * @return object
     */
    public function prepare($query);

    /**
     * Create Select Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createSelectQuery();

    /**
     * Create Insert Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function createInsertQuery();

    /**
     * Create update Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function createUpdateQuery();

    /**
     * Create a Delete Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function createDeleteQuery();

    /**
     * Creates an alias for $tableName, $columnName in $query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param string $columnName
     * @param string|null $tableName
     *
     * @return string
     */
    public function aliasedColumn($query, $columnName, $tableName = null);

    /**
     * Returns a qualified identifier for $columnName in $tableName.
     *
     * @param string $columnName
     * @param string $tableName
     *
     * @return string
     */
    public function quoteColumn($columnName, $tableName = null);

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function quoteTable($tableName);

    /**
     * Custom alias method.
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function alias($name, $alias);

    /**
     * Custom quote identifier method.
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function quoteIdentifier($identifier);

    /**
     * Get auto increment value.
     *
     * Returns the value used for autoincrement tables. Usually this will just
     * be null. In case for sequence based RDBMS this method can return a
     * proper value for the given column.
     *
     * @param string $table
     * @param string $column
     *
     * @return mixed
     */
    public function getAutoIncrementValue($table, $column);

    /**
     * Returns the name of the affected sequence.
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName($table, $column);

    /**
     * Returns underlying connection (e.g. Doctrine connection object).
     *
     * @return mixed
     */
    public function getConnection();
}
