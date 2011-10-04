<?php
/**
 * File containing the Country converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Exception\InvalidValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Content\FieldType\FieldSettings,
    ezp\Content\FieldType\Country\Value as CountryValue,
    DOMDocument;

class Country implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     * @throws \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Exception\InvalidValue if a value cannot be converted
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $countriesAlpha2 = array();
        $countriesLowercaseName = array();
        foreach ( $value->data->getCountriesInfo() as $countryInfo ) {
            $countriesAlpha2[] = $countryInfo["Alpha2"];
            $countriesLowercaseName[] = strtolower( $countryInfo["Name"] );
        }

        $storageFieldValue->dataText = join( ",", $countriesAlpha2 );
        sort( $countriesAlpha2 );
        $storageFieldValue->sortKeyString = join( ",", $countriesLowercaseName );
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new CountryValue( !empty( $value->dataText ) ? explode( ",", $value->dataText ) : null );
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

        if ( !empty( $fieldSettings["default"] ) )
        {
            $countries = new CountryValue( $fieldSettings["default"] );
            $storageDef->dataText5 = join( ",", array_keys( $countries->getCountriesInfo() ) );
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
        $fieldDef->fieldTypeConstraints = new FieldTypeConstraints;
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                "isMultiple" => !empty( $storageDef->dataInt1 ) ? (bool)$storageDef->dataInt1 : false,
                "default" => !empty( $storageDef->dataText5 ) ? new CountryValue( explode( ",", $storageDef->dataText5 ) ) : null,
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
