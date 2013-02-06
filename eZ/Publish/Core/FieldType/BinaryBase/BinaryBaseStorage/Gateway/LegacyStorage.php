<?php
/**
 * File containing the BinaryBaseStorage Gateway
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\Core\FieldType\BinaryBase\BinaryBaseStorage\Gateway;

abstract class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Returns the table name to store data in.
     *
     * @return string
     */
    abstract protected function getStorageTable();

    /**
     * Returns a column to property mapping for the storage table.
     *
     * @return void
     */
    protected function getPropertyMapping()
    {
        return array(
            'filename' => array(
                'name' => 'path',
                'cast' => 'strval',
            ),
            'mime_type' => array(
                'name' => 'mimeType',
                'cast' => 'strval',
            ),
            'original_filename' => array(
                'name' => 'fileName',
                'cast' => 'strval',
            )
        );
    }

    /**
     * Set columns to be fetched from the database
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be fetched from the database. Please do not
     * forget to call the parent when overwriting this method.
     *
     * @param \ezcQuerySelect $selectQuery
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return void
     */
    protected function setFetchColumns( \ezcQuerySelect $selectQuery, $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery->select(
            $connection->quoteColumn( 'filename' ),
            $connection->quoteColumn( 'mime_type' ),
            $connection->quoteColumn( 'original_filename' )
        );
    }

    /**
     * Sets the required insert columns to $selectQuery.
     *
     * This method is intended to be overwritten by derived classes in order to
     * add additional columns to be set in the database. Please do not forget
     * to call the parent when overwriting this method.
     *
     * @param \ezcQueryInsert $insertQuery
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return void
     */
    protected function setInsertColumns( \ezcQueryInsert $insertQuery, VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $insertQuery->set(
            $connection->quoteColumn( 'contentobject_attribute_id' ),
            $insertQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
        )->set(
            $connection->quoteColumn( 'filename' ),
            $insertQuery->bindValue(
                $this->removeMimeFromPath( $field->value->externalData['path'] )
            )
        )->set(
            $connection->quoteColumn( 'mime_type' ),
            $insertQuery->bindValue( $field->value->externalData['mimeType'] )
        )->set(
            $connection->quoteColumn( 'original_filename' ),
            $insertQuery->bindValue( $field->value->externalData['fileName'] )
        )->set(
            $connection->quoteColumn( 'version' ),
            $insertQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
        );
    }

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Stores the file reference in $field for $versionNo
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return void
     */
    public function storeFileReference( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto(
            $connection->quoteTable( $this->getStorageTable() )
        );

        $this->setInsertColumns( $insertQuery, $versionInfo, $field );

        $insertQuery->prepare()->execute();

        return false;
    }

    /**
     * Removes the prepended mime-type directory from $path for legacy storage.
     *
     * @param string $path
     *
     * @protected
     *
     * @return string
     */
    public function removeMimeFromPath( $path )
    {
        $res = substr( $path, strpos( $path, '/' ) + 1 );
        return $res;
    }

    /**
     * Returns the file reference data for the given $fieldId in $versionNo
     *
     * @param mixed $fieldId
     * @param int $versionNo
     *
     * @return array|void
     */
    public function getFileReferenceData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();

        $this->setFetchColumns( $selectQuery, $fieldId, $versionNo );

        $selectQuery->from(
            $connection->quoteTable( $this->getStorageTable() )
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $selectQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'version' ),
                    $selectQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( count( $result ) < 1 )
        {
            return null;
        }

        $propertyMap = $this->getPropertyMapping();

        $convertedResult = array();
        foreach ( reset( $result ) as $column => $value )
        {
            $convertedResult[$this->toPropertyName( $column )] = $this->castToPropertyValue( $value, $column );
        }
        $convertedResult['path'] = $this->prependMimeToPath(
            $convertedResult['path'],
            $convertedResult['mimeType']
        );

        return $convertedResult;
    }

    /**
     * Returns the property name for the given $columnName
     *
     * @param string $columnName
     *
     * @return string
     */
    protected function toPropertyName( $columnName )
    {
        $propertyMap = $this->getPropertyMapping();
        return ( $propertyMap[$columnName]['name'] );
    }

    /**
     * Returns $value casted as specified by {@link getPropertyMapping()}.
     *
     * @param mixed $value
     * @param string $columnName
     *
     * @return mixed
     */
    protected function castToPropertyValue( $value, $columnName )
    {
        $propertyMap = $this->getPropertyMapping();
        $castFunction = $propertyMap[$columnName]['cast'];
        return $castFunction( $value );
    }

    /**
     * Prepends $path with the first part of the given $mimeType.
     *
     * @param string $path
     * @param string $mimeType
     *
     * @protected
     *
     * @return string
     */
    public function prependMimeToPath( $path, $mimeType )
    {
        $res = substr( $mimeType, 0, strpos( $mimeType, '/' ) ) . '/' . $path;
        return $res;
    }

    /**
     * Removes all file references for the given $fieldIds
     *
     * @param array $fieldIds
     *
     * @return void
     */
    public function removeFileReferences( array $fieldIds, $versionNo )
    {
        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( $this->getStorageTable() )
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $fieldIds
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn( 'version' ),
                    $deleteQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Removes a specific file reference for $fieldId and $versionId
     *
     * @param mixed $fieldId
     * @param int $versionNo
     *
     * @return void
     */
    public function removeFileReference( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( $this->getStorageTable() )
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $deleteQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn( 'version' ),
                    $deleteQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Returns a set o file references, referenced by the given $fieldIds.
     *
     * @param array $fieldIds
     *
     * @return array
     */
    public function getReferencedFiles( array $fieldIds, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'filename' ),
            $connection->quoteColumn( 'mime_type' )
        )->from(
            $connection->quoteTable( $this->getStorageTable() )
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->in(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $fieldIds
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'version' ),
                    $selectQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $gateway = $this;
        return array_map(
            function ( $row ) use ( $gateway )
            {
                return $gateway->prependMimeToPath( $row['filename'], $row['mime_type'] );
            },
            $statement->fetchAll( \PDO::FETCH_ASSOC )
        );
    }

    /**
     * Returns a map with the number of references each file from $files has
     *
     * @param array $files
     *
     * @return array
     */
    public function countFileReferences( array $files )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'filename' ),
            $connection->quoteColumn( 'mime_type' ),
            $selectQuery->alias(
                $selectQuery->expr->count( $connection->quoteColumn( 'contentobject_attribute_id' ) ),
                'count'
            )
        )->from(
            $connection->quoteTable( $this->getStorageTable() )
        )->where(
            $selectQuery->expr->in(
                $connection->quoteColumn( 'filename' ),
                array_map(
                    array( $this, 'removeMimeFromPath' ),
                    $files
                )
            )
        )->groupBy( $connection->quoteColumn( 'filename' ) );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $countMap = array();
        foreach ( $statement->fetchAll( \PDO::FETCH_ASSOC ) as $row )
        {
            $path = $this->prependMimeToPath( $row['filename'], $row['mime_type'] );
            $countMap[$path] = (int)$row['count'];
        }

        // Complete counts
        foreach ( $files as $path )
        {
            // This is already the correct path
            if ( !isset( $countMap[$path] ) )
            {
                $countMap[$path] = 0;
            }
        }

        return $countMap;
    }
}

