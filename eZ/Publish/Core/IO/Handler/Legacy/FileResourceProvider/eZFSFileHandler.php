<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider\eZFSFileHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider;

use eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider;

/**
 * This class provides file resource functionality for a cluster file
 */
class eZFSFileHandler extends BaseHandler implements FileResourceProvider
{
    public function getResource( $storagePath )
    {
        $this->legacyKernel->enterLegacyRootDir();
        $fh = fopen( $storagePath, 'rb' );
        $this->legacyKernel->leaveLegacyRootDir();

        return $fh;
    }
}
