<?php
/**
 * File containing the eZ\Publish\Core\IO\Legacy class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Legacy;

use eZ\Publish\SPI\IO\Handler as IoHandlerInterface,
    eZ\Publish\SPI\IO\BinaryFile,
    eZ\Publish\SPI\IO\BinaryFileCreateStruct,
    eZ\Publish\SPI\IO\BinaryFileUpdateStruct,
    ezp\Io\ContentType,
    ezp\Io\Exception\PathExists,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\NotFound,
    eZClusterFileHandler,
    DateTime;

/**
 * Legacy Io/Storage handler, based on eZ Cluster
 *
 * Due to the legacy API, this handler has a few limitations:
 * - ctime is not really supported, and will always have the same value as mtime
 * - mtime can not be modified, and will always automatically be set depending on the server time upon each write operation
 */

class Handler implements IoHandlerInterface
{
    /**
     * File resource provider
     * @see getFileResourceProvider
     */
    private $fileResourceProvider = null;

    /**
     * Cluster handler instance
     * @var eZClusterFileHandlerInterface
     */
    private $clusterHandler = null;

    /**
     * Creates and stores a new BinaryFile based on the BinaryFileCreateStruct $file
     *
     * @param \eZ\Publish\SPI\IO\BinaryFileCreateStruct $file
     * @return \eZ\Publish\SPI\IO\BinaryFile The newly created BinaryFile object
     *
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function create( BinaryFileCreateStruct $file )
    {
        if ( $this->exists( $file->path ) )
        {
            throw new PathExists( $file->path );
        }

        // @todo Build a path / scope mapper
        $scope = 'todo';
        $this->getClusterHandler()->fileStoreContents(
            $file->path,
            fread( $file->getInputStream(), $file->size ),
            (string)$file->contentType,
            $scope
        );

        return $this->load( $file->path );
    }

    /**
     * Deletes the existing BinaryFile with path $path
     *
     * @param string $path
     * @throws \ezp\Base\Exception\NotFound If the file doesn't exist
     */
    public function delete( $path )
    {
        if ( !$this->getClusterHandler()->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        $this->getClusterHandler()->fileDelete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param \eZ\Publish\SPI\IO\BinaryFileUpdateStruct $updateFile
     * @return \eZ\Publish\SPI\IO\BinaryFile The updated BinaryFile
     *
     * @throws \ezp\Base\Exception\NotFound If the source path doesn't exist
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        $clusterHandler = $this->getClusterHandler();
        if ( !$clusterHandler->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        // path
        if ( $updateFile->path !== null && $updateFile->path != $path )
        {
            if ( $clusterHandler->fileExists( $updateFile->path ) )
            {
                throw new PathExists( $updateFile->path );
            }
            $clusterHandler->fileMove( $path, $updateFile->path );

            // update the path we are working on
            $path = $updateFile->path;
        }

        $resource = $updateFile->getInputStream();
        if ( $resource !== null )
        {
            $binaryUpdateData = fread( $resource, $updateFile->size );
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
     * @return bool
     */
    public function exists( $path )
    {
        return $this->getClusterHandler()->fileExists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     *
     * @param string $path
     * @return \eZ\Publish\SPI\IO\BinaryFile
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function load( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
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
            list( $type, $subType ) = explode( '/', $metaData['datatype'] );
            $file->contentType = new ContentType( $type, $subType );
        }
        else
        {
            $file->contentType = ContentType::getFromPath( $path );
        }

        $file->uri = $file->path;

        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     *
     * @param string $path
     * @return resource
     * @throws \ezp\Base\Exception\NotFound If no file identified by $path exists
     */
    public function getFileResource( $path )
    {
        return $this->getFileResourceProvider()->getResource( $this->load( $path ) );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     *
     * @param string $path
     * @return string
     * @throws \ezp\Base\Exception\NotFound if the file couldn't be found
     */
    public function getFileContents( $path )
    {
        if ( !$this->getClusterHandler()->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
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
}
