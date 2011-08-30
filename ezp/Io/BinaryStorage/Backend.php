<?php
/**
 * File containing the ezp\Io\BinaryStorage\Backend interface
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage;

use ezp\Io\BinaryFile, ezp\Io\BinaryFileUpdateStruct, ezp\Io\BinaryFileCreateStruct;

/**
 * Backend interface for handling of binary files I/O
 */

interface Backend
{
    /**
     * Creates and stores a new BinaryFile based on the create data of $file
     * @param BinaryFileCreateStruct $file
     * @return BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $file );

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param BinaryFileUpdateStruct $updateFile
     *
     * @return BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile );

    /**
     * Deletes the existing BinaryFile with path $path
     * @param string $path
     * @throws Exception if $file doesn't exist
     */
    public function delete( $file );

    /**
     * Checks if the BinaryFile with path $path exists
     * @paraam string $file
     */
    public function exists( $file );

    /**
     * Loads the BinaryFile identified by $path
     * @param string $path
     * @return BinaryFile
     */
    public function load( $path );

    /**
     * Returns a file resource to the BinaryFile $file
     * @param BinaryFile $file
     * @return resource
     */
    public function getFileResource( BinaryFile $file );
}
?>