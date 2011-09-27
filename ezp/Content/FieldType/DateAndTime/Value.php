<?php
/**
 * File containing the DateAndTime Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\DateAndTime;
use ezp\Content\FieldType\Value as ValueInterface,
    DateTime,
    RuntimeException;

/**
 * Value for DateAndTime field type
 */
class Value implements ValueInterface
{
    /**
     * Date content
     *
     * @var DateTime
     */
    public $value;

    /**
     * Construct a new Value object and initialize with $dateTime
     *
     * @param \DateTime $dateTime
     */
    public function __construct( DateTime $dateTime = null )
    {
        if ( $dateTime !== null )
            $this->value = $dateTime;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        throw new RuntimeException( "@TODO: Implement" );
        return new static( $stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        throw new RuntimeException( "@TODO: Implement" );
        return $this->value;
    }
}
