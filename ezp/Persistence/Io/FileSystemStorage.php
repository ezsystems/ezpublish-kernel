<?php
/**
 * File containing the FileSystemStorage class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Io;

/**
 */
class FileSystemStorage implements Interfaces\BinaryFileStorage
{
    /**
     */
    public $contains;

    /**
     * @param string $fileIdentifier
     * @return boolean
     */
    public function storeFile( $fileIdentifier )
    {
        // Not yet implemented
    }

    /**
     * @param string $fileIdentifier
     * @return FileReference
     */
    public function getFile( $fileIdentifier )
    {
        // Not yet implemented
    }

    /**
     * @param string $fileIdentifier
     * @return FileChunk
     */
    public function streamFile( $fileIdentifier )
    {
        // Not yet implemented
    }

    /**
     * @param string $fileIdentifier
     * @return boolean
     */
    public function exists( $fileIdentifier )
    {
        // Not yet implemented
    }

    /**
     */
    public function authenticate()
    {
        // Not yet implemented
    }
}
?>
