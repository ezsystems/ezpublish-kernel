<?php
/**
 * File containing the BinaryFileStorage interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_io
 */

namespace ezp\Persistence\Io\Interfaces;

/**
 * @package ezp
 * @subpackage persistence_io
 */
interface BinaryFileStorage
{

    /**
     * @param string $fileIdentifier
     * @return boolean
     */
    public function storeFile( $fileIdentifier );

    /**
     * @param string $fileIdentifier
     * @return FileReference
     */
    public function getFile( $fileIdentifier );

    /**
     * @param string $fileIdentifier
     * @return FileChunk
     */
    public function streamFile( $fileIdentifier );

    /**
     * @param string $fileIdentifier
     * @return boolean
     */
    public function exists( $fileIdentifier );

    /**
     */
    public function authenticate();
}
?>
