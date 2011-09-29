<?php
/**
 * File containing the FileResourceProvider interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage\Legacy;

/**
 * This interface handles providing of a file resource based on a cluster handler / cluster file
 */

interface FileResourceProvider
{
    /**
     * Returns a file resource for $clusterFile
     * @param eZClusterFileHandlerInterface $clusterFile Note: no hinting as not all handlers implement the interface
     * @return resource
     */
    public function getResource( $clusterFile );
}
