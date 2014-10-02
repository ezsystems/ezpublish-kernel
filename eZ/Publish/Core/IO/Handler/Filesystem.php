<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use DateTime;
use RuntimeException;
use eZ\Publish\Core\IO\MetadataHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filesystem IO handler that uses native functions.
 * Used for integration tests.
 */
class Filesystem implements IOHandlerInterface
{

    /**
     * @var MimeTypeDetector
     */
    protected $mimeTypeDetector;

    /**
     * The storage directory where data is stored
     *
     * Example: var/site/storage
     * @var string
     */
    private $storageDirectory;

    /**
     * The root dir for storage. Defaults to the current working directory.
     * Example: /mnt/binary
     * @var string
     */
    private $rootDirectory;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param MimeTypeDetector $mimeTypeDetector
     * @param array|string $options Possible keys: storage_dir, root_dir.
     *        Supports string instead of array for BC, in which case it is considered as storage_dir.
     * @throws InvalidArgumentException On invalid settings
     * @throws RuntimeException On invalid / not-writable directory
     */
    public function __construct( MimeTypeDetector $mimeTypeDetector, $options = array() )
    {
        $this->mimeTypeDetector = $mimeTypeDetector;

        // BC with < 5.3
        if ( is_string( $options ) )
        {
            $options = array( 'storage_dir' => $options );
        }

        $optionsResolver = new OptionsResolver();
        $this->configureOptions( $optionsResolver );
        $options = $optionsResolver->resolve( $options );

        if ( $options['storage_dir'][0] === '/' )
        {
            throw new InvalidArgumentException( 'storage_dir', 'can not be absolute. Use root_dir.' );
        }

        if ( isset( $options['root_dir'] ) && $options['root_dir'] !== '' )
        {
            $storageDirectory = $options['root_dir'] . '/' . $options['storage_dir'];
            $this->rootDirectory = $options['root_dir'];

        }
        else
        {
            $storageDirectory = $options['storage_dir'];
        }

        if ( !file_exists( $storageDirectory ) )
        {
            if ( !@mkdir( $storageDirectory, 0775, true ) )
            {
                throw new RuntimeException( "Could not create storage directory $storageDirectory" );
            }
        }

        if ( !is_dir( $storageDirectory ) )
        {
            throw new RuntimeException( "Storage directory $storageDirectory doesn't exist" );
        }

        if ( !is_writeable( $storageDirectory ) )
        {
            throw new RuntimeException( "Storage directory $storageDirectory can not be written to" );
        }

        $this->storageDirectory = $options['storage_dir'];
    }

    private function configureOptions( OptionsResolver $optionsResolver )
    {
        $optionsResolver->setRequired( array( 'storage_dir' ) );
        $optionsResolver->setOptional( array( 'root_dir' ) );
        $optionsResolver->setAllowedTypes( array( 'storage_dir' => 'string', 'root_dir' => 'string' ) );
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
            $returnSpiBinaryFileId = $spiBinaryFileId;
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

            $returnSpiBinaryFileId = $updateFileStruct->id;
            $destinationStoragePath = $this->getStoragePath( $returnSpiBinaryFileId );
        }

        // contents
        if ( $updateFileStruct->getInputStream() !== null )
        {
            $outputStream = fopen( $sourceStoragePath, 'wb' );
            stream_copy_to_stream( $updateFileStruct->getInputStream(), $outputStream );
            fclose( $outputStream );
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

        clearstatcache( true, $sourceStoragePath );
        clearstatcache( true, $destinationStoragePath );

        return $this->load( $returnSpiBinaryFileId );
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
        if ( !isset( $this->storageDirectory ) )
        {
            return $spiBinaryFileId;
        }
        else
        {
            return $this->storageDirectory . '/' . $spiBinaryFileId;
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
     * Gets the mime-type of the BinaryFile
     *
     * Example: text/xml
     *
     * @param string $spiBinaryFileId
     * @return string|null
     */
    public function getMimeType( $spiBinaryFileId )
    {
        return $this->mimeTypeDetector->getFromPath( $this->getStoragePath( $spiBinaryFileId ) ) ?: null;
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

        if ( $this->rootDirectory )
        {
            $path = $this->rootDirectory . '/' . $path;
        }
        return $path;
    }

    protected function removePrefix( $spiBinaryFileId )
    {
        if ( !isset( $this->storageDirectory ) )
        {
            return $spiBinaryFileId;
        }

        if ( strpos( $spiBinaryFileId, $this->storageDirectory . '/' ) !== 0 )
        {
            throw new InvalidArgumentException( '$uri', "Storage directory {$this->prefix} not found in {$spiBinaryFileId}" );
        }

        return substr( $spiBinaryFileId, strlen( $this->storageDirectory ) + 1 );
    }

    public function getUri( $spiBinaryFileId )
    {
        return '/' . ( $this->storageDirectory ? $this->storageDirectory . '/' : '') . $spiBinaryFileId;
    }
}
