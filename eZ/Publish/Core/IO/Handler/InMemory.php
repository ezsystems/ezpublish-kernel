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
        if ( isset( $this->storage[$createStruct->uri] ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->uri",
                "file '{$createStruct->uri}' already exists"
            );
        }

        $this->data[$createStruct->uri] = base64_encode(
            fread( $createStruct->getInputStream(), $createStruct->size )
        );

        $binaryFile = new BinaryFile();
        $binaryFile->uri = $createStruct->uri;
        $binaryFile->mtime = new DateTime;
        $binaryFile->size = $createStruct->size;

        return $this->storage[$binaryFile->uri] = $binaryFile;
    }

    /**
     * Deletes the existing BinaryFile with path $uri
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $uri
     */
    public function delete( $uri )
    {
        if ( !isset( $this->storage[$uri] ) )
        {
            throw new NotFoundException( 'BinaryFile', $uri );
        }

        unset( $this->storage[$uri] );
        unset( $this->data[$uri] );
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
    public function update( $uri, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !isset( $this->storage[$uri] ) )
        {
            throw new NotFoundException( 'BinaryFile', $uri );
        }

        // path
        if ( $updateFileStruct->uri !== null && $updateFileStruct->uri != $uri )
        {
            if ( isset( $this->storage[$updateFileStruct->uri] ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->uri",
                    "file '{$updateFileStruct->uri}' already exists"
                );
            }

            $oldUri = $uri;
            $newUri = $updateFileStruct->uri;

            $this->storage[$newUri] = $this->storage[$oldUri];
            $this->data[$newUri] = $this->data[$oldUri];

            $this->storage[$newUri]->uri = $newUri;

            unset( $this->storage[$oldUri] );
            unset( $this->data[$oldUri] );

            $uri = $newUri;
        }

        $resource = $updateFileStruct->getInputStream();
        if ( $resource !== null )
        {
            $this->data[$uri] = base64_encode( fread( $resource, $updateFileStruct->size ) );
            if ( $updateFileStruct->size !== null )
            {
                $this->storage[$uri]->size = $updateFileStruct->size;
            }
        }

        if ( $updateFileStruct->mtime !== null && $updateFileStruct->mtime !== $this->storage[$uri]->mtime )
        {
            $this->storage[$uri]->mtime = $updateFileStruct->mtime;
        }

        return $this->storage[$uri];
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $uri )
    {
        return isset( $this->storage[$uri] );
    }

    /**
     * Loads the BinaryFile identified by $uri
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $uri
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $uri )
    {
        if ( !isset( $this->storage[$uri] ) )
        {
            throw new NotFoundException( 'BinaryFile', $uri );
        }
        return $this->storage[$uri];
    }

    /**
     * Returns a file resource to the BinaryFile identified by $uri
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $uri
     *
     * @return resource
     */
    public function getFileResource( $uri )
    {
        if ( !isset( $this->storage[$uri] ) )
        {
            throw new NotFoundException( 'BinaryFile', $uri );
        }
        $uri = 'data://' . $this->storage[$uri]->mimeType . ';base64,' . $this->data[$uri];

        return fopen( $uri, 'rb' );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $uri
     *
     * @return string
     */
    public function getFileContents( $uri )
    {
        if ( !isset( $this->storage[$uri] ) )
        {
            throw new NotFoundException( 'BinaryFile', $uri );
        }
        return base64_decode( $this->data[$uri] );
    }

    public function getInternalPath( $path )
    {
        return $this->storagePrefix . $path;
    }

    public function getExternalPath( $path )
    {
        if ( substr( $path, 0, strlen( $this->storagePrefix ) ) != $this->storagePrefix )
        {
            throw new InvalidArgumentException( '$path', "Storage prefix" );
        }

        return substr( $path, strlen( $this->storagePrefix ) + 1 );
    }

    public function getMetadata( MetadataHandler $metadataHandler, $path )
    {
        // @todo This won't work. InternalPath is NOT a path. Need to write it to disk somehow.
        return $metadataHandler->extract( $this->getInternalPath( $path ) );
    }
}
