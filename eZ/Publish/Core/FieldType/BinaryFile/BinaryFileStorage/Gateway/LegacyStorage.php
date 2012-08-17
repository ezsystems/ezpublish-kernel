<?php
/**
 * File containing the BinaryFileStorage Gateway
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Maps column names to property names
     *
     * @var array
     */
    protected $propertyMap = array(
        'download_count' => 'downloadCount',
        'filename' => 'path',
        'mime_type' => 'mimeType',
        'original_filename' => 'fileName',
    );

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
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
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     * @throws \RuntimeException if no connection has been set, yet.
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
     * @return void
     */
    public function storeFileReference( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto(
            $connection->quoteTable( 'ezbinaryfile' )
        )->set(
            $connection->quoteColumn( 'contentobject_attribute_id' ),
            $insertQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
        )->set(
            // @todo: How to handle download_count ?
            $connection->quoteColumn( 'download_count' ),
            $insertQuery->bindValue( $field->value->externalData['downloadCount'], null, \PDO::PARAM_INT )
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

        $insertQuery->prepare()->execute();

        return false;
    }

    /**
     * Removes the prepended mime-type directory from $path for legacy storage.
     *
     * @param string $path
     * @return string
     * @protected
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
     * @return array|void
     */
    public function getFileReferenceData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'download_count' ),
            $connection->quoteColumn( 'filename' ),
            $connection->quoteColumn( 'mime_type' ),
            $connection->quoteColumn( 'original_filename' )
        )->from(
            $connection->quoteTable( 'ezbinaryfile' )
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

        $convertedResult = array();
        foreach ( reset( $result ) as $column => $value )
        {
            $convertedResult[$this->propertyMap[$column]] = $value;
        }
        $convertedResult['path'] = $this->prependMimeToPath(
            $convertedResult['path'],
            $convertedResult['mimeType']
        );

        return $convertedResult;
    }

    /**
     * Prepends $path with the first part of the given $mimeType.
     *
     * @param string $path
     * @param string $mimeType
     * @return string
     * @protected
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
     * @return void
     */
    public function removeFileReferences( array $fieldIds )
    {
        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( 'ezbinaryfile' )
        )->where(
            $deleteQuery->expr->in(
                $connection->quoteColumn( 'contentobject_attribute_id' ),
                $fieldIds
            )
        );

        $deleteQuery->prepare()->execute();
    }

    /**
     * Removes a specific file reference for $fieldId and $versionId
     *
     * @param mixed $fieldId
     * @param int $versionNo
     * @return void
     */
    public function removeFileReference( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( 'ezbinaryfile' )
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
     * @return array
     */
    public function getReferencedFiles( array $fieldIds )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'filename' ),
            $connection->quoteColumn( 'mime_type' )
        )->from(
            $connection->quoteTable( 'ezbinaryfile' )
        )->where(
            $selectQuery->expr->in(
                $connection->quoteColumn( 'contentobject_attribute_id' ),
                $fieldIds
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
     * Returns a map with the number of refereces each file from $files has
     *
     * @param array $files
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
            $connection->quoteTable( 'ezbinaryfile' )
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

