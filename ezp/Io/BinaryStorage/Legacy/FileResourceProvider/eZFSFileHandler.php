<?php
/**
 * File containing the ezp\Io\BinaryStorage\Legacy\FileResourceProvider\eZFSFileHandler class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage\Legacy\FileResourceProvider;

use ezp\Io\BinaryStorage\Legacy\FileResourceProvider;

/**
 * This class provides file resource functionnality for a cluster file
 */

class eZFSFileHandler implements FileResourceProvider
{
    /**
     * Returns a read file resource for $clusterFile
     * @param eZClusterFileHandlerInterface $clusterFile Note: no hinting as not all handlers implement the interface
     * @return resource
     */
    public function getResource( $file )
    {
        return fopen( $file->path, 'rb' );
    }
}
?>