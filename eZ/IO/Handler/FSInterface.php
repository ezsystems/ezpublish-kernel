<?php
/**
 * File containing the FSInterface class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace BD\Bundle\DFSBundle\eZ\IO\Handler;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

interface FSInterface
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
     * @param string $path
     *
     * @throws NotFoundException If $path isn't found
     */
    public function delete( $path );
}
