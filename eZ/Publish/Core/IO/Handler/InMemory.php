<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\InMemory class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IoHandlerInterface;
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DateTime;

/**
 * Backend interface for handling of binary files I/O in memory
 */
class InMemory implements IoHandlerInterface
{
    /**
     * Files storage
     * @var \eZ\Publish\SPI\IO\BinaryFile[]
     */
    private $storage;

    /**
     * Actual file data
     * @var array(filepath=>binaryData)
     */
    private $data;

    /** @var string */
    private $storagePrefix = 'memory:';

    public function __construct( array $storage = array() )
    {
        $this->storage = $storage;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param BinaryFileCreateStruct $createStruct
     *
     * @return BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        if ( isset( $this->storage[$createStruct->id] ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->id",
                "file '{$createStruct->id}' already exists"
            );
        }

        $this->data[$createStruct->id] = base64_encode(
            fread( $createStruct->getInputStream(), $createStruct->size )
        );

        $binaryFile = new BinaryFile();
        $binaryFile->id = $createStruct->id;
        $binaryFile->mtime = new DateTime;
        $binaryFile->size = $createStruct->size;

        return $this->storage[$binaryFile->id] = $binaryFile;
    }

    /**
     * Deletes the existing BinaryFile with path $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $id
     */
    public function delete( $id )
    {
        if ( !isset( $this->storage[$id] ) )
        {
            throw new NotFoundException( 'BinaryFile', $id );
        }

        unset( $this->storage[$id] );
        unset( $this->data[$id] );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\Core\IO\Values\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\Core\IO\Values\BinaryFile The updated BinaryFile
     */
    public function update( $id, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !isset( $this->storage[$id] ) )
        {
            throw new NotFoundException( 'BinaryFile', $id );
        }

        // path
        if ( $updateFileStruct->id !== null && $updateFileStruct->id != $id )
        {
            if ( isset( $this->storage[$updateFileStruct->id] ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->id",
                    "file '{$updateFileStruct->id}' already exists"
                );
            }

            $oldId = $id;
            $newId = $updateFileStruct->id;

            $this->storage[$newId] = $this->storage[$oldId];
            $this->data[$newId] = $this->data[$oldId];

            $this->storage[$newId]->id = $newId;

            unset( $this->storage[$oldId] );
            unset( $this->data[$oldId] );

            $id = $newId;
        }

        $resource = $updateFileStruct->getInputStream();
        if ( $resource !== null )
        {
            $this->data[$id] = base64_encode( fread( $resource, $updateFileStruct->size ) );
            if ( $updateFileStruct->size !== null )
            {
                $this->storage[$id]->size = $updateFileStruct->size;
            }
        }

        if ( $updateFileStruct->mtime !== null && $updateFileStruct->mtime !== $this->storage[$id]->mtime )
        {
            $this->storage[$id]->mtime = $updateFileStruct->mtime;
        }

        return $this->storage[$id];
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $id )
    {
        return isset( $this->storage[$id] );
    }

    /**
     * Loads the BinaryFile identified by $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        if ( !isset( $this->storage[$spiBinaryFileId] ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        return $this->storage[$spiBinaryFileId];
    }

    /**
     * Returns a file resource to the BinaryFile identified by $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        if ( !isset( $this->storage[$spiBinaryFileId] ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        $spiBinaryFileId = 'data://' . $this->storage[$spiBinaryFileId]->mimeType . ';base64,' . $this->data[$spiBinaryFileId];

        return fopen( $spiBinaryFileId, 'rb' );
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
        if ( !isset( $this->storage[$spiBinaryFileId] ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        return base64_decode( $this->data[$spiBinaryFileId] );
    }

    public function getInternalPath( $spiBinaryFileId )
    {
        return $this->storagePrefix . $spiBinaryFileId;
    }

    public function getExternalPath( $path )
    {
        if ( substr( $path, 0, strlen( $this->storagePrefix ) ) != $this->storagePrefix )
        {
            throw new InvalidArgumentException( '$path', "Storage prefix" );
        }

        return substr( $path, strlen( $this->storagePrefix ) + 1 );
    }

    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        // @todo This won't work. InternalPath is NOT a path. Need to write it to disk somehow.
        return $metadataHandler->extract( $this->getInternalPath( $spiBinaryFileId ) );
    }

    public function getUri( $spiBinaryFileId )
    {
        // @todo Not implemented, would require a Front controller
        return 'todo:$path';
    }

}
