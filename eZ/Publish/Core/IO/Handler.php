<?php
/**
 * File containing the \eZ\Publish\SPI\IO\Handler interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\Core\IO\MetadataHandler;

/**
 * Backend interface for handling of binary files I/O
 */
interface Handler
{
    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct );

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId );

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $spiBinaryFileId
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct );

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId );

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     * @param string $spiBinaryFileId
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $spiBinaryFileId );

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId );

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId );

    /**
     * Returns the internal, handler level path from the api level $binaryFileId
     * @param string $spiBinaryFileId
     * @return string
     */
    public function getInternalPath( $spiBinaryFileId );

    /**
     * Removes the internal storage path from $path
     * @param string $apiBinaryFileId
     * @return string
     */
    public function getExternalPath( $apiBinaryFileId );

    /**
     * Executes $metadataHandler on $path, and returns the metadata array
     * @param MetadataHandler $metadataHandler
     * @param string $spiBinaryFileId
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId );

    /**
     * Returns the file's public HTTP URI, as exposed from the outside
     * @deprecated should not be required. Seek & destroy.
     * @param string $spiBinaryFileId
     * @return string
     */
    public function getUri( $spiBinaryFileId );
}
