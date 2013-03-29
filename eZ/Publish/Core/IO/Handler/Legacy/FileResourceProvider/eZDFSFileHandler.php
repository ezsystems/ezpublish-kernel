<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider\eZDFSFileHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider;

use RuntimeException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider;

/**
 * This class provides file resource functionality for a cluster file
 */
class eZDFSFileHandler extends BaseHandler implements FileResourceProvider
{
    /**
     * @throws RuntimeException if the legacy kernel isn't set to DFS
     * @throws NotFoundException If the storage path isn't found
     */
    public function getResource( $storagePath )
    {
        $this->legacyKernel->enterLegacyRootDir();

        $fileResource = $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath )
            {
                /** @var $ini \eZINI */
                $ini = \eZINI::instance( 'file.ini' );
                $clusterHandler = $ini->variable( 'ClusteringSettings', 'FileHandler' );

                if ( $clusterHandler != 'eZDFSFileHandler' )
                {
                    throw new RuntimeException( "eZDFSFileHandler isn't the active file handler (active: $clusterHandler)" );
                }

                $dfsMountPointPath = $ini->variable( 'eZDFSClusteringSettings', 'MountPointPath' );
                $dfsPath = $dfsMountPointPath . DIRECTORY_SEPARATOR . $storagePath;
                if ( !file_exists( $dfsPath ) )
                {
                    throw new NotFoundException( "dfsPath", $dfsPath );
                }
                return fopen( $dfsPath, 'rb' );
            }
        );

        $this->legacyKernel->leaveLegacyRootDir();

        return $fileResource;
    }
}
