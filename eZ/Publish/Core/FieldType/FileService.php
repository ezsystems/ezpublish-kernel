<?php
/**
 * File containing the FileService interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\IO\Values\BinaryFile;

/**
 * Provides interaction with the IO API for FieldTypes
 */
class FileService
{
    /**
     * @var \eZ\Publish\Core\IO\IOService
     */
    protected $IOService;

    /**
     * @param \eZ\Publish\Core\IO\IOService $IOService
     * @param string                        $installDir
     * @param string                        $storageDir
     * @param string                        $identifierPrefix
     */
    public function __construct( IOService $IOService, $installDir, $storageDir, $identifierPrefix = '' )
    {
        $this->IOService = $IOService;
        $this->installDir = $installDir;
        $this->storageDir = $storageDir;
        $this->identifierPrefix = $identifierPrefix;
    }

    /**
     * Store the local file identified by $sourcePath in a location that corresponds
     * to $storageIdentifier. Returns a storage identifier used inside the
     * storage (might differ from the incoming $storageIdentifier).
     *
     * @param string $sourcePath
     * @param string $storageIdentifier
     *
     * @return string
     */
    public function storeFile( $sourcePath, $storageIdentifier )
    {
        $createStruct = $this->IOService->newBinaryCreateStructFromLocalFile( $sourcePath );
        $createStruct->uri = $storageIdentifier;

        $this->IOService->createBinaryFile( $createStruct );
    }

    /**
     * Uses $metadataHandler to extract metadata from $storageIdentifier
     * @param MetadataHandler $metadataHandler
     * @param string $storageIdentifier
     * @return array|bool metadata array, or false if no file matched $storageIdentifier
     */
    public function getMetadata( MetadataHandler $metadataHandler, $storageIdentifier )
    {
        if ( !$binaryFile = $this->loadBinaryFile( $storageIdentifier ) )
            return false;

        $temporaryFileName = tempnam( '/tmp', 'io' );
        if ( !is_writable( $temporaryFileName ) )
            throw new \RuntimeException( "Unable to open temporary file $temporaryFileName for writing" );

        $fh = fopen( $temporaryFileName, 'wb' );
        fputs( $fh, $this->IOService->getFileContents( $binaryFile ) );
        fclose( $fh );

        $metadata = $metadataHandler->extract( $temporaryFileName );
        unlink( $temporaryFileName );

        return $metadata;
    }

    /**
     * Returns the file size of the file identified by $storageIdentifier
     *
     * @param string $storageIdentifier
     *
     * @return int|bool the size in bytes, or false if the file wasn't found
     */
    public function getFileSize( $storageIdentifier )
    {
        if ( !$binaryFile = $this->loadBinaryFile( $storageIdentifier ) )
            return false;

        return $binaryFile->size;
    }

    /**
     * Loads a file by identifier
     * @return \eZ\Publish\Core\IO\Values\BinaryFile|bool the BinaryFile, or false if it doesn't exist
     */
    protected function loadBinaryFile( $storageIdentifier )
    {
        return $this->IOService->loadBinaryFile( $storageIdentifier );
    }

    /**
     * Removes the path identified by $storageIdentifier, potentially
     * $recursive.
     *
     * Attempts to removed the path identified by $storageIdentifier. If
     * $storageIdentifier is a directory which is not empty and $recursive is
     * set to false, an exception is thrown. Attempting to remove a non
     * existing $storageIdentifier is silently ignored.
     *
     * @param string $storageIdentifier
     *
     * @return void
     * @throws \RuntimeException if $storageIdentifier could not be removed (most
     *                           likely permission issues)
     */
    public function remove( $storageIdentifier )
    {
        if ( !$binaryFile = $this->loadBinaryFile( $storageIdentifier ) )
            return;

        $this->IOService->deleteBinaryFile( $binaryFile );
    }

    /**
     * Returns a storage identifier for the given $path
     *
     * The storage identifier is used to identify $path inside the storage
     * encapsulated by the file service.
     *
     * @param string $path
     *
     * @return string
     */
    public function getStorageIdentifier( $path )
    {
        $storageIdentifier = ( !empty( $this->identifierPrefix )
            ? $this->identifierPrefix . '/'
            : '' ) . $path;
        return $storageIdentifier;
    }

    /**
     * Returns is a file with the given $storageIdentifier exists
     *
     * @param string $storageIdentifier
     *
     * @return boolean
     */
    public function exists( $storageIdentifier )
    {
        return $this->IOService->loadBinaryFile( $storageIdentifier ) instanceof BinaryFile;
    }
}
