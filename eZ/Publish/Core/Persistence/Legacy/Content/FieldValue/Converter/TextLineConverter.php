<?php

/**
 * File containing the TextLine converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

class TextLineConverter implements Converter
{
    const STRING_LENGTH_VALIDATOR_IDENTIFIER = 'StringLengthValidator';

    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLineConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $value->data;
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataText;
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        if (isset($fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['maxStringLength'])) {
            $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['maxStringLength'];
        } else {
            $storageDef->dataInt1 = 0;
        }

        if (isset($fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['minStringLength'])) {
            $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->validators[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['minStringLength'];
        } else {
            $storageDef->dataInt2 = 0;
        }

        $storageDef->dataText1 = $fieldDef->defaultValue->data;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $validatorConstraints = [];

        if (isset($storageDef->dataInt1)) {
            $validatorConstraints[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['maxStringLength'] =
                $storageDef->dataInt1 != 0 ?
                    (int)$storageDef->dataInt1 :
                    null;
        }
        if (isset($storageDef->dataInt2)) {
            $validatorConstraints[self::STRING_LENGTH_VALIDATOR_IDENTIFIER]['minStringLength'] =
                $storageDef->dataInt2 != 0 ?
                    (int)$storageDef->dataInt2 :
                    null;
        }

        $fieldDef->fieldTypeConstraints->validators = $validatorConstraints;
        $fieldDef->defaultValue->data = $storageDef->dataText1 ?: null;
        $fieldDef->defaultValue->sortKey = $storageDef->dataText1 ?: '';
    }

    /**
     * Returns the name of the index column in the attribute table.
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
