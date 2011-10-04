<?php
/**
 * File containing the Selection converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Content\FieldType\FieldSettings,
    ezp\Content\FieldType\Selection\Value as SelectionValue,
    DOMDocument;

class Selection implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new SelectionValue();
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
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
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $options = array();

        foreach ( simplexml_load_string( $storageDef->dataText5 )->options->option as $option )
        {
            $options[(int)$option["id"]] = (string)$option["name"];
        }

        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints;
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => !empty( $storageDef->dataInt1 ) ? (bool)$storageDef->dataInt1 : false,
                "options" => $options,
            )
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
        return "sort_key_string";
    }

}
