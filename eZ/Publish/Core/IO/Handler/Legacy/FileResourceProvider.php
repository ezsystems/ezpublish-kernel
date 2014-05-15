<?php
/**
 * File containing the FileResourceProvider interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
