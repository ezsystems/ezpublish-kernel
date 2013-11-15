<?php

namespace eZ\Publish\Core\Persistence\Doctrine;

use Doctrine\DBAL\DBALException;

/**
 * Metadata description of an SQL table, used by runtime operations of the TableGateway.
 */
class TableMetadata
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $sequenceName;

    /**
     * @var array(string)
     */
    private $primaryKeyColumns = array();

    /**
     * @var array
     */
    private $columns = array();

    public function __construct( $tableName, $sequenceName = null )
    {
        $this->tableName = $tableName;
        $this->sequenceName = $sequenceName;
    }

    public function addColumn( $columnName, $type = 'string' )
    {
        $this->columns[$columnName] = array( 'type' => $type );

        return $this;
    }

    public function setPrimaryKey( array $columns )
    {
        foreach ( $columns as $columnName )
        {
            if ( !isset( $this->columns[$columnName] ) )
            {
                throw new DBALException(
                    sprintf(
                        'Column "%s" is not configured for table "%s"',
                        $columnName,
                        $this->tableName
                    )
                );
            }
        }

        $this->primaryKeyColumns = $columns;

        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getSequenceName()
    {
        return $this->sequenceName;
    }

    /**
     * @return string
     */
    public function getSinglePrimaryKeyColumn()
    {
        if ( count( $this->primaryKeyColumns ) !== 1 )
        {
            throw new DBALException(
                sprintf(
                    'Table "%s" needs exactly one primary key column for this operation, has: %d',
                    $this->tableName,
                    count( $this->primaryKeyColumns )
                )
            );
        }

        return $this->primaryKeyColumns[0];
    }

    /**
     * Retrieve the Doctrine database type for the given column.
     *
     * @return string|integer
     */
    public function getColumnType($columnName)
    {
        if ( !isset($this->columns[$columnName]) )
        {
            throw new DBALException(
                sprintf(
                    'Column "%s" is not configured for table "%s"',
                    $columnName,
                    $this->tableName
                )
            );
        }

        return $this->columns[$columnName]['type'];
    }
}
