<?php
/**
 * File containing the DFS class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler\DFS\MetadataHandler as DFSMetadataHandler;
use eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler as DFSBinaryDataHandler;
use eZ\Publish\Core\Base\Exceptions;
use eZ\Publish\Core\IO\Handler as IOHandler;
use eZ\Publish\Core\IO\MetadataHandler as IOMetadataHandler;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use InvalidArgumentException;

class DFS implements IOHandler
{
    /** @var DFSBinaryDataHandler */
    protected $binaryDataHandler;

    /** @var DFSMetadataHandler */
    protected $metaDataHandler;

    /** @var string */
    protected $storagePrefix;

    /**
     * @param string $storagePrefix
     * @param DFSMetadataHandler $metaDataHandler
     * @param DFSBinaryDataHandler $binaryDataHandler
     */
    public function __construct( $storagePrefix, DFSMetadataHandler $metaDataHandler, DFSBinaryDataHandler $binaryDataHandler )
    {
        $this->storagePrefix = $storagePrefix;
        $this->metaDataHandler = $metaDataHandler;
        $this->binaryDataHandler = $binaryDataHandler;
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
        $this->metaDataHandler->insert( $path, $createStruct->size );
        $this->binaryDataHandler->createFromStream( $path, $createStruct->getInputStream() );
        return $this->load( $createStruct->id );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        $path = $this->addStoragePrefix( $spiBinaryFileId );
        $this->metaDataHandler->delete( $path );
        $this->binaryDataHandler->delete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
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
            throw new Exceptions\NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $sourceStoragePath = $this->addStoragePrefix( $spiBinaryFileId );

        if ( !isset( $updateFileStruct->id ) || $updateFileStruct->id == $spiBinaryFileId )
        {
            $destinationStoragePath = $this->addStoragePrefix( $spiBinaryFileId );
        }
        else
        {
            $destinationStoragePath = $this->addStoragePrefix( $updateFileStruct->id );

            if ( $this->metaDataHandler->exists( $destinationStoragePath ) )
            {
                throw new Exceptions\InvalidArgumentException(
                    "\$updateFileStruct->id",
                    "File '{$updateFileStruct->id}' already exists"
                );
            }
        }

        if ( $destinationStoragePath != $sourceStoragePath )
        {
            $this->metaDataHandler->rename( $sourceStoragePath, $destinationStoragePath );
            $this->binaryDataHandler->rename( $sourceStoragePath, $destinationStoragePath );
        }

        if ( $updateFileStruct->getInputStream() !== null )
        {
            $this->binaryDataHandler->updateFileContents(
                $destinationStoragePath,
                $updateFileStruct->getInputStream()
            );
        }

        return $this->load( $updateFileStruct->id );
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
        return $this->metaDataHandler->exists(
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
        $metadata = $this->metaDataHandler->loadMetadata(
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new Exceptions\NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        return $this->binaryDataHandler->getFileResource( $this->addStoragePrefix( $spiBinaryFileId ) );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        return $this->binaryDataHandler->getFileContents(
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $spiBinaryFileId isn't
     *
     * @return array
     *
     * @todo Consider having specific metadatahandler interfaces: FileMetadataHandler, PathMetadataHandler...
     */
    public function getMetadata( IOMetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        return $this->binaryDataHandler->getMetadata(
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
     *
     * @todo Make it depend on the BinaryDataHandler (right ?)
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
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @return string
     */
    protected function removeStoragePrefix( $path )
    {
        if ( !isset( $this->storagePrefix ) )
        {
            return $path;
        }

        if ( strpos( $path, $this->storagePrefix ) !== 0 )
        {
            throw new Exceptions\InvalidArgumentException(
                '$uri',
                "Prefix {$this->storagePrefix} not found in {$path}"
            );
        }

        return substr( $path, strlen( $this->storagePrefix ) );
    }
}
