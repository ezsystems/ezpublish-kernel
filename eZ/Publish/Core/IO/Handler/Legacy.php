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
use eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
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
     * @var FileReso
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
     * The storage directory where data is stored
     * Example: var/site/storage
     * @var string
     */
    private $storageDirectory;

    /**
     * Created Legacy handler instance
     *
     * @param string $storageDirectory
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel $legacyKernel
     */
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
        $this->legacyKernel = $kernelClosure();
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
        if ( $this->exists( $createStruct->uri ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->uri",
                "file '" . $this->getStoragePath( $createStruct->uri ). "' already exists"
            );
        }

        $storagePath = $this->getStoragePath( $createStruct->uri );
        $scope = $this->getScope( $storagePath );
        $datatype = $this->getDatatype( $createStruct->mimeType );

        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $createStruct, $storagePath, $datatype, $scope, $clusterHandler )
            {
                $clusterHandler->fileStoreContents(
                    $storagePath,
                    fread( $createStruct->getInputStream(), $createStruct->size ),
                    $scope,
                    $datatype
                );
            },
            false
        );

        return $this->load( $createStruct->uri );
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
     * @param string $path
     */
    public function delete( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }
        $storagePath = $this->getStoragePath( $path );
        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $clusterHandler )
            {
                $clusterHandler->fileDelete( $storagePath );
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

        $destinationPath = $updateFileStruct->uri;
        if ( isset( $updateFileStruct->uri ) && $updateFileStruct->uri !== $path )
        {
            if ( $this->exists( $updateFileStruct->uri ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->uri",
                    "File '" . $this->getStoragePath( $updateFileStruct->uri ). "' already exists"
                );
            }

            $updateFileStruct->uri = $this->getStoragePath( $updateFileStruct->uri );
        }

        $storagePath = $this->getStoragePath( $path );
        $clusterHandler = $this->getClusterHandler();
        $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $updateFileStruct, $clusterHandler )
            {
                // path
                if ( $updateFileStruct->uri !== null && $updateFileStruct->uri != $storagePath )
                {
                    $clusterHandler->fileMove( $storagePath, $updateFileStruct->uri );
                    $storagePath = $updateFileStruct->uri;
                }

                $resource = $updateFileStruct->getInputStream();
                if ( $resource !== null )
                {
                    $binaryUpdateData = fread( $resource, $updateFileStruct->size );
                    $clusterFile = eZClusterFileHandler::instance( $storagePath );

                    $metaData = $clusterFile->metaData;
                    $scope = isset( $metaData['scope'] ) ? $metaData['scope'] : false;
                    $datatype = isset( $metaData['datatype'] ) ? $metaData['datatype'] : false;
                    $clusterFile->storeContents( $binaryUpdateData, $scope, $datatype );

                    // Unfortunately required due to insufficient handly of in memory cache in eZDFSFileHandler...
                    if ( isset( $GLOBALS['eZClusterInfo'][$storagePath] ) )
                    {
                        unset( $GLOBALS['eZClusterInfo'][$storagePath] );
                    }
                }
            },
            false
        );

        return $this->load( $destinationPath );
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
        $path = $this->getStoragePath( $path );
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

        $storagePath = $this->getStoragePath( $path );
        $metaData = $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath )
            {
                $clusterFile = eZClusterFileHandler::instance( $storagePath );
                return $clusterFile->metaData;
            },
            false
        );

        $file = new BinaryFile();
        $file->uri = $path;

        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( $metaData['mtime'] );

        $file->size = $metaData['size'];

        // will only work with some ClusterFileHandlers (DB based ones, not with FS ones)
        if ( isset( $metaData['datatype'] ) )
        {
            $file->mimeType = $metaData['datatype'];
        }

        $file->uri = $file->uri;

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     *
     * @throws NotFoundException if $path doesn't exist
     * @return resource
     */
    public function getFileResource( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFoundException( "BinaryFile", $path );
        }
        return $this->getFileResourceProvider()->getResource( $this->getStoragePath( $path ) );
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

        $storagePath = $this->getStoragePath( $path );
        $clusterHandler = $this->getClusterHandler();
        return $this->getLegacyKernel()->runCallback(
            function () use ( $storagePath, $clusterHandler )
            {
                return $clusterHandler->fileFetchContents( $storagePath );
            },
            false
        );
    }

    public function getInternalPath( $path )
    {
        return $this->getStoragePath( $path );
    }

    public function getExternalPath( $path )
    {
        return $this->removeStoragePath( $path );
    }

    public function getMetadata( MetadataHandler $metadataHandler, $path )
    {
        $clusterHandler = $this->getClusterHandler(
            $this->getStoragePath( $path )
        );

        return $this->getLegacyKernel()->runCallback(
            /** @var $clusterHandler \eZClusterFileHandlerInterface */
            function() use( $clusterHandler, $metadataHandler )
            {
                $temporaryFileName = $clusterHandler->fetchUnique();
                $metadata = $metadataHandler->extract( $temporaryFileName );
                $clusterHandler->fileDeleteLocal( $temporaryFileName );
                return $metadata;
            }
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
            false
        );

        if ( $returnValue === null )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        return $returnValue;
    }

    /**
     * Transforms a path in a storage path using the $storageDirectory
     * @param string $path
     * @return string
     */
    protected function getStoragePath( $path )
    {
        if ( $this->storageDirectory )
            $path = $this->storageDirectory . DIRECTORY_SEPARATOR . $path;
        return $path;
    }

    protected function removeStoragePath( $path )
    {
        if ( !$this->storageDirectory )
        {
            return $path;
        }

        if ( strpos( $path, $this->storageDirectory . DIRECTORY_SEPARATOR ) !== 0 )
        {
            throw new InvalidArgumentException( '$path', "Storage directory not found in $path" );
        }

        return substr( $path, strlen( $this->storageDirectory ) + 1 );
    }
}
