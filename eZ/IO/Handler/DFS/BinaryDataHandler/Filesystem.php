<?php
/**
 * File containing the Filesystem BinaryDataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace BD\Bundle\DFSBundle\eZ\IO\Handler\DFS\BinaryDataHandler;

use BD\Bundle\DFSBundle\eZ\IO\Handler\DFS\BinaryDataHandler;

class Filesystem implements BinaryDataHandler
{
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
    public function createFromStream( $path, $resource )
    {
        // TODO: Implement createFromStream() method.
    }

    /**
     * Deletes the file $path
     *
     * @param string $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path isn't found
     */
    public function delete( $path )
    {
        // TODO: Implement delete() method.
    }

    /**
     * Retrieves metadata from $path using $metadataHandler
     *
     * @param \eZ\Publish\Core\IO\MetadataHandler $metadataHandler
     * @param string          $path
     *
     * @return array
     */
    public function getMetadata( \eZ\Publish\Core\IO\MetadataHandler $metadataHandler, $path )
    {
        // TODO: Implement getMetadata() method.
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
        // TODO: Implement getFileContents() method.
    }

    /**
     * Returns a read-only, binary file resource to $path
     *
     * @param string $path
     *
     * @return resource A read-only binary resource to $path
     */
    public function getFileResource( $path )
    {
        // TODO: Implement getFileResource() method.
    }

    /**
     * Updates the content from $path with data from the read binary resource $resource
     *
     * @param string   $path
     * @param resource $resource
     */
    public function updateFileContents( $path, $resource )
    {
        // TODO: Implement updateFileContents() method.
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
    public function rename( $fromPath, $toPath )
    {
        // TODO: Implement rename() method.
    }

}
 