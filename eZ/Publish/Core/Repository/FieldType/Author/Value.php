<?php
/**
 * File containing the Author Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Author;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Base\Exception\Logic;

/**
 * Value for Author field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * List of authors
     *
     * @var \eZ\Publish\Core\Repository\FieldType\Author\AuthorCollection
     */
    public $authors;

    /**
     * Construct a new Value object and initialize with $authors
     *
     * @param \eZ\Publish\Core\Repository\FieldType\Author\Author[] $authors
     */
    public function __construct( array $authors = array() )
    {
        $this->authors = new AuthorCollection( $this, $authors );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        throw new Logic( 'fromString() is not supported by this field type' );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        if ( empty( $this->authors ) )
            return "";

        $authorNames = array();

        if ( $this->authors instanceof AuthorCollection )
        {
            foreach ( $this->authors as $author )
            {
                $authorNames[] = $author->name;
            }
        }

        return implode( ', ', $authorNames );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\ValueInterface::getTitle()
     */
    public function getTitle()
    {
        return isset( $this->authors[0] ) ? $this->authors[0]->name : "";
    }
}
