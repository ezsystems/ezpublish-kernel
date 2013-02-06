<?php
/**
 * File containing the eZ\Publish\Core\IO\Legacy\FileResourceProvider\eZFSFileHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\LegacyHandler\FileResourceProvider;

use eZ\Publish\Core\IO\LegacyHandler\FileResourceProvider;

/**
 * This class provides file resource functionality for a cluster file
 */

class eZFSFileHandler extends BaseHandler implements FileResourceProvider
{
    /**
     * Returns a read file resource for $clusterFile
     * @param \eZClusterFileHandlerInterface $clusterFile Note: no hinting as not all handlers implement the interface
     *
     * @return resource
     */
    public function getResource( $file )
    {
        $this->legacyKernel->enterLegacyRootDir();
        $fh = fopen( $file->path, 'rb' );
        $this->legacyKernel->leaveLegacyRootDir();

        return $fh;
    }
}
