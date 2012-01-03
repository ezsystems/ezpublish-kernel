<?php
/**
 * File containing the Rating Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Rating;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue;

/**
 * Value for Rating field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Is rating disabled
     *
     * @var bool
     */
    public $isDisabled = false;

    /**
     * Construct a new Value object and initialize it with its $isDisabled state
     *
     * @param bool $isDisabled
     */
    public function __construct( $isDisabled = false )
    {
        $this->isDisabled = (bool)$isDisabled;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( (bool)(int)$stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return $this->isDisabled ? "1" : "0";
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        throw new \RuntimeException( 'Implement this method' );
    }
}
