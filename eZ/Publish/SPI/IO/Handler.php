<?php
/**
 * File containing the \eZ\Publish\SPI\IO\Handler interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\IO;

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
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createFilestruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createFilestruct );

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path );

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
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct );

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $path );

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $path );

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return resource
     */
    public function getFileResource( $path );

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path );
}
