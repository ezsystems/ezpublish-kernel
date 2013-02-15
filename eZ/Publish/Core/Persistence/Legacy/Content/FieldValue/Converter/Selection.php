<?php
/**
 * File containing the Selection converter
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use DOMDocument;

class Selection implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return Selection
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
        $storageFieldValue->sortKeyString = $storageFieldValue->dataText = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        if ( $value->dataText !== '' )
        {
            $fieldValue->data = array_map(
                'intval',
                explode( '-', $value->dataText )
            );
        }
        else
        {
            $fieldValue->data = array();
        }
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;

        if ( isset( $fieldSettings["isMultiple"] ) )
            $storageDef->dataInt1 = (int)$fieldSettings["isMultiple"];

        if ( !empty( $fieldSettings["options"] ) )
        {
            $xml = new DOMDocument( "1.0", "utf-8" );
            $xml->appendChild(
                $selection = $xml->createElement( "ezselection" )
            );
            $selection->appendChild(
                $options = $xml->createElement( "options" )
            );
            foreach ( $fieldSettings["options"] as $id => $name )
            {
                $options->appendChild(
                    $option = $xml->createElement( "option" )
                );
                $option->setAttribute( "id", $id );
                $option->setAttribute( "name", $name );
            }
            $storageDef->dataText5 = $xml->saveXML();
        }
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $options = array();

        foreach ( simplexml_load_string( $storageDef->dataText5 )->options->option as $option )
        {
            $options[(int)$option["id"]] = (string)$option["name"];
        }

        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => !empty( $storageDef->dataInt1 ) ? (bool)$storageDef->dataInt1 : false,
                "options" => $options,
            )
        );

        // @todo: Can Selection store a default value in the DB?
        $fieldDef->defaultValue = new FieldValue();
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
        return "sort_key_string";
    }

}
