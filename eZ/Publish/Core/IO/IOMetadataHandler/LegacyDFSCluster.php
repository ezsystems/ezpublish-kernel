<?php
/**
 * File containing the DoctrineDBAL IOMetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\IOMetadataHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Exception\BinaryFileNotFoundException;
use eZ\Publish\Core\IO\Handler\DFS\MetadataHandler;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * @todo Describe
 * @todo Rename to LegacyStorage ?
 */
class LegacyDFSCluster implements MetadataHandler
{
    /** @var Connection */
    private $db;

    public function __construct( Connection $connection )
    {
        $this->db = $connection;
    }

    /**
     * Inserts a new reference to file $spiBinaryFileId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $spiBinaryFileId already exists
     *
     * @param string  $spiBinaryFileId
     * @param integer $mtime
     *
     * @throws DBALException Any unhandled DBAL exception
     *
     * @todo fix exception handling
     */
    public function create( BinaryFileCreateStruct $binaryFileCreateStruct )
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
            $stmt->bindValue( 'scope', '@todo' );
            $stmt->bindValue( 'datatype', $binaryFileCreateStruct->mimeType );
            $stmt->execute();
        }
        catch ( DBALException $e )
        {
            throw $e;
        }

        if ( $stmt->rowCount() == 0 )
        {
            throw new \Exception("@TODO: unexpected rowCount " . $stmt->rowCount());
        }
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

        // @todo delete or expire ? legacy_mode option ?
        $stmt = $this->db->prepare( 'DELETE FROM ezdfsfile WHERE name_hash LIKE :name_hash' );
        $stmt->bindValue( 'name_hash', md5( $path ) );
        $stmt->execute();

        if ( $stmt->rowCount() != 1 )
        {
            throw new BinaryFileNotFoundException( $path );
        }
    }

    /**
     * Loads and returns metadata for $spiBinaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @return array A hash with metadata for $spiBinaryFileId. Keys: mtime, size.
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

        return $stmt->fetch( \PDO::FETCH_ASSOC );
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
     * @param BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return mixed
     */
    protected function getNameTrunk( BinaryFileCreateStruct $binaryFileCreateStruct )
    {
        // @todo fixme
        return $binaryFileCreateStruct->id;
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
}
