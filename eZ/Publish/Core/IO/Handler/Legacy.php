<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\SPI\IO\BinaryFile;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZClusterFileHandler;
use DateTime;
use finfo;

/**
 * Legacy Io/Storage handler, based on eZ Cluster
 *
 * Due to the legacy API, this handler has a few limitations:
 * - ctime is not really supported, and will always have the same value as mtime
 * - mtime can not be modified, and will always automatically be set depending on the server time upon each write operation
 */
class Legacy implements IOHandlerInterface
{
    /**
     * File resource provider
     * @see getFileResourceProvider
     */
    private $fileResourceProvider = null;

    /**
     * Cluster handler instance
     * @var \eZClusterFileHandlerInterface
     */
    private $clusterHandler = null;

    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * The storage directory where data is stored
     * Example: var/site/storage
     * @var string
     */
    private $varDirectory;

    /**
     * Created Legacy handler instance
     *
     * @param string $storageDirectory
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel $legacyKernel
     */
    public function __construct( $varDirectory, LegacyKernel $legacyKernel = null )
    {
        if ( $legacyKernel )
        {
            $this->legacyKernelClosure = $legacyKernel;
        }
        $this->varDirectory = $varDirectory;
    }

    public function setLegacyKernelClosure( \Closure $kernelClosure )
    {
        $this->legacyKernelClosure = $kernelClosure;
    }

    public function setLegacyKernel( LegacyKernel $kernel )
    {
        $this->legacyKernel = $kernel;
    }

    /**
     * @return LegacyKernel
     */
    protected function getLegacyKernel()
    {
        $kernelClosure = $this->legacyKernelClosure;
        print_r( $this->legacyKernelClosure );
        return $kernelClosure();
    }

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createStruct )
    {
        if ( $this->exists( $createStruct->uri ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->uri",
                "file '{$createStruct->uri}' already exists"
            );
        }

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $createStruct, $clusterHandler )
            {
                // @todo Build a path / scope mapper
                $scope = 'todo';
                $clusterHandler->fileStoreContents(
                    $createStruct->uri,
                    fread( $createStruct->getInputStream(), $createStruct->size ),
                    $createStruct->mimeType,
                    $scope
                );
            },
            false
        );

        return $this->load( $createStruct->uri );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $path, $clusterHandler )
            {
                $clusterHandler->fileDelete( $path );
            },
            false
        );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $clusterHandler = $this->getClusterHandler();
        $path = $this->getLegacyKernel()->runCallback(
            function () use ( $path, $updateFileStruct, $clusterHandler )
            {
                // path
                if ( $updateFileStruct->uri !== null && $updateFileStruct->uri != $path )
                {
                    if ( $clusterHandler->fileExists( $updateFileStruct->uri ) )
                    {
                        throw new InvalidArgumentException(
                            "\$updateFileStruct->uri",
                            "file '{$updateFileStruct->uri}' already exists"
                        );
                    }
                    $clusterHandler->fileMove( $path, $updateFileStruct->uri );

                    // update the path we are working on
                    $path = $updateFileStruct->uri;
                }

                $resource = $updateFileStruct->getInputStream();
                if ( $resource !== null )
                {
                    $binaryUpdateData = fread( $resource, $updateFileStruct->size );
                    $clusterFile = eZClusterFileHandler::instance( $path );
                    $metaData = $clusterFile->metaData;
                    $scope = isset( $metaData['scope'] ) ? $metaData['scope'] : false;
                    $datatype = isset( $metaData['datatype'] ) ? $metaData['datatype'] : false;
                    $clusterFile->storeContents( $binaryUpdateData, $scope, $datatype );
                }

                // mtime and ctime have no effect as mtime isn't modifiable, and ctime isn't really supported (=mtime)
                return $path;
            },
            false
        );

        return $this->load( $path );
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists( $path )
    {
        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            function () use ( $clusterHandler, $path )
            {
                return $clusterHandler->fileExists( $path );
            },
            false
        );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $path
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $metaData = $this->getLegacyKernel()->runCallback(
            function () use ( $path )
            {
                $clusterFile = eZClusterFileHandler::instance( $path );
                return $clusterFile->metaData;
            },
            false
        );

        $file = new BinaryFile();
        $file->uri = $path;

        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( $metaData['mtime'] );

        // Setting the same timestamp as mtime, since ctime is not supported in Legacy
        $file->ctime = clone $file->mtime;

        $file->size = $metaData['size'];

        // will only work with some ClusterFileHandlers (DB based ones, not with FS ones)
        if ( isset( $metaData['datatype'] ) )
        {
            $file->mimeType = $metaData['datatype'];
        }
        else
        {
            $file->mimeType = $this->getMimeTypeFromLocalFile( $path );
        }

        $file->uri = $file->uri;
        $file->originalFile = basename( $file->uri );

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     *
     * @return resource
     */
    public function getFileResource( $path )
    {
        return $this->getFileResourceProvider()->getResource( $this->load( $path ) );// @todo incorrect object provided to getResource?
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            function () use ( $path, $clusterHandler )
            {
                return $clusterHandler->fileFetchContents( $path );
            },
            false
        );
    }

    /**
     * Returns the appropriate FileResourceProvider depending on the cluster handler in use
     *
     * @throws \Exception
     *
     * @return \eZ\Publish\Core\IO\LegacyHandler\FileResourceProvider
     */
    private function getFileResourceProvider()
    {
        if ( !isset( $this->fileResourceProvider ) )
        {
            $class = __CLASS__ . '\\FileResourceProvider\\' . get_class( $this->getClusterHandler() );
            if ( !class_exists( $class ) )
            {
                throw new \Exception( "FileResourceProvider $class couldn't be found" );
            }
            $this->fileResourceProvider = new $class;
            $this->fileResourceProvider->setLegacyKernel( $this->getLegacyKernel() );
        }

        return $this->fileResourceProvider;
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
                    return eZClusterFileHandler::instance();
                },
                false
            );
        }

        return $this->clusterHandler;
    }

    /**
     * Returns a mimeType from a local file, using fileinfo
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If file does not exist
     *
     * @todo If legacy path is made available then this function can use that to skip executing legacy kernel
     *
     * @param string $path
     *
     * @return string
     */
    protected function getMimeTypeFromLocalFile( $path )
    {
        $returnValue = $this->getLegacyKernel()->runCallback(
            function () use ( $path )
            {
                if ( !is_file( $path ) )
                    return null;

                $fileInfo = new finfo( FILEINFO_MIME_TYPE );
                return $fileInfo->file( $path );
            },
            false
        );

        if ( $returnValue === null )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        return $returnValue;
    }
}
