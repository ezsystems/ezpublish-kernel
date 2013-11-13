<?php

namespace eZ\Publish\Core\Persistence\Doctrine;

use Doctrine\DBAL\Connection;

/**
 * Table Gateway using Doctrine DBAL
 *
 * Will reduce the necessary code in concrete Gateway implementations
 * of Legacy and SqlNg storage engines.
 */
class TableGateway
{
    private $connection;
    private $platform;
    private $metadata;

    public function __construct(Connection $connection, TableMetadata $metadata)
    {
        $this->connection = $connection;
        $this->platform = $connection->getDatabasePlatform();
        $this->metadata = $metadata;
    }

    public function insert(array $data)
    {
        $types = $this->getTypes( $data );

        if ( $this->metadata->getSequenceName() && $this->platform->prefersSequences() )
        {
            $primaryKeyColumn = $this->metadata->getSinglePrimaryKeyColumn();
            $data[$primaryKeyColumn] = $this->connection->nextSequenceValue( $this->metadata->getSequenceName() );

            $types[] = $this->metadata->columns[$primaryKeyColumn]['type'];
        }

        $this->connection->insert( $this->metadata->getTableName(), $data, $types );

        if ( $this->platform->prefersIdentityColumns() || !$this->metadata->getSequenceName() )
        {
            return $this->connection->lastInsertId();
        }

        return $data[$primaryKeyColumn];
    }

    private function getTypes(array $data)
    {
        $types = array();

        foreach ( $data as $columnName => $value )
        {
            $types[] = $this->metadata->getColumnType( $columnName );
        }

        return $types;
    }

    public function update(array $data, array $where)
    {
        $types = array_merge( $this->getTypes( $data ), $this->getTypes( $where ) );

        return $this->connection->update( $this->metadata->getTableName(), $data, $where, $types );
    }

    public function delete(array $where)
    {
        $types = $this->getTypes( $where );

        return $this->connection->delete( $this->metadata->getTableName(), $where, $types );
    }
}
