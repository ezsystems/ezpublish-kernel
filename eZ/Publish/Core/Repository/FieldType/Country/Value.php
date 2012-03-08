<?php
/**
 * File containing the Country Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Country;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

/**
 * Value for Country field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Countries values
     *
     * @var array
     */
    public $values;

    /**
     * Countries data
     *
     * @var array
     */
    public $data = array();

    /**
     * Construct a new Value object and initialize it with its $values and associated $data
     *
     * @param string[] $values
     * @param array[] $data
     */
    public function __construct( array $values = array(), array $data = array() )
    {
        $this->values = $values;
        $this->data = $data;
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( array( $stringValue ) );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return implode( ",", $this->values );
    }
}
