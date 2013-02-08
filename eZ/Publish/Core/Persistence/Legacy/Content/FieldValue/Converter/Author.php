<?php
/**
 * File containing the Author converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use DOMDocument;

class Author implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return Author
     */
    public static function create()
    {
        return new self;
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $this->generateXmlString( $value->data );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $this->restoreValueFromXmlString( $value->dataText );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        // Nothing to store
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->defaultValue->data = array();
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }

    /**
     * Generates XML string from $authorValue to be stored in storage engine
     *
     * @param array $authorValue
     *
     * @return string The generated XML string
     */
    private function generateXmlString( array $authorValue )
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );

        $root = $doc->createElement( 'ezauthor' );
        $doc->appendChild( $root );

        $authors = $doc->createElement( 'authors' );
        $root->appendChild( $authors );

        foreach ( $authorValue as $author )
        {
            $authorNode = $doc->createElement( 'author' );
            $authorNode->setAttribute( 'id', $author["id"] );
            $authorNode->setAttribute( 'name', $author["name"] );
            $authorNode->setAttribute( 'email', $author["email"] );
            $authors->appendChild( $authorNode );
            unset( $authorNode );
        }

        return $doc->saveXML();
    }

    /**
     * Restores an author Value object from $xmlString
     *
     * @param string $xmlString XML String stored in storage engine
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
     */
    private function restoreValueFromXmlString( $xmlString )
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $authors = array();

        if ( $dom->loadXML( $xmlString ) === true )
        {
            foreach ( $dom->getElementsByTagName( 'author' ) as $author )
            {
                $authors[] = array(
                    'id' => $author->getAttribute( 'id' ),
                    'name' => $author->getAttribute( 'name' ),
                    'email' => $author->getAttribute( 'email' )
                );
            }
        }

        return $authors;
    }
}
