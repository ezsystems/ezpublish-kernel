<?php
/**
 * File containing the MetadataHandler interface.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace EzSystems\DFSIOBundle\eZ\IO\Handler\DFS;

interface MetadataHandler
{
    /**
     * Inserts a new reference to file $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If a file $path already exists
     *
     * @param string $path
     * @param integer $mtime
     */
    public function insert( $path, $mtime );

    /**
     * Deletes file $path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $path is not found
     *
     * @param string $path
     */
    public function delete( $path );

    /**
     * Loads and returns metadata for $path
     *
     * @param string $path
     *
     * @return array A hash with metadata for $path. Keys: mtime, size.
     */
    public function loadMetadata( $path );

    /**
     * Checks if a file $path exists
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists( $path );

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
}
