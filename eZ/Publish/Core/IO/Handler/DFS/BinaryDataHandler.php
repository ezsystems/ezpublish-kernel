<?php
/**
 * File containing the BinaryDataHandler interface
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler\DFS;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\MetadataHandler;

interface BinaryDataHandler
{
    /**
     * Creates the file $path with data from $resource
     * @param string $path
     * @param resource $resource
     *
     * @throws InvalidArgumentException If file already exists
     *
     * @return void
     */
    public function createFromStream( $path, $resource );

    /**
     * Deletes the file $path
     *
     * @param string $path
     *
     * @throws NotFoundException If $path isn't found
     */
    public function delete( $path );

    /**
     * Retrieves metadata from $path using $metadataHandler
     *
     * @param MetadataHandler $metadataHandler
     * @param string $path
     *
     * @return array
     */
    public function getMetadata( MetadataHandler $metadataHandler, $path );

    /**
     * Returns the binary content from $path
     *
     * @param $path
     *
     * @throws NotFoundException If $path is not found
     *
     * @return string
     */
    public function getFileContents( $path );

    /**
     * Returns a read-only, binary file resource to $path
     *
     * @param string $path
     *
     * @return resource A read-only binary resource to $path
     */
    public function getFileResource( $path );

    /**
     * Updates the content from $path with data from the read binary resource $resource
     *
     * @param string $path
     * @param resource $resource
     */
    public function updateFileContents( $path, $resource  );

    /**
     * Renames file $fromPath to $toPath
     *
     * @param $fromPath
     * @param $toPath
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $toPath already exists
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $fromPath does not exist
     */
    public function rename( $fromPath, $toPath );

    /**
     * Returns the public URI for $path
     * @param string $path
     * @return string
     */
    public function getUri( $path );
}
