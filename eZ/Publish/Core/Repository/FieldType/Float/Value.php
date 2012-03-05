<?php
/**
 * File containing the Float Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Float;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue;

/**
 * Value for Float field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Float content
     *
     * @var float
     */
    public $value = 0.0;

    /**
     * Construct a new Value object and initialize with $value
     *
     * @param float $value
     */
    public function __construct( $value = null )
    {
        if ( $value !== null )
            $this->value = $value;
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( $stringValue );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
