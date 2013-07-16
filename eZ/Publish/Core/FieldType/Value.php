<?php
/**
 * File containing the Value abstract class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\SPI\FieldType\Value as ValueInterface;

/**
 * Abstract class for all field value classes.
 * A field value object is to be understood with associated field type
 */
abstract class Value extends ValueObject implements ValueInterface
{
    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    abstract public function __toString();
}
