<?php
/**
 * File containing the Keyword Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Keyword;
use ezp\Content\FieldType\Value as ValueInterface;

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
     * Initializes the keyword value with a simple string.
     *
     * @param string $stringValue A comma separated list of tags, eg: "php, eZ Publish, html5"
     *                            Space after comma is optional, each tag is trimmed to remove it.
     * @return \ezp\Content\FieldType\Keyword\Value Instance of the keyword value
     * @throws \ezp\Base\Exception\InvalidArgumentValue
     */
    public static function fromString( $stringValue )
    {
        $tags = array();
        foreach ( explode( ',', $stringValue ) as $tag )
        {
            $tag = trim( $tag );
            if ( $tag )
                $tags[] = $tag;
        }

        return new static( array_unique( $tags ) );
    }

    /**
     * Returns a string representation of the keyword value.
     *
     * @return string A comma separated list of tags, eg: "php, eZ Publish, html5"
     */
    public function __toString()
    {
        return implode( ', ', $this->values );
    }
}
