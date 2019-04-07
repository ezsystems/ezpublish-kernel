<?php

/**
 * File containing the DateTime class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\DateAndTime;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use DateInterval;
use DateTime;

class Type extends FieldType
{
    /**
     * Default value types.
     */
    const DEFAULT_EMPTY = 0;
    const DEFAULT_CURRENT_DATE = 1;
    const DEFAULT_CURRENT_DATE_ADJUSTED = 2;

    protected $settingsSchema = [
        'useSeconds' => [
            'type' => 'bool',
            'default' => false,
        ],
        // One of the DEFAULT_* class constants
        'defaultType' => [
            'type' => 'choice',
            'default' => self::DEFAULT_EMPTY,
        ],
        /*
         * @var DateInterval
         * Used only if defaultValueType is set to DEFAULT_CURRENT_DATE_ADJUSTED
         */
        'dateInterval' => [
            'type' => 'dateInterval',
            'default' => null,
        ],
    ];

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezdatetime';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|int|DateTime|\eZ\Publish\Core\FieldType\DateAndTime\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = Value::fromString($inputValue);
        }

        if (is_int($inputValue)) {
            $inputValue = Value::fromTimestamp($inputValue);
        }

        if ($inputValue instanceof DateTime) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->value instanceof DateTime) {
            throw new InvalidArgumentType(
                '$value->value',
                'DateTime',
                $value->value
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     *
     * @return int|null
     */
    protected function getSortInfo(BaseValue $value)
    {
        if ($value->value === null) {
            return null;
        }

        return $value->value->getTimestamp();
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash Null or associative array containing one of the following (first value found in the order below is picked):
     *                    'rfc850': Date in RFC 850 format (DateTime::RFC850)
     *                    'timestring': Date in parseable string format supported by DateTime (e.g. 'now', '+3 days')
     *                    'timestamp': Unix timestamp
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        if (isset($hash['rfc850']) && $hash['rfc850']) {
            return Value::fromString($hash['rfc850']);
        }

        if (isset($hash['timestring']) && is_string($hash['timestring'])) {
            return Value::fromString($hash['timestring']);
        }

        return Value::fromTimestamp((int)$hash['timestamp']);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\DateAndTime\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        if ($value->value instanceof DateTime) {
            return [
                'timestamp' => $value->value->getTimestamp(),
                'rfc850' => $value->value->format(DateTime::RFC850),
            ];
        }

        return [
            'timestamp' => 0,
            'rfc850' => null,
        ];
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = [];

        foreach ($fieldSettings as $name => $value) {
            if (isset($this->settingsSchema[$name])) {
                switch ($name) {
                    case 'useSeconds':
                        if (!is_bool($value)) {
                            $validationErrors[] = new ValidationError(
                                "Setting 'Use seconds' value must be of boolean type",
                                null,
                                [],
                                "[$name]"
                            );
                        }
                        break;
                    case 'defaultType':
                        $definedTypes = [
                            self::DEFAULT_EMPTY,
                            self::DEFAULT_CURRENT_DATE,
                            self::DEFAULT_CURRENT_DATE_ADJUSTED,
                        ];
                        if (!in_array($value, $definedTypes, true)) {
                            $validationErrors[] = new ValidationError(
                                "Setting 'Default value' is of unknown type",
                                null,
                                [],
                                "[$name]"
                            );
                        }
                        break;
                    case 'dateInterval':
                        if (isset($value)) {
                            if ($value instanceof DateInterval) {
                                // String conversion of $value, because DateInterval objects cannot be compared directly
                                if (
                                    isset($fieldSettings['defaultType'])
                                    && $fieldSettings['defaultType'] !== self::DEFAULT_CURRENT_DATE_ADJUSTED
                                    && $value->format('%y%m%d%h%i%s') !== '000000'
                                ) {
                                    $validationErrors[] = new ValidationError(
                                        "Setting 'Current date and time adjusted by' can be used only when setting 'Default value' is set to 'Adjusted current datetime'",
                                        null,
                                        [],
                                        "[$name]"
                                    );
                                }
                            } else {
                                $validationErrors[] = new ValidationError(
                                    "Setting 'Current date and time adjusted by' value must be an instance of 'DateInterval' class",
                                    null,
                                    [],
                                    "[$name]"
                                );
                            }
                        }
                        break;
                }
            } else {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    ['%setting%' => $name],
                    "[$name]"
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Converts the given $fieldSettings to a simple hash format.
     *
     * This is the default implementation, which just returns the given
     * $fieldSettings, assuming they are already in a hash format. Overwrite
     * this in your specific implementation, if necessary.
     *
     * @param mixed $fieldSettings
     *
     * @return array|hash|scalar|null
     */
    public function fieldSettingsToHash($fieldSettings)
    {
        $fieldSettingsHash = parent::fieldSettingsToHash($fieldSettings);

        if (isset($fieldSettingsHash['dateInterval'])) {
            $fieldSettingsHash['dateInterval'] = $fieldSettingsHash['dateInterval']->format(
                'P%r%yY%r%mM%r%dDT%r%hH%iM%r%sS'
            );
        }

        return $fieldSettingsHash;
    }

    /**
     * Converts the given $fieldSettingsHash to field settings of the type.
     *
     * This is the reverse operation of {@link fieldSettingsToHash()}.
     *
     * This is the default implementation, which just returns the given
     * $fieldSettingsHash, assuming the supported field settings are already in
     * a hash format. Overwrite this in your specific implementation, if
     * necessary.
     *
     * @param array|hash|scalar|null $fieldSettingsHash
     *
     * @return mixed
     */
    public function fieldSettingsFromHash($fieldSettingsHash)
    {
        $fieldSettings = parent::fieldSettingsFromHash($fieldSettingsHash);

        if (isset($fieldSettings['dateInterval'])) {
            $fieldSettings['dateInterval'] = new DateInterval($fieldSettings['dateInterval']);
        }

        return $fieldSettings;
    }
}
