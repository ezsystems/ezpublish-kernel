<?php
/**
 * File containing the TextLine converter
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter;
use \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter,
    \ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    \ezp\Persistence\Content\FieldValue,
    \ezp\Persistence\Content\Type\FieldDefinition,
    \ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Content\FieldType\TextLine\Value as TextLineValue;

class TextLine implements Converter
{
    const STRING_LENGTH_VALIDATOR_FQN = 'ezp\\Content\\FieldType\\TextLine\\StringLengthValidator';
    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param FieldValue $value
     * @param StorageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataText = $value->data->text;
        $storageFieldValue->sortKeyString = $value->sortKey['sort_key_string'];
        // @TODO: This shouldn't be done here, a converter shouldn't add missing data, it should only convert.
        $storageFieldValue->sortKeyInt = 0;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param StorageFieldValue $value
     * @param FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = new TextLineValue( $value->dataText );
        // TODO: Feel there is room for some improvement here, to generalize this code across field types.
        $fieldValue->sortKey = array( 'sort_key_string' => $value->sortKeyString );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param FieldDefinition $fieldDef
     * @param StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints[self::STRING_LENGTH_VALIDATOR_FQN]['maxStringLength'] ) )
        {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints[self::STRING_LENGTH_VALIDATOR_FQN]['maxStringLength'];
        }
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param StorageFieldDefinition $storageDef
     * @param FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        if ( !empty( $storageDef->dataInt1 ) )
        {
            $fieldDef->fieldTypeConstraints = array(
                self::STRING_LENGTH_VALIDATOR_FQN => array( 'maxStringLength' => $storageDef->dataInt1 ) );
        }
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
        return 'sort_key_string';
    }

}
