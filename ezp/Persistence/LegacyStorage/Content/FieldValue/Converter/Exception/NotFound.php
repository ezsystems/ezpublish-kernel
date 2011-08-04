<?php
/**
 * File containing the FieldValue Converter NotFound Exception class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\LegacyStorage\Content\FieldValue\Converter\Exception;

/**
 * Exception thrown if no converter for a type was found
 */
class NotFound extends \InvalidArgumentException
{
    /**
     * Creates a new exception for $typeName
     *
     * @param mixed $typeName
     */
    public function __construct( $typeName )
    {
        parent::__construct(
            sprintf( 'FieldValue Converter for type "%s" not found.', $typeName )
        );
    }
}
