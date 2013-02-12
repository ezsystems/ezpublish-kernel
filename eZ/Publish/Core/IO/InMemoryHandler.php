<?php
/**
 * File containing the eZ\Publish\Core\IO\InMemoryHandler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;

use eZ\Publish\SPI\IO\Handler as IoHandlerInterface;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DateTime;

/**
 * Backend interface for handling of binary files I/O
 */

class InMemoryHandler implements IoHandlerInterface
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

    public function __construct( array $storage = array() )
    {
        $this->storage = $storage;
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createFilestruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createFilestruct )
    {
        if ( isset( $this->storage[$createFilestruct->path] ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->path",
                "file '{$createFilestruct->path}' already exists"
            );
        }

        $this->data[$createFilestruct->path] = base64_encode(
            fread( $createFilestruct->getInputStream(), $createFilestruct->size )
        );

        $binaryFile = new BinaryFile();
        $binaryFile->path = $createFilestruct->path;
        $binaryFile->uri = $createFilestruct->path;
        $binaryFile->mimeType = $createFilestruct->mimeType;
        $binaryFile->ctime = new DateTime;
        $binaryFile->mtime = clone $binaryFile->ctime;
        $binaryFile->originalFile = $createFilestruct->originalFile;
        $binaryFile->size = $createFilestruct->size;

        return $this->storage[$binaryFile->path] = $binaryFile;
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        unset( $this->storage[$path] );
        unset( $this->data[$path] );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        // path
        if ( $updateFileStruct->path !== null && $updateFileStruct->path != $path )
        {
            if ( isset( $this->storage[$updateFileStruct->path] ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->path",
                    "file '{$updateFileStruct->path}' already exists"
                );
            }

            $oldPath = $path;
            $newPath = $updateFileStruct->path;

            $this->storage[$newPath] = $this->storage[$oldPath];
            $this->data[$newPath] = $this->data[$oldPath];

            $this->storage[$newPath]->path = $newPath;

            unset( $this->storage[$oldPath] );
            unset( $this->data[$oldPath] );

            $path = $newPath;
        }

        $resource = $updateFileStruct->getInputStream();
        if ( $resource !== null )
        {
            $this->data[$path] = base64_encode( fread( $resource, $updateFileStruct->size ) );
            if ( $updateFileStruct->size !== null )
            {
                $this->storage[$path]->size = $updateFileStruct->size;
            }
        }

        if ( $updateFileStruct->mtime !== null && $updateFileStruct->mtime !== $this->storage[$path]->mtime )
        {
            $this->storage[$path]->mtime = $updateFileStruct->mtime;
        }

        if ( $updateFileStruct->ctime !== null && $updateFileStruct->ctime !== $this->storage[$path]->ctime )
        {
            $this->storage[$path]->ctime = $updateFileStruct->ctime;
        }

        return $this->storage[$path];
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $path )
    {
        return isset( $this->storage[$path] );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }
        return $this->storage[$path];
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return resource
     */
    public function getFileResource( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }
        $uri = 'data://' . $this->storage[$path]->mimeType . ';base64,' . $this->data[$path];

        return fopen( $uri, 'rb' );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }
        return base64_decode( $this->data[$path] );
    }
}
