<?php
/**
 * File containing the Keyword Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Keyword;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue;

/**
 * Value for Keyword field type
 */
class Value implements ValueInterface
{
    /**
     * Content of the value
     *
     * @var string[]
     */
    public $values = array();

    /**
     * Construct a new Value object and initialize with $values
     *
     * @param string[] $values
     */
    public function __construct( array $values = null )
    {
        if ( $values !== null )
            $this->values = $values;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function build( PersistenceFieldValue $vo )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        throw new \RuntimeException( "@TODO: Implement" );
        return new static( $stringValue );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        throw new \RuntimeException( "@TODO: Implement" );
        return (string)$this->values;
    }
}
