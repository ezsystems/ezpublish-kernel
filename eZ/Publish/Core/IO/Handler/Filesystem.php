<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZClusterFileHandler;
use DateTime;
use eZ\Publish\Core\IO\MetadataHandler;

/**
 * Legacy Io/Storage handler, based on eZ Cluster
 *
 * Due to the legacy API, this handler has a few limitations:
 * - ctime is not really supported, and will always have the same value as mtime
 * - mtime can not be modified, and will always automatically be set depending on the server time upon each write operation
 */
class Filesystem implements IOHandlerInterface
{
    /**
     * The storage directory where data is stored
     * Example: var/site/storage
     * @var string
     */
    private $storageDirectory;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param string $storageDirectory
     */
    public function __construct( $storageDirectory )
    {
        if ( !file_exists( $storageDirectory ) || !is_dir( $storageDirectory ) )
        {
            throw new \RuntimeException( "Storage directory $storageDirectory doesn't exist" );
        }
        if ( !is_writeable( $storageDirectory ) )
        {
            throw new \RuntimeException( "Storage directory $storageDirectory can not be written to" );
        }
        $this->storageDirectory = realpath( $storageDirectory );
        if ( $storageDirectory[0] !== DIRECTORY_SEPARATOR )
        {
            $this->prefix = $storageDirectory;
        }
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     * @throws \RuntimeException If the directory in $createStruct->uri exists but is a file
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        if ( $this->exists( $createStruct->uri ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->uri",
                "file '{$createStruct->uri}' already exists"
            );
        }

        $storagePath = $this->getStoragePath( $createStruct->uri );
        $outputDirectory = dirname( $createStruct->uri );
        if ( file_exists( $outputDirectory ) && !is_dir( $outputDirectory ) )
        {
            throw new RuntimeException( "Directory $outputDirectory exists but is a file" );
        }
        if ( !file_exists( $outputDirectory ) )
        {
            $this->createFolderStructure( $outputDirectory );
        }

        $outputStream = fopen( $storagePath, 'wb' );
        stream_copy_to_stream( $createStruct->getInputStream(), $outputStream );
        return $this->load( $createStruct->uri );
    }

    /**
     * Creates in the storage directory folders to the relative path $folderPath
     * @param string $folderPath
     */
    private function createFolderStructure( $folderPath )
    {
        $folderParts = explode( DIRECTORY_SEPARATOR, $folderPath );
        foreach ( $folderParts as $key => $folderPart )
        {
            $folderString = implode(
                DIRECTORY_SEPARATOR,
                array_slice( $folderParts, 0, $key + 1 )
            );
            $folderStringStoragePath = $this->getStoragePath( $folderString );
            if ( file_exists( $folderStringStoragePath ) )
                continue;
            mkdir(
                $folderStringStoragePath
            );
        }
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }
        unlink( $this->getStoragePath( $path ) );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $sourceStoragePath = $this->getStoragePath( $path );

        if ( !isset( $updateFileStruct->uri ) || $updateFileStruct->uri == $path )
        {
            $destinationStoragePath = $this->getStoragePath( $path );
        }
        else
        {
            if ( $this->exists( $updateFileStruct->uri ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->uri",
                    "File '{$updateFileStruct->uri}' already exists"
                );
            }

            $destinationStoragePath = $this->getStoragePath( $updateFileStruct->uri );
        }

        // path
        if ( $destinationStoragePath != $sourceStoragePath )
        {
            $destinationStorageDirectory = dirname( $destinationStoragePath );
            if ( !file_exists( $destinationStorageDirectory ) )
            {
                $this->createFolderStructure( $destinationStorageDirectory );
            }
            rename( $sourceStoragePath, $destinationStoragePath );
        }

        if ( $updateFileStruct->getInputStream() !== null )
        {
            $outputStream = fopen( $sourceStoragePath, 'wb' );
            stream_copy_to_stream( $updateFileStruct->getInputStream(), $outputStream );
            fclose( $outputStream );
        }

        return $this->load( $sourceStoragePath );
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
        return file_exists( $this->getStoragePath( $path ) );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $storagePath = $this->getStoragePath( $path );
        $file = new BinaryFile();
        $file->uri = $path;
        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( filemtime( $storagePath ) );
        $file->size = filesize( $storagePath );

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     *
     * @return resource
     */
    public function getFileResource( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( "BinaryFile", $path );
        }
        return fopen( $this->getStoragePath( $path ), 'rb' );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        return file_get_contents( $this->getStoragePath( $path ) );
    }

    /**
     * Returns the internal, handler level path to $path
     * @param string $path
     * @return string
*/
    public function getInternalPath( $path )
    {
        if ( !isset( $this->prefix ) )
        {
            return $path;
        }
        else
        {
            return $this->prefix . DIRECTORY_SEPARATOR . $path;
        }
    }

    public function getExternalPath( $path )
    {
        return $this->removePrefix( $path );
    }

    /**
     * Executes $metadataHandler on $path, and returns the metadata array
     *
     * @param MetadataHandler $metadataHandler
     * @param string          $path
     *
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, $path )
    {
        return $metadataHandler->extract( $this->getStoragePath( $path ) );
    }

    /**
     * Transforms a path in a storage path using the $storageDirectory
     * @param string $path
     * @return string
     */
    protected function getStoragePath( $path )
    {
        if ( $this->storageDirectory )
            $path = $this->storageDirectory . DIRECTORY_SEPARATOR . $path;
        return $path;
    }

    protected function removePrefix( $uri )
    {
        if ( !isset( $this->prefix ) )
        {
            return $uri;
        }

        if ( strpos( $uri, $this->prefix . DIRECTORY_SEPARATOR ) !== 0 )
        {
            throw new InvalidArgumentException( '$uri', "Prefix {$this->prefix} not found in {$uri}" );
        }

        return substr( $uri, strlen( $this->prefix ) + 1 );
    }
}
