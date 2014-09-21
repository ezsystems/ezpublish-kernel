<?php
/**
 * File containing the Filesystem BinaryDataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler;

use eZ\Publish\Core\IO\MetadataHandler as IOMetadataHandler;
use eZ\Publish\Core\IO\Handler\DFS\BinaryDataHandler;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Filesystem;

class FlySystem implements BinaryDataHandler
{
    /** @var FilesystemInterface */
    private $filesystem;

    public function __construct( AdapterInterface $adapter)
    {
        $this->filesystem = new FileSystem( $adapter );
    }

    /**
     * Creates the file $path with data from $resource
     *
     * @param string   $path
     * @param resource $resource
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If file already exists
     *
     * @return void
     */
    public function createFromStream($path, $resource)
    {
        try {
            $this->filesystem->writeStream($path, $resource, ['visibility' => 'public']);
        } catch ( \League\Flysystem\FileExistsException $e ) {
            $this->filesystem->updateStream($path, $resource, ['visibility' => 'public']);
        }
    }

    /**
     * Deletes the file $path
     *
     * @param string $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path isn't found
     */
    public function delete($path)
    {
        $this->filesystem->delete($path);
    }

    /**
     * Retrieves metadata from $path using $metadataHandler
     *
     * @param IOMetadataHandler $metadataHandler
     * @param string $path
     *
     * @return array
     */
    public function getMetadata(IOMetadataHandler $metadataHandler, $path)
    {
        // @todo
        return array();
    }

    /**
     * Returns the binary content from $path
     *
     * @param $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path is not found
     *
     * @return string
     */
    public function getFileContents( $path )
    {
        return $this->filesystem->read($path);
    }

    /**
     * Returns a read-only, binary file resource to $path
     *
     * @param string $path
     *
     * @return resource A read-only binary resource to $path
     */
    public function getFileResource($path)
    {
        return $this->filesystem->readStream($path);
    }

    /**
     * Updates the content from $path with data from the read binary resource $resource
     *
     * @param string   $path
     * @param resource $resource
     */
    public function updateFileContents( $path, $resource )
    {
        $this->filesystem->writeStream($path, $resource, ['visibility' => 'public']);
    }

    /**
     * Renames file $fromPath to $toPath
     *
     * @param $fromPath
     * @param $toPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $toPath already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $fromPath does not exist
     */
    public function rename($fromPath, $toPath)
    {
        $this->filesystem->rename($fromPath, $toPath);
    }

}
