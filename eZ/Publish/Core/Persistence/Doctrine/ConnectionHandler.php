<?php

namespace eZ\Publish\Core\Persistence\Doctrine;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Database\QueryException;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class ConnectionHandler implements DatabaseHandler
{
    protected $lastInsertedIds = array();

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

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
        if ( isset( $this->lastInsertedIds[$sequenceName] ) )
        {
            $lastInsertId = $this->lastInsertedIds[$sequenceName];
            unset( $this->lastInsertedIds[$sequenceName] );
            return $lastInsertId;
        }

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
        return new SelectDoctrineQuery($this->connection);
    }

    /**
     * Create Insert Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\InsertQuery
     */
    public function createInsertQuery()
    {
        return new InsertDoctrineQuery($this->connection);
    }

    /**
     * Create update Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\UpdateQuery
     */
    public function createUpdateQuery()
    {
        return new UpdateDoctrineQuery($this->connection);
    }

    /**
     * Create a Delete Query object.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DeleteQuery
     */
    public function createDeleteQuery()
    {
        return new DeleteDoctrineQuery($this->connection);
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
        if ( $this->connection->getDatabasePlatform()->getName() === 'sqlite' )
        {
            return $this->getAutoIncrementValueSqlite( $table, $column );
        }

        return "null";
    }

    private function getAutoIncrementValueSqlite( $table, $column )
    {
        if ( ( $table === "ezcontentobject_attribute" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentobject_attribute" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentobject_attribute.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentobject_attribute.id"];
        }

        if ( ( $table === "ezcontentclass" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentclass" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentclass.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentclass.id"];
        }

        if ( ( $table === "ezcontentclass_attribute" ) && ( $column === "id" ) )
        {
            // This is a @HACK -- since this table has a multi-column key with
            // auto-increment, which is not easy to simulate in SQLite. This
            // solves it for now.
            $q = $this->createSelectQuery();
            $q->select( $q->expr->max( "id" ) )->from( "ezcontentclass_attribute" );
            $statement = $q->prepare();
            $statement->execute();

            $this->lastInsertedIds["ezcontentclass_attribute.id"] = (int)$statement->fetchColumn() + 1;
            return $this->lastInsertedIds["ezcontentclass_attribute.id"];
        }

        return "NULL";
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
