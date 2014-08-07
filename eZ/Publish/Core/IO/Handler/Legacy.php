<?php
/**
 * File containing the eZ\Publish\Core\IO\Handler\Legacy class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler;

use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\Core\IO\MetadataHandler;
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
     * @var FileResource
     */
    private $fileResourceProvider = null;

    /**
     * Cluster handler instance
     * @var \eZClusterFileHandlerInterface
     */
    private $clusterHandler = null;

    /**
     * @var LegacyKernel
     */
    private $legacyKernel;

    /**
     * Closure containing legacy kernel bootstrap
     *
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * The storage directory where data is stored
     * Example: var/site/storage
     * @var string
     */
    private $storageDirectory;

    public function __construct( $storageDirectory, LegacyKernel $legacyKernel = null )
    {
        if ( $legacyKernel )
        {
            $this->legacyKernel = $legacyKernel;
        }
        $this->storageDirectory = $storageDirectory;
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
        if ( !isset( $this->legacyKernel ) && isset( $this->legacyKernelClosure ) )
        {
            $kernelClosure = $this->legacyKernelClosure;
            $this->legacyKernel = $kernelClosure();
        }

        return $this->legacyKernel;
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
        if ( $this->exists( $createStruct->id ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->id",
                "file '" . $this->getStoragePath( $createStruct->id ). "' already exists"
            );
        }

        $storagePath = $this->getStoragePath( $createStruct->id );
        $scope = $this->getScope( $storagePath );
        $dataType = $this->getDatatype( $createStruct->mimeType );

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $createStruct, $storagePath, $dataType, $scope, $clusterHandler )
            {
                $clusterHandler->fileStoreContents(
                    $storagePath,
                    fread( $createStruct->getInputStream(), $createStruct->size ),
                    $scope,
                    $dataType
                );
            },
            false,
            false
        );

        return $this->load( $createStruct->id );
    }

    protected function getDatatype( $mimeType )
    {
        return substr( $mimeType, 0, strpos( $mimeType, '/' ) );
    }

    /**
     * Maps a URI to a scope.
     *
     * Note that mediafile & binaryfile can't be distinguished from each other using the path alone,
     * since they're stored in the same directory. We consider that a file of mimetype media will be
     * a mediafile, and other binary files will be scoped binaryfile
     *
     * @throws InvalidArgumentException If $path isn't a legacy compatible storage path
     * @param string $path
     * @return string|false The scope, defaulting to UNKNOWN_SCOPE
     */
    protected function getScope( $path )
    {
        $pathArray = explode( DIRECTORY_SEPARATOR, $path );
        if ( count( $pathArray ) < 5 )
            throw new InvalidArgumentException( "\$path", "'$path' isn't a Legacy compatible storage path" );

        $storagePrefix = $pathArray[3];
        $mimeType = $pathArray[4];

        if ( $storagePrefix === 'images' )
        {
            return 'images';
        }

        if ( $storagePrefix === 'original' )
        {
            if ( $mimeType === 'video' || $mimeType === 'audio' )
            {
                return 'mediafile';
            }
            else
            {
                return 'binaryfile';
            }
        }

        return 'UNKNOWN_SCOPE';
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $spiBinaryFileId
     */
    public function delete( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }
        $storagePath = $this->getStoragePath( $spiBinaryFileId );
        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $clusterHandler )
            {
                $clusterHandler->fileDelete( $storagePath );
            },
            false,
            false
        );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $spiBinaryFileId
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $spiBinaryFileId, BinaryFileUpdateStruct $updateFileStruct )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $destinationPath = $updateFileStruct->id;
        if ( isset( $updateFileStruct->id ) && $updateFileStruct->id !== $spiBinaryFileId )
        {
            if ( $this->exists( $updateFileStruct->id ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->id",
                    "Destination ID '" . $this->getStoragePath( $updateFileStruct->id ). "' already exists"
                );
            }

            $updateFileStruct->id = $this->getStoragePath( $updateFileStruct->id );
        }

        $storagePath = $this->getStoragePath( $spiBinaryFileId );
        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $updateFileStruct, $clusterHandler )
            {
                // path
                if ( $updateFileStruct->id !== null && $updateFileStruct->id != $storagePath )
                {
                    $clusterHandler->fileMove( $storagePath, $updateFileStruct->id );
                    $storagePath = $updateFileStruct->id;
                }

                $resource = $updateFileStruct->getInputStream();
                if ( $resource !== null )
                {
                    $binaryUpdateData = fread( $resource, $updateFileStruct->size );
                    $clusterFile = eZClusterFileHandler::instance( $storagePath );

                    $metaData = $clusterFile->metaData;
                    $scope = isset( $metaData['scope'] ) ? $metaData['scope'] : false;
                    $dataType = isset( $metaData['datatype'] ) ? $metaData['datatype'] : false;
                    $clusterFile->storeContents( $binaryUpdateData, $scope, $dataType );

                    // Unfortunately required due to insufficient handling of in memory cache in eZDFSFileHandler...
                    if ( isset( $GLOBALS['eZClusterInfo'][$storagePath] ) )
                    {
                        unset( $GLOBALS['eZClusterInfo'][$storagePath] );
                    }
                }
            },
            false,
            false
        );

        return $this->load( $destinationPath );
    }

    /**
     * Checks if the BinaryFile with path $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return boolean
     */
    public function exists( $spiBinaryFileId )
    {
        $spiBinaryFileId = $this->getStoragePath( $spiBinaryFileId );
        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            function () use ( $clusterHandler, $spiBinaryFileId )
            {
                return $clusterHandler->fileExists( $spiBinaryFileId );
            },
            false,
            false
        );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If no file identified by $path exists
     *
     * @param string $spiBinaryFileId
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile
     */
    public function load( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $storagePath = $this->getStoragePath( $spiBinaryFileId );
        $metaData = $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath )
            {
                $clusterFile = eZClusterFileHandler::instance( $storagePath );
                return $clusterFile->metaData;
            },
            false,
            false
        );

        $file = new BinaryFile();
        $file->id = $spiBinaryFileId;

        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( $metaData['mtime'] );

        $file->size = $metaData['size'];
        $file->uri = $this->getUri( $spiBinaryFileId );

        // will only work with some ClusterFileHandlers (DB based ones, not with FS ones)
        if ( isset( $metaData['datatype'] ) )
        {
            $file->mimeType = $metaData['datatype'];
        }

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $spiBinaryFileId
     *
     * @throws NotFoundException if $path doesn't exist
     * @return resource
     */
    public function getFileResource( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( "spiBinaryFile", $spiBinaryFileId );
        }
        return $this->getFileResourceProvider()->getResource( $this->getStoragePath( $spiBinaryFileId ) );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $spiBinaryFileId
     *
     * @return string
     */
    public function getFileContents( $spiBinaryFileId )
    {
        if ( !$this->exists( $spiBinaryFileId ) )
        {
            throw new NotFoundException( 'BinaryFile', $spiBinaryFileId );
        }

        $storagePath = $this->getStoragePath( $spiBinaryFileId );
        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $clusterHandler )
            {
                return $clusterHandler->fileFetchContents( $storagePath );
            },
            false,
            false
        );
    }

    public function getInternalPath( $spiBinaryFileId )
    {
        return $this->getStoragePath( $spiBinaryFileId );
    }

    public function getExternalPath( $path )
    {
        return $this->removeStoragePath( $path );
    }

    public function getMetadata( MetadataHandler $metadataHandler, $spiBinaryFileId )
    {
        $clusterHandler = $this->getClusterHandler(
            $this->getStoragePath( $spiBinaryFileId )
        );

        return $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function() use( $clusterHandler, $metadataHandler )
            {
                $temporaryFileName = $clusterHandler->fetchUnique();
                $metadata = $metadataHandler->extract( $temporaryFileName );
                $clusterHandler->fileDeleteLocal( $temporaryFileName );
                return $metadata;
            },
            false,
            false
        );
    }

    /**
     * Returns the appropriate FileResourceProvider depending on the cluster handler in use
     *
     * @throws \Exception
     *
     * @return \eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider
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
     * @param string $path
     * @return eZClusterFileHandler
     */
    private function getClusterHandler( $path = null )
    {
        if ( $path )
        {
            if ( !isset( $this->clusterFileHandlers[$path] ) )
            {
                $this->clusterFileHandlers[$path] = $this->getLegacyKernel()->runCallback(
                    function () use ( $path )
                    {
                        return eZClusterFileHandler::instance( $path );
                    },
                    false,
                    false
                );
            }
            $clusterHandler = $this->clusterFileHandlers[$path];
        }
        else
        {
            if ( $this->clusterHandler === null )
            {
                $this->clusterHandler = $this->getLegacyKernel()->runCallback(
                    function ()
                    {
                        return eZClusterFileHandler::instance();
                    },
                    false,
                    false
                );
            }
            $clusterHandler = $this->clusterHandler;
        }

        return $clusterHandler;
    }

    /**
     * Returns a mimeType from a local file, using fileinfo
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If file does not exist
     * @todo If legacy path is made available then this function can use that to skip executing legacy kernel
     * @param string $path
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
            false,
            false
        );

        if ( $returnValue === null )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        return $returnValue;
    }

    /**
     * Transforms an SPI id in a storage path using the $storageDirectory
     * @param string $spiBinaryFileId
     * @return string
     */
    protected function getStoragePath( $spiBinaryFileId )
    {
        if ( $this->storageDirectory )
            $spiBinaryFileId = $this->storageDirectory . '/' . $spiBinaryFileId;
        return $spiBinaryFileId;
    }

    /**
     * @param string $path
     *
     * @return string spiBinaryFileId
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    protected function removeStoragePath( $path )
    {
        if ( !$this->storageDirectory )
        {
            return $path;
        }

        if ( strpos( $path, $this->storageDirectory . '/' ) !== 0 )
        {
            throw new InvalidArgumentException( '$path', "Storage directory '$this->storageDirectory' not found in $path" );
        }

        return substr( $path, strlen( $this->storageDirectory ) + 1 );
    }

    public function getUri( $spiBinaryFileId )
    {
        return '/' . $this->getInternalPath( $spiBinaryFileId );
    }
}
