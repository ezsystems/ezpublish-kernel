<?php
/**
 * File containing the ezp\Io\Handler interface
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;

use ezp\Io\BinaryFile,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\BinaryFileCreateStruct;

/**
 * Backend interface for handling of binary files I/O
 */

interface Handler
{
    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @param \ezp\Io\BinaryFileCreateStruct $file
     * @return \ezp\Io\BinaryFile The newly created BinaryFile object
     *
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function create( BinaryFileCreateStruct $file );

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @param string $path
     * @throws \ezp\Base\Exception\NotFound If the file doesn't exist
     */
    public function delete( $path );

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param \ezp\Io\BinaryFileUpdateStruct $updateFile
     * @return \ezp\Io\BinaryFile The updated BinaryFile
     *
     * @throws \ezp\Base\Exception\NotFound If the source path doesn't exist
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile );

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     * @return bool
     */
    public function exists( $file );

    /**
     * Loads the BinaryFile identified by $path
     *
     * @param string $path
     * @return \ezp\Io\BinaryFile
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function load( $path );

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     * @return resource
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function getFileResource( $path );

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @param string $path
     * @return string
     * @throws \ezp\Base\Exception\NotFound if the file couldn't be found
     */
    public function getFileContents( $path );
}
