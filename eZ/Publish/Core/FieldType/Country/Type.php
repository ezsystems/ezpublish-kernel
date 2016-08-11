<?php

/**
 * File containing the Country class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Country;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\Country\Exception\InvalidValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * The Country field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $settingsSchema = array(
        'isMultiple' => array(
            'type' => 'boolean',
            'default' => false,
        ),
    );

    /**
     * @var array
     */
    protected $countriesInfo;

    /**
     * @param array $countriesInfo Array of countries data
     */
    public function __construct(array $countriesInfo)
    {
        $this->countriesInfo = $countriesInfo;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezcountry';
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string)$value;
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\eZ\Publish\Core\FieldType\Country\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = $this->fromHash($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!is_array($value->countries)) {
            throw new InvalidArgumentType(
                '$value->countries',
                'array',
                $value->countries
            );
        }
    }

    /**
     * Validates field value against 'isMultiple' setting.
     *
     * Does not use validators.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Country\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $validationErrors = array();

        if ($this->isEmptyValue($fieldValue)) {
            return $validationErrors;
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ((!isset($fieldSettings['isMultiple']) || $fieldSettings['isMultiple'] === false)
            && count($fieldValue->countries) > 1) {
            $validationErrors[] = new ValidationError(
                'Field definition does not allow multiple countries to be selected.',
                null,
                array(),
                'countries'
            );
        }

        foreach ($fieldValue->countries as $alpha2 => $countryInfo) {
            if (!isset($this->countriesInfo[$alpha2])) {
                $validationErrors[] = new ValidationError(
                    "Country with Alpha2 code '%alpha2%' is not defined in FieldType settings.",
                    null,
                    array(
                        '%alpha2%' => $alpha2,
                    ),
                    'countries'
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $value
     *
     * @return array
     */
    protected function getSortInfo(BaseValue $value)
    {
        $countries = array();
        foreach ($value->countries as $countryInfo) {
            $countries[] = $this->transformationProcessor->transformByGroup($countryInfo['Name'], 'lowercase');
        }

        sort($countries);

        return implode(',', $countries);
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Country\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        $countries = array();
        foreach ($hash as $country) {
            foreach ($this->countriesInfo as $countryInfo) {
                switch ($country) {
                    case $countryInfo['Name']:
                    case $countryInfo['Alpha2']:
                    case $countryInfo['Alpha3']:
                        $countries[$countryInfo['Alpha2']] = $countryInfo;
                        continue 3;
                }
            }

            throw new InvalidValue($country);
        }

        return new Value($countries);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Country\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array_keys($value->countries);
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
        $validationErrors = array();

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    array(
                        '%setting%' => $name,
                    ),
                    "[$name]"
                );
                continue;
            }

            switch ($name) {
                case 'isMultiple':
                    if (!is_bool($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }
}
