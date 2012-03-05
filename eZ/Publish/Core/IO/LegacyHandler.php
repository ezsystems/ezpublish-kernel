<?php
/**
 * File containing the eZ\Publish\Core\IO\LegacyHandler class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO;

use eZ\Publish\SPI\IO\Handler as IoHandlerInterface,
    eZ\Publish\SPI\IO\BinaryFile,
    eZ\Publish\SPI\IO\BinaryFileCreateStruct,
    eZ\Publish\SPI\IO\BinaryFileUpdateStruct,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZClusterFileHandler,
    DateTime,
    finfo;

/**
 * Legacy Io/Storage handler, based on eZ Cluster
 *
 * Due to the legacy API, this handler has a few limitations:
 * - ctime is not really supported, and will always have the same value as mtime
 * - mtime can not be modified, and will always automatically be set depending on the server time upon each write operation
 */

class LegacyHandler implements IoHandlerInterface
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
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $createFilestruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $createFilestruct )
    {
        if ( $this->exists( $createFilestruct->path ) )
        {
            throw new InvalidArgumentException(
                "\$createFilestruct->path",
                "file '{$createFilestruct->path}' already exists"
            );
        }

        // @todo Build a path / scope mapper
        $scope = 'todo';
        $this->getClusterHandler()->fileStoreContents(
            $createFilestruct->path,
            fread( $createFilestruct->getInputStream(), $createFilestruct->size ),
            $createFilestruct->mimeType,
            $scope
        );

        return $this->load( $createFilestruct->path );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the file doesn't exist
     *
     * @param string $path
     */
    public function delete( $path )
    {
        if ( !$this->getClusterHandler()->fileExists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $this->getClusterHandler()->fileDelete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the source path doesn't exist
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the target path already exists
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFileStruct
     *
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFileStruct )
    {
        $clusterHandler = $this->getClusterHandler();
        if ( !$clusterHandler->fileExists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        // path
        if ( $updateFileStruct->path !== null && $updateFileStruct->path != $path )
        {
            if ( $clusterHandler->fileExists( $updateFileStruct->path ) )
            {
                throw new InvalidArgumentException(
                    "\$updateFileStruct->path",
                    "file '{$updateFileStruct->path}' already exists"
                );
            }
            $clusterHandler->fileMove( $path, $updateFileStruct->path );

            // update the path we are working on
            $path = $updateFileStruct->path;
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
        return $this->getClusterHandler()->fileExists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
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

        $clusterFile = eZClusterFileHandler::instance( $path );

        $metaData = $clusterFile->metaData;

        $file = new BinaryFile();
        $file->path = $path;

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
            $file->mimeType = self::getMimeTypeFromPath( $path );
        }

        $file->uri = $file->path;

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no file identified by $path exists
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
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the file couldn't be found
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        if ( !$this->getClusterHandler()->fileExists( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        return $this->getClusterHandler()->fileFetchContents( $path );
    }

    /**
     * Returns the appropriate FileResourceProvider depending on the cluster handler in use
     *
     * @return \eZ\Publish\Core\IO\Legacy\FileResourceProvider
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
            $this->clusterHandler = eZClusterFileHandler::instance();
        return $this->clusterHandler;
    }

    /**
     * Returns a mimeType from a file path, using fileinfo
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If file does not exist
     *
     * @param string $path
     *
     * @return string
     */
    protected static function getMimeTypeFromPath( $path )
    {
        if ( !is_file( $path ) )
        {
            throw new NotFoundException( 'BinaryFile', $path );
        }

        $finfo = new finfo( FILEINFO_MIME_TYPE );
        return $finfo->file( $path );
    }
}
