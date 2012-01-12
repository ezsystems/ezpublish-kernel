<?php
/**
 * File containing the XmlText converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\XmlText\Value as XmlTextValue,
    ezp\Content\FieldType\FieldSettings;

class XmlText implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data->rawText;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new XmlTextValue( $value->dataText, XmlTextValue::INPUT_FORMAT_RAW );
    }

    /**
     * Converts field definition data from $fieldDefinition into $storageFieldDefinition
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDefinition
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDefinition, StorageFieldDefinition $storageDefinition )
    {
        $storageDefinition->dataInt1 = $fieldDefinition->fieldTypeConstraints->fieldSettings['numRows'];
        $storageDefinition->dataText2 = $fieldDefinition->fieldTypeConstraints->fieldSettings['tagPreset'];
    }

    /**
     * Converts field definition data from $storageDefinition into $fieldDefinition
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDefinition
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDefinition, FieldDefinition $fieldDefinition )
    {
        $fieldDefinition->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'numRows' => $storageDefinition->dataInt1,
                'tagPreset' => $storageDefinition->dataText2,
                'defaultText' => $storageDefinition->dataText1
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
        return null;
    }

}
