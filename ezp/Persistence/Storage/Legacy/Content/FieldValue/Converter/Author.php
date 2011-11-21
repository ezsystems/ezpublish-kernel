<?php
/**
 * File containing the Author converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\Author\Value as AuthorValue,
    ezp\Content\FieldType\Author\Author as AuthorItem,
    DOMDocument;

class Author implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $this->generateXmlString( $value->data );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $this->restoreValueFromXmlString( $value->dataText );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        // Nothing to store
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints;
        $fieldDef->defaultValue = new FieldValue(
            array( 'data' => new AuthorValue )
        );
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
     * @param \ezp\Content\FieldType\Author\Value $authorValue
     * @return string The generated XML string
     */
    private function generateXmlString( AuthorValue $authorValue )
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );

        $root = $doc->createElement( 'ezauthor' );
        $doc->appendChild( $root );

        $authors = $doc->createElement( 'authors' );
        $root->appendChild( $authors );

        foreach ( $authorValue->authors as $author )
        {
            $authorNode = $doc->createElement( 'author' );
            $authorNode->setAttribute( 'id', $author->id );
            $authorNode->setAttribute( 'name', $author->name );
            $authorNode->setAttribute( 'email', $author->email );
            $authors->appendChild( $authorNode );
            unset( $authorNode );
        }

        return $doc->saveXML();
    }

    /**
     * Restores an author Value object from $xmlString
     *
     * @param string $xmlString XML String stored in storage engine
     * @return \ezp\Content\FieldType\Author\Value
     */
    private function restoreValueFromXmlString( $xmlString )
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $authors = array();

        if ( $dom->loadXML( $xmlString ) === true )
        {
            foreach ( $dom->getElementsByTagName( 'author' ) as $author )
            {
                $authors[] = new AuthorItem(
                    array(
                        'id' => $author->getAttribute( 'id' ),
                        'name' => $author->getAttribute( 'name' ),
                        'email' => $author->getAttribute( 'email' )
                    )
                );
            }
        }

        return new AuthorValue( $authors );
    }
}
