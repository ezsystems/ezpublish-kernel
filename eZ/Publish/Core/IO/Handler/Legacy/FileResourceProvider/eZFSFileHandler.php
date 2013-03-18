<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider\eZFSFileHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
