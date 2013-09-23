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
     * @throws \RuntimeException If the directory in $createStruct->spiBinaryFileIdi exists but is a file
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        if ( $this->exists( $createStruct->id ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->id",
                "file '{$createStruct->id}' already exists"
            );
        }

        $storagePath = $this->getStoragePath( $createStruct->id );
        $outputDirectory = dirname( $createStruct->id );
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
        return $this->load( $createStruct->id );
    }

    /**
     * Creates in the storage directory folders to the relative path $folderPath
     * @param string $folderPath
     */
    private function createFolderStructure( $folderPath )
    {
        $folderParts = explode( '/', $folderPath );
        foreach ( $folderParts as $key => $folderPart )
        {
            $folderString = implode(
                '/',
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
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        unlink( $this->getStoragePath( $spiBinaryFileId ) );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $spiBinaryFileId
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
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
                throw new InvalidArgumentException(
                    "\$updateFileStruct->id",
                    "File '{$updateFileStruct->id}' already exists"
                );
            }

            $destinationStoragePath = $this->getStoragePath( $updateFileStruct->id );
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
     * @param string $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId )
    {
        return file_exists( $this->getStoragePath( $spiBinaryFileId ) );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $storagePath = $this->getStoragePath( $spiBinaryFileId );
        $file = new BinaryFile();
        $file->id = $spiBinaryFileId;
        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( filemtime( $storagePath ) );
        $file->size = filesize( $storagePath );
        $file->uri = $this->getUri( $spiBinaryFileId );

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( "BinaryFile", $spiBinaryFileId );
        }
        return fopen( $this->getStoragePath( $spiBinaryFileId ), 'rb' );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        return file_get_contents( $this->getStoragePath( $spiBinaryFileId ) );
    }

    /**
     * Returns the internal, handler level path to $path
     * @param string $spiBinaryFileId
     * @return string
*/
    public function getInternalPath( $spiBinaryFileId )
    {
        if ( !isset( $this->prefix ) )
        {
            return $spiBinaryFileId;
        }
        else
        {
            return $this->prefix . '/' . $spiBinaryFileId;
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
     * @param string          $spiBinaryFileId
     *
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        return $metadataHandler->extract( $this->getStoragePath( $spiBinaryFileId ) );
    }

    /**
     * Transforms a path in a storage path using the $storageDirectory
     * @param string $path
     * @return string
     */
    protected function getStoragePath( $path )
    {
        if ( $this->storageDirectory )
            $path = $this->storageDirectory . '/' . $path;
        return $path;
    }

    protected function removePrefix( $spiBinaryFileId )
    {
        if ( !isset( $this->prefix ) )
        {
            return $spiBinaryFileId;
        }

        if ( strpos( $spiBinaryFileId, $this->prefix . '/' ) !== 0 )
        {
            throw new InvalidArgumentException( '$uri', "Prefix {$this->prefix} not found in {$spiBinaryFileId}" );
        }

        return substr( $spiBinaryFileId, strlen( $this->prefix ) + 1 );
    }

    public function getUri( $spiBinaryFileId )
    {
        return '/' . ( $this->prefix ? $this->prefix . '/' : '') . $spiBinaryFileId;
    }
}
