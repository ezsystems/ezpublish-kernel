<?php
/**
 * File containing the FileResourceProvider interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler\Legacy;

/**
 * This interface handles providing of a file resource based on a stored cluster path
 */

interface FileResourceProvider
{
    /**
     * Returns a read file resource for legacy path $path
     * @param string $storagePath
     * @return resource
     */
    public function getResource( $storagePath );
}
