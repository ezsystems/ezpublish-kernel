<?php
/**
 * File containing the ezp\Io\BinaryStorage\Handler interface
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage;

/**
 * Interface for handling of binary files I/O
 */

interface Handler
{
    /**
     * Stores $file
     * @param BinaryFile $file
     */
    public function storeFile( BinaryFile $file );

    /**
     * Returns a file resource to $file
     * @param string $fileIdentifier
     * @return resource
     */
    public function getFileResource()
}
?>