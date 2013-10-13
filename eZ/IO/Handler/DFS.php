<?php
/**
 * File containing the DFS class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace BD\Bundle\DFSBundle\eZ\IO\Handler;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Handler as IOHandler;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;

class DFS implements IOHandler
{
    /** @var FSInterface */
    protected $fs;

    /** @var DBInterface */
    protected $db;

    /** @var string */
    protected $storagePrefix;

    /**
     * @param string $storagePrefix
     * @param DBInterface $db
     * @param FSInterface $fs
     */
    public function construct( $storagePrefix, DBInterface $db, FSInterface $fs )
    {
        $this->storagePrefix = $storagePrefix;
        $this->db = $db;
        $this->fs = $fs;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws InvalidArgumentException If the target path already exists
     *
     * @param BinaryFileCreateStruct $createStruct
     *
     * @return BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        $path = $this->addStoragePrefix( $createStruct->id );
        $this->db->insert( $path, $createStruct->mtime );
        $this->fs->createFromStream( $path, $createStruct->getInputStream() );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws NotFoundException If the file doesn't exist
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        $path = $this->addStoragePrefix( $spiBinaryFileId );
        $this->db->delete( $path );
        $this->fs->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws NotFoundException If the source path doesn't exist
     * @throws InvalidArgumentException If the target path already exists
     *
     * @param string $spiBinaryFileId
     * @param BinaryFileUpdateStruct $updateFileStruct
     *
     * @return BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $sourceStoragePath = $this->getStoragePath( $spiBinaryFileId );

        if ( !isset( $updateFileStruct->id ) || $updateFileStruct->id == $spiBinaryFileId )
        {
            $destinationStoragePath = $this->getStoragePath( $spiBinaryFileId );
        }
        else
        {
            if ( $this->exists( $updateFileStruct->id ) )
            {
                throw new \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException(
                    "\$updateFileStruct->id",
                    "File '{$updateFileStruct->id}' already exists"
                );
            }

            $destinationStoragePath = $this->getStoragePath( $updateFileStruct->id );
        }

        if ( $destinationStoragePath != $sourceStoragePath )
        {
            $this->db->rename( $sourceStoragePath, $destinationStoragePath );
            $this->fs->rename( $sourceStoragePath, $destinationStoragePath );
        }

        if ( $updateFileStruct->getInputStream() !== null )
        {
            $this->fs->updateFileContents(
                $destinationStoragePath,
                $updateFileStruct->getInputStream()
            );
        }

        return $this->load( $destinationStoragePath );
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId )
    {
        return $this->db->exists(
            $this->addStoragePrefix( $spiBinaryFileId )
        );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        $metadata = $this->db->loadMetadata(
            $this->addStoragePrefix( $spiBinaryFileId )
        );

        $spiBinaryFile = new BinaryFile;
        $spiBinaryFile->id = $spiBinaryFileId;
        $spiBinaryFile->mtime = new \DateTime( "@{$metadata['mtime']}" );
        $spiBinaryFile->size = $metadata['size'];
        $spiBinaryFile->uri = $this->getUri( $spiBinaryFileId );

        return $spiBinaryFile;
    }

    /**
     * Returns a read only, binary file resource to the BinaryFile identified by $path
     *
     * @throws NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        return $this->fs->getFileResource( $this->addStoragePrefix( $spiBinaryFileId ) );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        return $this->fs->getFileContents(
            $this->addStoragePrefix( $spiBinaryFileId )
        );
    }

    /**
     * Returns the internal, handler level path from the api level $binaryFileId
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getInternalPath( $externalPath )
    {
        return $this->addStoragePrefix( $externalPath );
    }

    /**
     * Removes the internal storage path from $path
     *
     * @param string $apiBinaryFileId
     *
     * @return string
     */
    public function getExternalPath( $path )
    {
        return $this->removeStoragePrefix( $path );
    }

    /**
     * Executes $metadataHandler on $path, and returns the metadata array
     *
     * @param MetadataHandler $metadataHandler
     * @param string $spiBinaryFileId
     *
     * @throws NotFoundException If $spiBinaryFileId isn't
     *
     * @return array
     *
     * @todo Consider having specific metadatahandler interfaces: FileMetadataHandler, PathMetadataHandler...
     */
    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        return $this->fs->getMetadata(
            $metadataHandler,
            $this->addStoragePrefix( $spiBinaryFileId )
        );
    }

    /**
     * Returns the file's public HTTP URI, as exposed from the outside
     * @deprecated should not be required. Seek & destroy.
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getUri( $spiBinaryFileId )
    {
        return '/' . $this->addStoragePrefix( $spiBinaryFileId );
    }

    /**
     * Prefixes $spiBinaryFileId with the storage prefix, if any
     * @param string $spiBinaryFileId
     * @return string
     */
    protected function addStoragePrefix( $spiBinaryFileId )
    {
        return $this->storagePrefix . $spiBinaryFileId;
    }

    /**
     * Removes the storage prefix from $path
     *
     * @param $path
     *
     * @throws InvalidArgumentException
     * @return string
     */
    protected function removeStoragePrefix( $path )
    {
        if ( !isset( $this->storagePrefix ) )
        {
            return $path;
        }

        if ( strpos( $path, $this->storagePrefix . '/' ) !== 0 )
        {
            throw new \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException(
                '$uri',
                "Prefix {$this->storagePrefix} not found in {$path}"
            );
        }

        return substr( $path, strlen( $this->storagePrefix ) + 1 );
    }
}
