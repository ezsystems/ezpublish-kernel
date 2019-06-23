<?php

/**
 * File containing the Float converter.
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

class FloatConverter implements Converter
{
    const FLOAT_VALIDATOR_IDENTIFIER = 'FloatValueValidator';

    const HAS_MIN_VALUE = 1;
    const HAS_MAX_VALUE = 2;

    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return static
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
        $storageFieldValue->dataFloat = $value->data;
        $storageFieldValue->sortKeyInt = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataFloat;
        $fieldValue->sortKey = $value->sortKeyInt;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        if (isset($fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['minFloatValue'])) {
            $storageDef->dataFloat1 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['minFloatValue'];
        }

        if (isset($fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['maxFloatValue'])) {
            $storageDef->dataFloat2 = $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER]['maxFloatValue'];
        }

        // Defining dataFloat4 which holds the validator state (min value/max value)
        $storageDef->dataFloat4 = $this->getStorageDefValidatorState($storageDef->dataFloat1, $storageDef->dataFloat2);
        $storageDef->dataFloat3 = $fieldDef->defaultValue->data;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * The constant (HAS_MIN_VALUE, HAS_MAX_VALUE) are set if the field max or min are define
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $validatorParameters = ['minFloatValue' => null, 'maxFloatValue' => null];
        if ($storageDef->dataFloat4 & self::HAS_MIN_VALUE) {
            $validatorParameters['minFloatValue'] = $storageDef->dataFloat1;
        }

        if ($storageDef->dataFloat4 & self::HAS_MAX_VALUE) {
            $validatorParameters['maxFloatValue'] = $storageDef->dataFloat2;
        }
        $fieldDef->fieldTypeConstraints->validators[self::FLOAT_VALIDATOR_IDENTIFIER] = $validatorParameters;
        $fieldDef->defaultValue->data = $storageDef->dataFloat3;
        $fieldDef->defaultValue->sortKey = 0;
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
        return 'sort_key_int';
    }

    /**
     * Returns validator state for storage definition.
     * Validator state is a bitfield value composed of:
     * - {@link self::HAS_MAX_VALUE}
     * - {@link self::HAS_MIN_VALUE}.
     *
     * @param int|null $minValue Minimum int value, or null if not set
     * @param int|null $maxValue Maximum int value, or null if not set
     *
     * @return int
     */
    private function getStorageDefValidatorState($minValue, $maxValue)
    {
        $state = 0;

        if ($minValue !== null) {
            $state |= self::HAS_MIN_VALUE;
        }

        if ($maxValue !== null) {
            $state |= self::HAS_MAX_VALUE;
        }

        return $state;
    }
}
