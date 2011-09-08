<?php
/**
 * File containing the Author class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Handler;
use ezp\Content\FieldType\Handler,
    DOMDocument;

class Author implements Handler
{
    protected $authors = array();
    protected $authorCount = 0;

    /**
     * Populates the field type handler with data from a field type.
     *
     * @param mixed $value
     * @return void
     */
    public function initWithFieldTypeValue( $value )
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        if ( $dom->loadXML( $value ) )
        {
            $authors = $dom->getElementsByTagName( 'author' );
            foreach ( $authors as $author )
            {
                $this->addAuthor( $author->getAttribute( "id" ), $author->getAttribute( "name" ), $author->getAttribute( "email" ) );
            }
        }


    }

    /**
     * Return a compatible value to store in a field type after manipulation
     * in the handler.
     *
     * @return mixed
     */
    public function getFieldTypeValue()
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );

        $root = $doc->createElement( "ezauthor" );
        $doc->appendChild( $root );

        $authors = $doc->createElement( "authors" );
        $root->appendChild( $authors );

        $id = 0;
        foreach ( $this->authors as $author )
        {
            unset( $authorNode );
            $authorNode = $doc->createElement( "author" );
            $authorNode->setAttribute( "id", $id++ );
            $authorNode->setAttribute( "name", $author["name"] );
            $authorNode->setAttribute( "email", $author["email"] );

            $authors->appendChild( $authorNode );
        }
        return $doc->saveXML();
    }

    // This method is not implemented yet, in the legacy engine it is only used
    // for the old title() callback, for name pattern resolution.
    //public function name() {}

    public function addAuthor( $id, $name, $email )
    {
        if ( $id === false || $id === null)
        {
            $id = $this->authors[$this->authorCount - 1]['id'] + 1;
        }

        $this->authors[] = array(
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "is_default" => false
        );

        $this->authorCount++;

    }

    public function authorList()
    {
        return $this->authors;
    }

    public function isEmpty()
    {
        return $this->authorCount === 0;
    }
}
