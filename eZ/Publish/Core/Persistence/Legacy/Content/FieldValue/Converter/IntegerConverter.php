<?php

/**
 * File containing the Integer converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

class IntegerConverter implements Converter
{
    const VALIDATOR_IDENTIFIER = 'IntegerValueValidator';

    const HAS_MIN_VALUE = 1;
    const HAS_MAX_VALUE = 2;

    /**
     * Factory for current class.
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @return Integer
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
        $storageFieldValue->dataInt = $value->data;
        $storageFieldValue->sortKeyInt = (int)$value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataInt;
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
        $minIntegerValue = isset($fieldDef->fieldTypeConstraints->validators[self::VALIDATOR_IDENTIFIER]['minIntegerValue']) ?
            $fieldDef->fieldTypeConstraints->validators[self::VALIDATOR_IDENTIFIER]['minIntegerValue'] : null;
        $maxIntegerValue = isset($fieldDef->fieldTypeConstraints->validators[self::VALIDATOR_IDENTIFIER]['maxIntegerValue']) ?
            $fieldDef->fieldTypeConstraints->validators[self::VALIDATOR_IDENTIFIER]['maxIntegerValue'] : null;

        $storageDef->dataInt1 = (int)$minIntegerValue;
        $storageDef->dataInt2 = (int)$maxIntegerValue;

        // Defining dataInt4 which holds the validator state (min value/max value/minMax value)
        $storageDef->dataInt4 = $this->getStorageDefValidatorState($minIntegerValue, $maxIntegerValue);
        $storageDef->dataInt3 = $fieldDef->defaultValue->data;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $validatorParameters = array('minIntegerValue' => null, 'maxIntegerValue' => null);
        if ($storageDef->dataInt4 & self::HAS_MIN_VALUE) {
            $validatorParameters['minIntegerValue'] = $storageDef->dataInt1;
        }

        if ($storageDef->dataInt4 & self::HAS_MAX_VALUE) {
            $validatorParameters['maxIntegerValue'] = $storageDef->dataInt2;
        }
        $fieldDef->fieldTypeConstraints->validators[self::VALIDATOR_IDENTIFIER] = $validatorParameters;
        $fieldDef->defaultValue->data = $storageDef->dataInt3;
        $fieldDef->defaultValue->sortKey = ($storageDef->dataInt3 === null ? 0 : $storageDef->dataInt3);
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
     *   - {@link self::HAS_MAX_VALUE}
     *   - {@link self::HAS_MIN_VALUE}.
     *
     * @param int|false|null $minValue Minimum int value, false if none, or null if not set
     * @param int|false|null $maxValue Maximum int value, false if none, or null if not set
     *
     * @return int
     */
    private function getStorageDefValidatorState($minValue, $maxValue)
    {
        $state = 0;

        if (is_numeric($minValue)) {
            $state |= self::HAS_MIN_VALUE;
        }

        if (is_numeric($maxValue)) {
            $state |= self::HAS_MAX_VALUE;
        }

        return $state;
    }
}
