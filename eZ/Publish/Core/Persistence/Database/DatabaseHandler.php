<?php
/**
 * File containing an interface for the database abstractions
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Database;

/**
 * Interface for relational database abstractions supported by eZ.
 */
interface DatabaseHandler
{
    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollBack();

    /**
     * Execute a query against the database
     *
     * @param string $query
     */
    public function exec($query);

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
    public function aliasedColumn( SelectQuery $query, $columnName, $tableName = null );

    /**
     * Returns a qualified identifier for $columnName in $tableName.
     *
     * @param string $columnName
     * @param string $tableName
     *
     * @return string
     */
    public function quoteColumn( $columnName, $tableName = null );

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function quoteTable( $tableName );

    /**
     * Custom alias method
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     * @return string
     */
    public function alias( $name, $alias );

    /**
     * Custom quote identifier method
     *
     * Ignores some properties of identifier quoting, but since we use somehow
     * sane table and column names, ourselves, this is fine.
     *
     * This is an optimization and works around the ezcDB implementation.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier( $identifier );

    /**
     * Get auto increment value
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
    public function getAutoIncrementValue( $table, $column );

    /**
     * Returns the name of the affected sequence
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName( $table, $column );
}
