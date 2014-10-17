<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\IOMetadataHandler;

use DateTime;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\IO\IOMetadataHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\Core\IO\UrlDecorator;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use RuntimeException;

/**
 * Manages IO metadata in a mysql table, ezdfsfile.
 *
 * It will prevent simultaneous writes to the same file.
 */
class LegacyDFSCluster implements IOMetadataHandler
{
    /** @var Connection */
    private $db;

    /** @var UrlDecorator */
    private $urlDecorator;

    /**
     * @param Connection $connection Doctrine DBAL connection
     * @param UrlDecorator $urlDecorator The URL decorator used to add a prefix to files path
     */
    public function __construct( Connection $connection, UrlDecorator $urlDecorator = null )
    {
        $this->db = $connection;
        $this->urlDecorator = $urlDecorator;
    }

    /**
     * Inserts a new reference to file $spiBinaryFileId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $spiBinaryFileId already exists
     *
     * @param string  $spiBinaryFileId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if an error occurs creating the record
     * @throws RuntimeException if a DBAL error occurs
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function create( SPIBinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $path = $this->addPrefix( $binaryFileCreateStruct->id );

        try
        {
            /**
             * @todo what might go wrong here ? Can another process be trying to insert the same image ?
             *       what happens if somebody did ?
             **/
            $stmt = $this->db->prepare(
<<<SQL
INSERT INTO ezdfsfile
  (name, name_hash, name_trunk, mtime, size, scope, datatype)
  VALUES (:name, :name_hash, :name_trunk, :mtime, :size, :scope, :datatype)
ON DUPLICATE KEY UPDATE
  datatype=VALUES(datatype), scope=VALUES(scope), size=VALUES(size),
  mtime=VALUES(mtime), expired=VALUES(expired)
SQL
            );
            $stmt->bindValue( 'name', $path );
            $stmt->bindValue( 'name_hash', md5( $path ) );
            $stmt->bindValue( 'name_trunk', $this->getNameTrunk( $binaryFileCreateStruct ) );
            $stmt->bindValue( 'mtime', $binaryFileCreateStruct->mtime );
            $stmt->bindValue( 'size', $binaryFileCreateStruct->size );
            $stmt->bindValue( 'scope', $this->getScope( $binaryFileCreateStruct ) );
            $stmt->bindValue( 'datatype', $binaryFileCreateStruct->mimeType );
            $stmt->execute();
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( "A DBAL error occured while writing $path", 0, $e );
        }

        if ( $stmt->rowCount() == 0 )
        {
            throw new BadStateException( 'LegacyDFSCluster', "Unexpected rowCount after creating" );
        }

        return $this->mapSPIBinaryFileCreateStructToSPIBinaryFile( $binaryFileCreateStruct );
    }

    /**
     * Deletes file $spiBinaryFileId
     *
     * @throws BinaryFileNotFoundException If $spiBinaryFileId is not found
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        $path = $this->addPrefix( $spiBinaryFileId );

        // Unlike the legacy cluster, the file is directly deleted. It was inherited from the DB cluster anyway
        $stmt = $this->db->prepare( 'DELETE FROM ezdfsfile WHERE name_hash LIKE :name_hash' );
        $stmt->bindValue( 'name_hash', md5( $path ) );
        $stmt->execute();

        if ( $stmt->rowCount() != 1 )
        {
            // Is this really necessary ?
            throw new BinaryFileNotFoundException( $path );
        }
    }

    /**
     * Loads and returns metadata for $spiBinaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @return SPIBinaryFile
     *
     * @throws BinaryFileNotFoundException if no row is found for $spiBinaryFileId
     * @throws DBALException Any unhandled DBAL exception
     */
    public function load( $spiBinaryFileId )
    {
        $path = $this->addPrefix( $spiBinaryFileId );

        $stmt = $this->db->prepare( 'SELECT * FROM ezdfsfile WHERE name_hash LIKE ? AND expired != 1 AND mtime > 0' );
        $stmt->bindValue( 1, md5( $path ) );
        $stmt->execute();

        if ( $stmt->rowCount() == 0 )
        {
            throw new BinaryFileNotFoundException( $path );
        }

        $row = $stmt->fetch( \PDO::FETCH_ASSOC ) + array( 'id' => $spiBinaryFileId );

        return $this->mapArrayToSPIBinaryFile( $row );
    }

    /**
     * Checks if a file $spiBinaryFileId exists
     *
     * @param string $spiBinaryFileId
     *
     * @throws NotFoundException
     * @throws DBALException Any unhandled DBAL exception
     * @return bool
     */
    public function exists( $spiBinaryFileId )
    {
        $path = $this->addPrefix( $spiBinaryFileId );

        $stmt = $this->db->prepare( 'SELECT name FROM ezdfsfile WHERE name_hash LIKE ? and mtime > 0 and expired != 1' );
        $stmt->bindValue( 1, md5( $path ) );
        $stmt->execute();

        return ( $stmt->rowCount() == 1 );
    }

    /**
     * @param SPIBinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return mixed
     */
    protected function getNameTrunk( SPIBinaryFileCreateStruct $binaryFileCreateStruct )
    {
        return $this->addPrefix( $binaryFileCreateStruct->id );
    }

    /**
     * Returns the value for the scope meta field, based on the created file's path.
     *
     * Note that this is slightly incorrect, as it will return binaryfile for media files as well. It is a bit
     * of an issue, but shouldn't be a blocker given that this meta field isn't used that much.
     *
     * @param SPIBinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return string
     */
    protected function getScope( SPIBinaryFileCreateStruct $binaryFileCreateStruct )
    {
        list( $filePrefix ) = explode( '/', $binaryFileCreateStruct->id );

        switch ( $filePrefix )
        {
            case 'images':
                return 'image';

            case 'original':
                return 'binaryfile';
        }

        return 'UNKNOWN_SCOPE';
    }

    /**
     * Adds the internal prefix string to $id
     *
     * @param string $id
     *
     * @return string prefixed id
     */
    protected function addPrefix( $id )
    {
        return isset( $this->urlDecorator ) ? $this->urlDecorator->decorate( $id ) : $id;
    }

    /**
     * Removes the internal prefix string from $prefixedId
     *
     * @param string $prefixedId
     *
     * @return string the id without the prefix
     * @throws InvalidBinaryFileIdException if the prefix isn't found in $prefixedId
     */
    protected function removePrefix( $prefixedId )
    {
        return isset( $this->urlDecorator ) ? $this->urlDecorator->undecorate( $prefixedId ) : $prefixedId;
    }

    public function getMimeType( $spiBinaryFileId )
    {
        $stmt = $this->db->prepare( 'SELECT * FROM ezdfsfile WHERE name_hash LIKE ? AND expired != 1 AND mtime > 0' );
        $stmt->bindValue( 1, md5( $this->addPrefix( $spiBinaryFileId ) ) );
        $stmt->execute();

        if ( $stmt->rowCount() == 0 )
        {
            throw new BinaryFileNotFoundException( $spiBinaryFileId );
        }

        $row = $stmt->fetch( \PDO::FETCH_ASSOC );

        return $row['datatype'];
    }

    /**
     * Maps an array of data base properties (id, size, mtime, datatype, md5_path, path...) to an SPIBinaryFile object
     *
     * @param array $properties database properties array
     *
     * @return SPIBinaryFile
     */
    protected function mapArrayToSPIBinaryFile( array $properties )
    {
        $spiBinaryFile = new SPIBinaryFile();
        $spiBinaryFile->id = $properties['id'];
        $spiBinaryFile->size = $properties['size'];
        $spiBinaryFile->mtime = new DateTime( '@' . $properties['mtime'] );
        $spiBinaryFile->mimeType = $properties['datatype'];
        return $spiBinaryFile;
    }

    /**
     * @param SPIBinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return SPIBinaryFile
     */
    protected function mapSPIBinaryFileCreateStructToSPIBinaryFile( SPIBinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $spiBinaryFile = new SPIBinaryFile();
        $spiBinaryFile->id = $binaryFileCreateStruct->id;
        $spiBinaryFile->mtime = $binaryFileCreateStruct->mtime;
        $spiBinaryFile->size = $binaryFileCreateStruct->size;
        $spiBinaryFile->mimeType = $binaryFileCreateStruct->mimeType;
        return $spiBinaryFile;
    }
}
