<?php
/**
 * File containing the Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;

/**
 * Description of Value
 */
interface Value
{
    /**
     * Initializes the field value with a simple string.
     * It's up to the field value to define $stringValue format.
     * If $stringValue format is not supported, an {@link \ezp\Base\Exception\InvalidArgumentValue} exception should be thrown.
     *
     * @param string $stringValue
     * @return \ezp\Content\FieldType\Value Instance of the field value
     * @throws \ezp\Base\Exception\InvalidArgumentValue
     */
    public static function fromString( $stringValue );

    /**
     * Returns a string representation of the field value.
     * This string representation must be compatible with {@link self::fromString()} supported format
     *
     * @return string
     */
    public function __toString();
}
