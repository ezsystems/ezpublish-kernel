<?php
/**
 * File containing the Country converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\InvalidValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Repository\FieldType\FieldSettings,
    eZ\Publish\Core\Repository\FieldType\Country\Value as CountryValue,
    DOMDocument;

class Country implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\InvalidValue if a value cannot be converted
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
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new CountryValue( !empty( $value->dataText ) ? explode( ",", $value->dataText ) : null );
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

        if ( !empty( $fieldSettings["default"] ) )
        {
            $countries = new CountryValue( $fieldSettings["default"] );
            $storageDef->dataText5 = join( ",", array_keys( $countries->getCountriesInfo() ) );
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
