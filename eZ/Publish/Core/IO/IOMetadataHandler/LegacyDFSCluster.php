<?php
/**
 * File containing the DoctrineDBAL IOMetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\IOMetadataHandler;

use eZ\Publish\Core\IO\IOMetadataHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use RuntimeException;

/**
 * @todo Describe
 * @todo Rename to LegacyStorage ?
 */
class LegacyDFSCluster implements IOMetadataHandler
{
    /** @var Connection */
    private $db;

    /** @var string */
    private $prefix;

    public function __construct( Connection $connection, array $options = array() )
    {
        $this->db = $connection;
        if ( isset( $options['prefix'] ) )
        {
            $this->prefix = trim( $options['prefix'], '/' );
        }
    }

    /**
     * Inserts a new reference to file $spiBinaryFileId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $spiBinaryFileId already exists
     *
     * @param string  $spiBinaryFileId
     * @param integer $mtime
     *
     * @throws RuntimeException if an error occurs creating the record
     */
    public function create( SPIBinaryFileCreateStruct $binaryFileCreateStruct )
    {
        $path = $this->addPrefix( $binaryFileCreateStruct->id );

        try {
            /**
             * @todo what might go wrong here ? Can another process be trying to insert the same image ?
             *       what happens if somebody did ?
             **/
            $stmt = $this->db->prepare(<<<SQL
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
            // @todo BadStateException
            throw new \RuntimeException( "Unexpected rowCount " . $stmt->rowCount() );
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

        $row = array_merge(
            array( 'id' => $spiBinaryFileId ),
            $stmt->fetch( \PDO::FETCH_ASSOC )
        );

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

    protected function addPrefix( $id )
    {
        if ( !isset( $this->prefix ) )
        {
            return $id;
        }

        return sprintf( '%s/%s', $this->prefix, $id );
    }

    /**
     * @throws InvalidBinaryFileIdException
     */
    protected function removePrefix( $prefixedId )
    {
        if ( !isset( $this->prefix ) )
        {
            return $prefixedId;
        }

        if ( strpos( $prefixedId, $this->prefix . '/' ) !== 0 )
        {
            throw new InvalidBinaryFileIdException( $prefixedId );
        }

        return substr( $prefixedId, strlen( $this->prefix ) + 1 );
    }

    public function getMimeType( $spiBinaryFileId )
    {
        $return $this->load( $spiBinaryFileId )->id;
    }

    /**
     * @param $spiBinaryFile
     *
     * @return SPIBinaryFile
     */
    protected function mapArrayToSPIBinaryFile( array $properties )
    {
        if ( !isset( $properties->mimeType ) )
        {
            $properties['mimeType'] = $this->getMimeType( $properties['id'] );
        }

        $spiBinaryFile = new SPIBinaryFile();
        $spiBinaryFile->id = $properties['id'];
        $spiBinaryFile->size = $properties['size'];
        $spiBinaryFile->mtime = $properties['mtime'];
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
        $spiBinaryFile->mimeType = $binaryFileCreateStruct->mimeType;
        $spiBinaryFile->mtime = $binaryFileCreateStruct->mtime;
        $spiBinaryFile->size = $binaryFileCreateStruct->size;
        $spiBinaryFile->mimeType = $binaryFileCreateStruct->mimeType;
        return $spiBinaryFile;
    }
}
