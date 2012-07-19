<?php
/**
 * File containing the TextLine converter
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
    eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue,
    eZ\Publish\Core\FieldType\FieldSettings;

class TextLine implements Converter
{
    const STRING_LENGTH_VALIDATOR_IDENTIFIER = "StringLengthValidator";

    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return TextLine
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
        $storageFieldValue->dataText = $value->data;
        $storageFieldValue->sortKeyString = $value->sortKey['sort_key_string'];
        // @TODO: This shouldn't be done here, a converter shouldn't add missing data, it should only convert.
        $storageFieldValue->sortKeyInt = 0;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = $value->dataText;
        // @todo: Feel there is room for some improvement here, to generalize this code across field types.
        $fieldValue->sortKey = array( 'sort_key_string' => $value->sortKeyString );
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['maxStringLength'] ) )
        {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['maxStringLength'];
        }
        else
        {
            $storageDef->dataInt1 = 0;
        }

        if ( isset( $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['minStringLength'] ) )
        {
            $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['minStringLength'];
        }
        else
        {
            $storageDef->dataInt2 = 0;
        }

        $storageDef->dataText1 = $fieldDef->defaultValue->data;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $validatorConstraints = array();

        if ( isset( $storageDef->dataInt1 ) )
        {
            $validatorConstraints[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]["maxStringLength"] = (int)$storageDef->dataInt1;
        }
        if ( isset( $storageDef->dataInt2 ) )
        {
            $validatorConstraints[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]["minStringLength"] = (int)$storageDef->dataInt2;
        }

        $fieldDef->fieldTypeConstraints->validators = $validatorConstraints;
        $fieldDef->defaultValue->data = isset( $storageDef->dataText1 ) ? $storageDef->dataText1 : '';
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
