<?php
/**
 * File containing the Integer Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Integer;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue;

/**
 * Value for Integer field type
 */
class Value implements ValueInterface
{
    /**
     * Content of the value
     *
     * @var int
     */
    public $value = 0;

    /**
     * Construct a new Value object and initialize with $value
     *
     * @param int $value
     */
    public function __construct( $value = null )
    {
        if ( $value !== null )
            $this->value = $value;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( (int)$stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
