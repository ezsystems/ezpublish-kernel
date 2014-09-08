<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider\eZDFSFileHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
            },
            false,
            false
        );

        $this->legacyKernel->leaveLegacyRootDir();

        return $fileResource;
    }
}
