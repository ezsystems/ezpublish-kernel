<?php
/**
 * File containing the Country InvalidValue Exception class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Repository\FieldType\Country\Exception;
use ezp\Base\Exception as BaseException,
    Exception;

/**
 * Exception thrown if an invalid identifier is used for a country
 */
class InvalidValue extends Exception implements BaseException
{
    /**
     * Creates a new exception when $value is invalid.
     *
     * @param mixed $value
     */
    public function __construct( $value )
    {
        parent::__construct(
            "\"$value\" is not a valid value country identifier."
        );
    }
}
