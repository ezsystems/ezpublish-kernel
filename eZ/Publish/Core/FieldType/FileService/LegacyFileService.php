<?php
/**
 * File containing the LocalFileService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\FileService;

use eZ\Publish\SPI\FieldType\FileService;
use RuntimeException;

/**
 * An implementation of FieldType\FileService that uses the Legacy eZ Publish Cluster
 */
class LegacyFileService implements FileService
{
    /**
     * eZ Publish base install dir
     *
     * @var string
     */
    protected $installDir;

    /**
     * Storage dir path (relative to $installDir)
     *
     * @var string
     */
    protected $storageDir;

    /**
     * Identifier prefix, will be appended to $storageDir for storing, but also
     * delivered as part of the storage identifier.
     *
     * @var string
     */
    protected $identifierPrefix;

    /**
     * @var \Closure
     */
    protected $kernelClosure;

    /**
     * @var eZClusterFileHandler
     */
    protected $clusterHandler;

    /**
     * @param callable $kernelClosure
     * @param string   $installDir
     * @param          $storageDir
     * @param string   $identifierPrefix
     */
    public function __construct( \Closure $kernelClosure, $installDir, $storageDir, $identifierPrefix = '' )
    {
        $this->kernelClosure = $kernelClosure;
        $this->installDir = $installDir;
        $this->storageDir = $storageDir;
        $this->identifierPrefix = $identifierPrefix;

        echo __METHOD__ . "\n";
        print_r(
            array(
                '$this->installDir' => $this->installDir,
                '$this->storageDir' => $this->storageDir,
                '$this->identifierPrefix' => $this->identifierPrefix,
            )
        );
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    public function getLegacyKernel()
    {
        $legacyKernelClosure = $this->kernelClosure;
        return $legacyKernelClosure();
    }

    /**
     * Lazy loads eZClusterFileHandler
     *
     * @return \eZClusterFileHandler
     */
    private function getClusterHandler()
    {
        if ( $this->clusterHandler === null )
        {
            $this->clusterHandler = $this->getLegacyKernel()->runCallback(
                function ()
                {
                    return \eZClusterFileHandler::instance();
                },
                false
            );
        }

        return $this->clusterHandler;
    }

    /**
     * Returns the target storage path for $path
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTargetPath( $path )
    {
        return ( !empty( $this->storageDir ) ? $this->storageDir . '/' : '' ) . $path;
    }

    /**
     * Returns the full path for $path
     *
     * @param string $path
     * @param bool $allowLocal
     *
     * @return string
     */
    protected function getFullPath( $path, $allowLocal = false )
    {
        if ( $allowLocal && substr( $path, 0, 1 ) === '/' )
        {
            return $path;
        }
        return $this->installDir . '/' . ( !empty( $this->storageDir ) ? $this->storageDir . '/' : '' ) . $path;
    }

    /**
     * Store the local file identified by $sourcePath in a location that corresponds
     * to $storageIdentifier. Returns an $storageIdentifier again.
     *
     * @param string $sourcePath
     * @param string $storageIdentifier
     *
     * @return string
     */
    public function storeFile( $sourcePath, $storageIdentifier )
    {
        echo "Attempting to store file $sourcePath with storage id $storageIdentifier\n";
        $fullSourcePath = $this->getFullPath( $sourcePath, true );
        $targetPath = $this->getTargetPath( $storageIdentifier );

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function () use ( $clusterHandler, $fullSourcePath, $targetPath )
            {
                // @todo Build a path / scope mapper
                $scope = 'todo';
                $clusterHandler->fileStoreContents(
                    $targetPath,
                    fread( $fullSourcePath, filesize( $fullSourcePath ) ),
                    'application/todo',
                    $scope
                );
            }
        );

        return $storageIdentifier;
    }

    /**
     * Removes the path identified by $storageIdentifier, potentially
     * $recursive.
     *
     * Attempts to removed the path identified by $storageIdentifier. If
     * $storageIdentifier is a directory which is not empty and $recursive is
     * set to false, an exception is thrown. Attempting to remove a non
     * existing $storageIdentifier is silently ignored.
     *
     * @param string $storageIdentifier
     * @param boolean $recursive
     *
     * @return void
     * @throws \RuntimeException if children of $storageIdentifier exist and
     *                           $recursive is false
     * @throws \RuntimeException if $storageIdentifier could not be removed (most
     *                           likely permission issues)
     */
    public function remove( $storageIdentifier, $recursive = false )
    {
        $targetPath = $this->getTargetPath( $storageIdentifier );

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function () use ( $clusterHandler, $targetPath, $recursive )
            {
                $clusterHandler->fileDelete(
                    $targetPath,
                    $recursive
                );
            }
        );
    }

    /**
     * Returns a storage identifier for the given $path
     *
     * The storage identifier is used to identify $path inside the storage
     * encapsulated by the file service.
     *
     * @param string $path
     *
     * @return string
     */
    public function getStorageIdentifier( $path )
    {
        $storageIdentifier = ( !empty( $this->identifierPrefix )
            ? $this->identifierPrefix . '/'
            : '' ) . $path;
        echo "Converting path $path to storageIdentifier $storageIdentifier\n";
        return $storageIdentifier;
    }

    /**
     * Returns a hash of meta data
     *
     * array(
     *  'width' => <int>,
     *  'height' => <int>,
     *  'mime' => <string>,
     * );
     *
     * @param string $storageIdentifier
     *
     * @return array
     */
    public function getMetaData( $storageIdentifier )
    {
        // Does not depend on GD
        $metaData = getimagesize( $this->getFullPath( $storageIdentifier ) );

        return array(
            'width' => $metaData[0],
            'height' => $metaData[1],
            'mime' => $metaData['mime'],
        );
    }

    /**
     * Returns the file size of the file identified by $storageIdentifier
     *
     * @param string $storageIdentifier
     *
     * @return int
     */
    public function getFileSize( $storageIdentifier )
    {
        $clusterHandler = $this->getClusterHandler(
            $this->getTargetPath( $storageIdentifier )
        );
        return $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function() use( $clusterHandler )
            {
                return $clusterHandler->size();
            }
        );
    }

    /**
     * Returns true if a file with the given $storageIdentifier exists
     *
     * @param string $storageIdentifier
     *
     * @return boolean
     */
    public function exists( $storageIdentifier )
    {
        $targetPath = $this->getTargetPath( $storageIdentifier );

        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function() use( $clusterHandler, $targetPath )
            {
                return $clusterHandler->fileExists( $targetPath );
            }
        );
    }
}
