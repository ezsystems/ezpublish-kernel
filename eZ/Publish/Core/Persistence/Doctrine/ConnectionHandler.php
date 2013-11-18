<?php

namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\QueryException;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class ConnectionHandler implements DatabaseHandler
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        try
        {
            $this->connection->beginTransaction();
        }
        catch ( DBALException $e )
        {
            throw new QueryException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    public function commit()
    {
        try
        {
            $this->connection->commit();
        }
        catch ( DBALException $e )
        {
            throw new QueryException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        try
        {
            $this->connection->rollBack();
        }
        catch ( DBALException $e )
        {
            throw new QueryException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    public function prepare( $query )
    {
        return $this->connection->prepare( $query );
    }

    /**
     * Retrieve the last auto incremet or sequence id
     *
     * @param string $sequenceName
     * @return string
     */
    public function lastInsertId( $sequenceName = null )
    {
        return $this->connection->lastInsertId( $sequenceName );
    }

    /**
     * @return bool
     */
    public function useSequences()
    {
        return $this->connection->getDatabasePlatform()->supportsSequences();
    }

    /**
     * Execute a query against the database
     *
     * @param string $query
     */
    public function exec( $query )
    {
        try
        {
            $this->connection->exec( $query );
        }
        catch ( DBALException $e )
        {
            throw new QueryException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * Create Select Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createSelectQuery()
    {
        return new SelectDoctrineQuery( $this->connection );
    }

    /**
     * Create Insert Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function createInsertQuery()
    {
        return new InsertDoctrineQuery( $this->connection );
    }

    /**
     * Create update Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function createUpdateQuery()
    {
        return new UpdateDoctrineQuery( $this->connection );
    }

    /**
     * Create a Delete Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function createDeleteQuery()
    {
        return new DeleteDoctrineQuery( $this->connection );
    }

    /**
     * Creates an alias for $tableName, $columnName in $query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param string $columnName
     * @param string|null $tableName
     *
     * @return string
     */
    public function aliasedColumn( $query, $columnName, $tableName = null )
    {
        return $this->alias(
            $this->quoteColumn( $columnName, $tableName ),
            $this->quoteIdentifier(
                ( $tableName ? $tableName . '_' : '' ) .
                $columnName
            )
        );
    }

    /**
     * Returns a qualified identifier for $columnName in $tableName.
     *
     * @param string $columnName
     * @param string $tableName
     *
     * @return string
     */
    public function quoteColumn( $columnName, $tableName = null )
    {
        return
            ( $tableName ? $this->quoteTable( $tableName ) . '.' : '' ) .
            $this->quoteIdentifier( $columnName );
    }

    /**
     * Returns a qualified identifier for $tableName.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function quoteTable( $tableName )
    {
        return $this->quoteIdentifier( $tableName );
    }

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
    public function alias( $name, $alias )
    {
        return $name . ' ' . $alias;
    }

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
    public function quoteIdentifier( $identifier )
    {
        return '`' . $identifier . '`';
    }

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
    public function getAutoIncrementValue( $table, $column )
    {
        return "null";
    }

    /**
     * Returns the name of the affected sequence
     *
     * @param string $table
     * @param string $column
     *
     * @return string
     */
    public function getSequenceName( $table, $column )
    {
        return null;
    }
}
