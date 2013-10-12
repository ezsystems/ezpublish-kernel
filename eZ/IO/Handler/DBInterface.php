<?php
/**
 * File containing the DBInterface class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace BD\Bundle\DFSBundle\eZ\IO\Handler;

interface DBInterface
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
}
