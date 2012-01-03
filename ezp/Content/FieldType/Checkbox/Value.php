<?php
/**
 * File containing the Checkbox Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Checkbox;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue;

/**
 * Value for Checkbox field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Boolean value
     *
     * @var bool
     */
    public $bool;

    /**
     * Construct a new Value object and initialize it $boolValue
     *
     * @param bool $boolValue
     */
    public function __construct( $boolValue = false )
    {
        $this->bool = $boolValue;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     * @return \ezp\Content\FieldType\Checkbox\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( (bool)$stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     * @return string
     */
    public function __toString()
    {
        return $this->bool ? '1' : '0';
    }
}
