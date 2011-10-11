<?php
/**
 * Io\Service class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;
use ezp\Base\Repository,
    ezp\Base\Service as BaseService,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Io\BinaryFile,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\BinaryFileCreateStruct,
    ezp\Io\ContentType,
    ezp\Io\Handler as IoHandlerInterface,
    DateTime;

/**
 * Io\Service class
 *
 * Differs from other Services in that it uses different handler, namely {@link \ezp\Io\Handler}
 */
class Service extends BaseService
{
    /**
     * @var \ezp\Io\Handler
     */
    protected $handler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \ezp\Base\Repository $repository
     * @param \ezp\Io\Handler $handler
     */
    public function __construct( Repository $repository, IoHandlerInterface $handler )
    {
        $this->repository = $repository;
        $this->handler = $handler;
    }

    /**
     * Creates a BinaryFile object from the uploaded file $uploadedFile
     *
     * @param array $uploadedFile The $_POST hash of an uploaded file
     * @param string $repositoryPath The path the file must be stored as
     * @return \ezp\Io\BinaryFile
     * @throws \InvalidArgumentValue When given an invalid uploaded file
     * @uses create() To create the resulting binary file in backend
     * @todo Create file in backend like done in createFromLocalFile()
     */
    public function createFromUploadedFile( array $uploadedFile, $repositoryPath )
    {
        if ( !isset( $uploadedFile['tmp_name'] ) || !is_uploaded_file( $uploadedFile['tmp_name'] ) )
        {
            throw new InvalidArgumentValue( 'uploadedFile', $uploadedFile );
        }

        $file = new BinaryFile();
        $file->size = $uploadedFile['size'];
        $file->ctime = new DateTime;
        $file->mtime = clone $file->ctime;
        $file->contentType = $uploadedFile['type'];

        return $file;
    }

    /**
     * Creates a BinaryFile object from $localFile
     *
     * @param string $localFile Path to local file
     * @param string $repositoryPath The path the file must be stored as
     * @return \ezp\Io\BinaryFile
     * @throws \InvalidArgumentValue When given a non existing / unreadable file
     * @uses create() To create the resulting binary file in backend
     */
    public function createFromLocalFile( $localFile, $repositoryPath )
    {
        if ( !file_exists( $localFile ) || !is_readable( $localFile ) )
        {
            throw new InvalidArgumentValue( 'localFile', $localFile );
        }

        $file = new BinaryFileCreateStruct();
        $file->originalFile = basename( $localFile );
        $file->size = filesize( $localFile );
        $file->contentType = ContentType::getFromPath( $localFile );
        $file->path = $repositoryPath;

        $inputStream = fopen( $localFile, 'rb' );
        $file->setInputStream( $inputStream );

        return $this->create( $file );
    }

    /**
     * Stores $binaryFile to the repository
     *
     * It is recommended to use {@link createFromLocalFile()} or {@link createFromUploadedFile()} as they
     * handle most use cases with less code needed then using this method directly.
     *
     * @param \ezp\Io\BinaryFileCreateStruct $binaryFile
     * @return \ezp\Io\BinaryFile The created BinaryFile object
     * @uses \ezp\Io\Handler::create() To create the binary file in backend
     */
    public function create( BinaryFileCreateStruct $binaryFile )
    {
        return $this->handler->create( $binaryFile );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $originalPath
     * @param \ezp\Io\BinaryFileUpdateStruct $updateFile
     * @return \ezp\Io\BinaryFile The update BinaryFile
     * @uses \ezp\Io\Handler::update() To update the binary file in backend
     */
    public function update( $originalPath, BinaryFileUpdateStruct $updateFile )
    {
        return $this->handler->update( $originalPath, $updateFile );
    }

    /**
     * Checks if a BinaryFile with $path exists in the repository
     *
     * @param string $path
     * @return bool
     * @uses \ezp\Io\Handler::exists() To see if file exists in backend
     */
    public function exists( $path )
    {
        return $this->handler->exists( $path );
    }

    /**
     * Deletes the BinaryFile with $path
     *
     * @param string $path
     * @uses \ezp\Io\Handler::delete() To delete the binary file in backend
     */
    public function delete( $path )
    {
        $this->handler->delete( $path );
    }

    /**
     * Loads the binary file with $path
     *
     * @param string $path
     * @return \ezp\Io\BinaryFile
     * @uses \ezp\Io\Handler::load() To load the binary file from backend
     */
    public function load( $path )
    {
        return $this->handler->load( $path );
    }

    /**
     * Returns a read (mode: rb) file resource to the binary file identified by $path
     *
     * @param string $path
     * @return resource
     * @uses \ezp\Io\Handler::getFileResource() To get the binary file resource from backend
     */
    public function getFileResource( $path )
    {
        return $this->handler->getFileResource( $path );
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     * @param string $path
     * @return string Binary content
     * @uses \ezp\Io\Handler::getFileContents() To get the binary file content from backend
     */
    public function getFileContents( $path )
    {
        return $this->handler->getFileContents( $path );
    }
}
