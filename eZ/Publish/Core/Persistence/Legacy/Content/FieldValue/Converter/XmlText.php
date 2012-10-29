<?php
/**
 * File containing the XmlText converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition,
    eZ\Publish\Core\FieldType\FieldSettings,
    eZ\Publish\Core\FieldType\XmlText\Value,
    DOMDocument;

class XmlText implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return XmlText
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
        $storageFieldValue->dataText = $value->data->saveXML();
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $domDoc = new DOMDocument;
        $domDoc->loadXML( $value->dataText ?: Value::EMPTY_VALUE );
        $fieldValue->data = $domDoc;
    }

    /**
     * Converts field definition data from $fieldDefinition into $storageFieldDefinition
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDefinition, StorageFieldDefinition $storageDefinition )
    {
        $storageDefinition->dataInt1 = $fieldDefinition->fieldTypeConstraints->fieldSettings['numRows'];
        $storageDefinition->dataText2 = $fieldDefinition->fieldTypeConstraints->fieldSettings['tagPreset'];
        if ( !empty( $fieldDefinition->defaultValue->data ) )
            $storageDefinition->dataText1 = $fieldDefinition->defaultValue->data->saveXML();
    }

    /**
     * Converts field definition data from $storageDefinition into $fieldDefinition
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDefinition
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDefinition, FieldDefinition $fieldDefinition )
    {
        $fieldDefinition->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'numRows' => $storageDefinition->dataInt1,
                'tagPreset' => $storageDefinition->dataText2
            )
        );

        $defaultValue = null;
        if ( !empty( $storageDefinition->dataText1 ) )
        {
            $defaultValue = new DOMDocument;
            $defaultValue->loadXML( $storageDefinition->dataText1 );
        }
        $fieldDefinition->defaultValue->data = $defaultValue;
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
        return null;
    }

}
