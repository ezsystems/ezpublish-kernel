<?php
/**
 * File containing the TypeNotFound class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\Legacy\Exception;
use ezp\Base\Exception\NotFound;

/**
 * Exception thrown when a Type to be loaded is not found
 */
class TypeNotFound extends NotFound
{
    /**
     * Creates a new exception for $typeId in $status;
     *
     * @param mixed $typeId
     * @param mixed $status
     */
    public function __construct( $typeId, $status )
    {
        parent::__construct(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type',
            sprintf( 'ID: %s, Status: %s', $typeId, $status )
        );
    }
}
