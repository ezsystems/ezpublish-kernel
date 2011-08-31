<?php
/**
 * File containing the ezp\Io\BinaryStorage\Legacy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage;

use ezp\Io\BinaryStorage\Backend,
    ezp\Io\BinaryFile, ezp\Io\BinaryFileCreateStruct, ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\ContentType,
    ezp\Base\Exception\InvalidArgumentValue,
    eZClusterFileHandler,
    DateTime;

/**
 * Legacy BinaryStorage handler, based on eZ Cluster
 */

class Legacy implements Backend
{
    /**
     * Cluster handler instance
     * @var eZClusterFileHandlerInterface
     */
    protected $clusterHandler;

    public function __construct()
    {
        $this->clusterHandler = eZClusterFileHandler::instance();
    }

    /**
     * Creates a new BinaryFile based on the BinaryFileCreateStruct $file
     * @param BinaryFileCreateStruct $file
     * @throws \ezp\Base\Exception\InvalidArgumentValue If a file with this path already exists
     * @return BinaryFile The created BinaryFile object
     */
    public function create( BinaryFileCreateStruct $file )
    {
        if ( $this->exists( $file->path ) )
        {
            throw new \Exception( "A binary file identified with $file->path already exists" );
        }


        // @todo Build a path / scope mapper
        $scope = 'todo';
        $this->clusterHandler->fileStoreContents(
            $file->path,
            fread( $file->getInputStream(), $file->size ),
            (string)$file->contentType,
            $scope
        );

        return $this->load( $file->path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param BinaryFileUpdateStruct $updateFile
     *
     * @return BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }

    /**
     * Deletes the file $path
     * @param string $path
     * @throws \ezp\Base\Exception\InvalidArgumentType If the file doesn't exist
     */
    public function delete( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new InvalidArgumentType( 'path', 'no such file', $path );
        }

        $this->clusterHandler->fileDelete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param BinaryFileUpdateStruct $updateFile
     *
     * @return BinaryFile The updated BinaryFile
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {

    }

    /**
     * Returns a file resource to the BinaryFile $file
     * @param BinaryFile $file
     * @return resource
     */
    public function getFileResource( BinaryFile $file )
    {

    }

    /**
     * Checks if a file with $path exists in the backend
     * @var string $path
     * @return bool
     */
    public function exists( $path )
    {
        return $this->clusterHandler->fileExists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     * @param string $path
     * @return BinaryFile
     * @throws InvalidArgumentValue When the requested $path doesn't exist
     */
    public function load( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new InvalidArgumentValue( 'path', $path );
        }

        $clusterFile = eZClusterFileHandler::instance( $path );

        $metaData = $clusterFile->metaData;

        $file = new BinaryFile();
        $file->path = $path;

        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( $metaData['mtime'] );

        $file->ctime = $file->mtime;

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


        return $file;
    }

    /**
     * Returns a file resource to the BinaryFile $file
     * @param BinaryFile $file
     * @return resource
     */
    public function getFileResource( BinaryFile $file )
    {
        throw new \RuntimeException( 'Not implemented, yet.' );
    }
}
?>
