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
    ezp\Base\Exception\Logic;

/**
 * Value for Author field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * List of authors
     *
     * @var \ezp\Content\FieldType\Author\AuthorCollection
     */
    public $authors;

    /**
     * Construct a new Value object and initialize with $authors
     *
     * @param \ezp\Content\FieldType\Author\Author[] $authors
     */
    public function __construct( array $authors = array() )
    {
        $this->authors = new AuthorCollection( $this, $authors );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        throw new Logic( 'fromString() is not supported by this field type' );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        $string = '';
        if ( count( $this->authors ) > 0 )
        {
            $authorNames = array();
            foreach ( $this->authors as $author )
            {
                $authorNames[] = $author->name;
            }

            $string = implode( ', ', $authorNames );
        }

        return $string;
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        $title = '';
        if ( count( $this->authors ) > 0 )
        {
            $title = $this->authors[0]->name;
        }

        return $title;
    }
}
