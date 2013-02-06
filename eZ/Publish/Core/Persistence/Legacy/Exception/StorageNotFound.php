<?php
/**
 * File containing the StorageNotFound class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Exception;

/**
 * Exception thrown no storage for a type was found
 */
class StorageNotFound extends \InvalidArgumentException
{
    /**
     * Creates a new exception for $typeName
     *
     * @param mixed $typeName
     */
    public function __construct( $typeName )
    {
        parent::__construct(
            sprintf( 'Storage for type "%s" not found.', $typeName )
        );
    }
}
