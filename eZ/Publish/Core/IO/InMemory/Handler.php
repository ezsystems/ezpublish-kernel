<?php
/**
 * File containing the eZ\Publish\SPI\IO\Handler interface
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\InMemory;

use eZ\Publish\SPI\IO\Handler as IoHandlerInterface,
    eZ\Publish\SPI\IO\BinaryFile,
    eZ\Publish\SPI\IO\BinaryFileUpdateStruct,
    eZ\Publish\SPI\IO\BinaryFileCreateStruct,
    ezp\Base\Exception\NotFound,
    ezp\Io\Exception\PathExists,
    DateTime;

/**
 * Backend interface for handling of binary files I/O
 */

class Handler implements IoHandlerInterface
{
    /**
     * Files storage
     * @var array
     */
    private $storage;

    /**
     * Actual file data
     * @var array(filepath=>binaryData)
     */
    private $data;

    /**
     * File data resources (handles)
     * @var resource[]
     */
    private $resources = array();

    public function __construct()
    {
        $this->storage = array();
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $file
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     *
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function create( BinaryFileCreateStruct $file )
    {
        if ( isset( $this->storage[$file->path] ) )
        {
            throw new PathExists( $file->path );
        }

        $this->data[$file->path] = base64_encode( fread( $file->getInputStream(), $file->size ) );

        $binaryFile = new BinaryFile();
        $binaryFile->path = $file->path;
        $binaryFile->contentType = $file->contentType;
        $binaryFile->ctime = new DateTime;
        $binaryFile->mtime = clone $binaryFile->ctime;
        $binaryFile->originalFile = $file->originalFile;
        $binaryFile->size = $file->size;
        $this->storage[$binaryFile->path] = $binaryFile;

        return $this->storage[$binaryFile->path];
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @param string $path
     * @throws \ezp\Base\Exception\NotFound If the file doesn't exist
     */
    public function delete( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        unset( $this->storage[$path] );
        unset( $this->data[$path] );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFile
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     *
     * @throws \ezp\Base\Exception\NotFound If the source path doesn't exist
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        // path
        if ( $updateFile->path !== null && $updateFile->path != $path )
        {
            if ( isset( $this->storage[$updateFile->path] ) )
            {
                throw new PathExists( $updateFile->path );
            }

            $oldPath = $path;
            $newPath = $updateFile->path;

            $this->storage[$newPath] = $this->storage[$oldPath];
            $this->data[$newPath] = $this->data[$oldPath];

            $this->storage[$newPath]->path = $newPath;

            unset( $this->storage[$oldPath] );
            unset( $this->data[$oldPath] );

            $path = $newPath;
        }

        $resource = $updateFile->getInputStream();
        if ( $resource !== null )
        {
            $this->data[$path] = base64_encode( fread( $resource, $updateFile->size ) );
            if ( $updateFile->size !== null )
            {
                $this->storage[$path]->size = $updateFile->size;
            }
        }

        if ( $updateFile->mtime !== null && $updateFile->mtime !== $this->storage[$path]->mtime )
        {
            $this->storage[$path]->mtime = $updateFile->mtime;
        }

        if ( $updateFile->ctime !== null && $updateFile->ctime !== $this->storage[$path]->ctime )
        {
            $this->storage[$path]->ctime = $updateFile->ctime;
        }

        return $this->storage[$path];
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     * @return bool
     */
    public function exists( $path )
    {
        return isset( $this->storage[$path] );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @param string $path
     * @return \eZ\Publish\SPI\IO\BinaryFile
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function load( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }
        return $this->storage[$path];
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     * @return resource
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function getFileResource( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }
        $uri = 'data://' . (string)$this->storage[$path]->contentType . ';base64,' . $this->data[$path];

        return fopen( $uri, 'rb' );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @param string $path
     * @return string
     * @throws \ezp\Base\Exception\NotFound if the file couldn't be found
     */
    public function getFileContents( $path )
    {
        if ( !isset( $this->storage[$path] ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }
        return base64_decode( $this->data[$path] );
    }
}
