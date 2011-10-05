<?php
/**
 * File containing the XmlText converter
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
    ezp\Content\FieldType\XmlText\Value as XmlTextValue,
    ezp\Content\FieldType\FieldSettings;

class XmlText implements Converter
{
    // const STRING_LENGTH_VALIDATOR_FQN = 'ezp\\Content\\FieldType\\TextLine\\StringLengthValidator';

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \ezp\Persistence\Content\FieldValue $value
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data->text;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue $value
     * @param \ezp\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new XmlTextValue( $value->dataText );
    }

    /**
     * Converts field definition data from $fieldDefinition into $storageFieldDefinition
     *
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDefinition
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDefinition, StorageFieldDefinition $storageDefinition )
    {
        if ( isset( $fieldDefinition->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_FQN]['maxStringLength'] ) )
        {
            $storageDefinition->dataInt1 = $fieldDefinition->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_FQN]['maxStringLength'];
        }

        $storageDefinition->dataText1 = $fieldDefinition->fieldTypeConstraints->fieldSettings['defaultText'];
    }

    /**
     * Converts field definition data from $storageDefinition into $fieldDefinition
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition $storageDefinition
     * @param \ezp\Persistence\Content\Type\FieldDefinition $fieldDefinition
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDefinition, FieldDefinition $fieldDefinition )
    {
        $fieldDefinition->fieldTypeConstraints = new FieldTypeConstraints;

        $settings = new FieldSettings;

        if ( !empty( $storageDefinion->dataInt1 ) )
        {
            $settings['numRows'] = $storageDefinion->dataInt1;
        }

        if ( !empty( $storageDefinion->dataText2 ) )
        {
            $settings['tagPreset'] = $storageDefinion->dataText2;
        }

        $fieldDefinition->fieldTypeConstraints->fieldSettings = $settings;
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
