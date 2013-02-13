<?php
/**
 * File containing the FieldValue Converter NotFound Exception class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Exception thrown if no converter for a type was found
 */
class NotFound extends NotFoundException
{
    /**
     * Creates a new exception for $typeName
     *
     * @param mixed $typeName
     */
    public function __construct( $typeName )
    {
        parent::__construct(
            'eZ\\Publish\\SPI\\Persistence\\Content\\FieldValue\\Converter\\*',
            $typeName
        );
    }
}
