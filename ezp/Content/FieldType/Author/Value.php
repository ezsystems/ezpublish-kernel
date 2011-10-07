<?php
/**
 * File containing the Author Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Author;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue,
    RuntimeException;

/**
 * Value for Author field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * List of authors
     *
     * @var array
     */
    public $authors;

    /**
     * Construct a new Value object and initialize with $authors
     *
     * @param array $authors
     */
    public function __construct( array $authors = null )
    {
        if ( $authors !== null )
            $this->authors = $authors;
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
        return $this->authors;
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        throw new \RuntimeException( 'Implement this method' );
    }
}
