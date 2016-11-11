<?php

/**
 * File containing the FieldType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client;

use eZ\Publish\API\Repository\FieldType as APIFieldType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI;
use eZ\Publish\SPI\FieldType\Value;

class FieldType implements APIFieldType
{
    /**
     * Wrapped FieldType.
     *
     * @var \eZ\Publish\API\Repository\FieldType
     */
    protected $innerFieldType;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType $innerFieldType
     */
    public function __construct(SPI\FieldType\FieldType $innerFieldType)
    {
        $this->innerFieldType = $innerFieldType;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return $this->innerFieldType->getFieldTypeIdentifier();
    }

    /**
     * Returns a schema for the settings expected by the FieldType.
     *
     * Returns an arbitrary value, representing a schema for the settings of
     * the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * @return mixed
     */
    public function getSettingsSchema()
    {
        return $this->innerFieldType->getSettingsSchema();
    }

    /**
     * Returns a schema for the validator configuration expected by the FieldType.
     *
     * Returns an arbitrary value, representing a schema for the validator
     * configuration of the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementor to define and document a schema for
     * the return value and document it. In addition, it is necessary that all
     * consumers of this interface (e.g. Public API, REST API, GUIs, ...)
     * provide plugin mechanisms to hook adapters for the specific FieldType
     * into. These adapters then need to be either shipped with the FieldType
     * or need to be implemented by a third party. If there is no adapter
     * available for a specific FieldType, it will not be usable with the
     * consumer.
     *
     * Best practice:
     *
     * It is considered best practice to return a hash map, which contains
     * rudimentary settings structures, like e.g. for the "ezstring" FieldType
     *
     * <code>
     *  array(
     *      'stringLength' => array(
     *          'minStringLength' => array(
     *              'type'    => 'int',
     *              'default' => 0,
     *          ),
     *          'maxStringLength' => array(
     *              'type'    => 'int'
     *              'default' => null,
     *          )
     *      ),
     *  );
     * </code>
     *
     * @return mixed
     */
    public function getValidatorConfigurationSchema()
    {
        return $this->innerFieldType->getValidatorConfigurationSchema();
    }

    public function getName($value)
    {
        return $this->innerFieldType->getName($value);
    }

    /**
     * Indicates if the field type supports indexing and sort keys for searching.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return $this->innerFieldType->isSearchable();
    }

    /**
     * Indicates if the field definition of this type can appear only once in the same ContentType.
     *
     * @return bool
     */
    public function isSingular()
    {
        return $this->innerFieldType->isSingular();
    }

    /**
     * Indicates if the field definition of this type can be added to a ContentType with Content instances.
     *
     * @return bool
     */
    public function onlyEmptyInstance()
    {
        return $this->innerFieldType->onlyEmptyInstance();
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return $this->innerFieldType->getEmptyValue();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * Usually, only the value returned by {@link getEmptyValue()} is
     * considered empty but that is not always the case.
     *
     * Note: This function assumes that $value is valid so this function can only
     * be used reliably on $values that came from the API, not from the user.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmptyValue($value)
    {
        return $this->innerFieldType->isEmptyValue($value);
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash($hash)
    {
        return $this->innerFieldType->fromHash($hash);
    }

    /**
     * Converts a Value to a hash.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash($value)
    {
        return $this->innerFieldType->toHash($value);
    }

    /**
     * Converts the given $fieldSettings to a simple hash format.
     *
     * @param mixed $fieldSettings
     *
     * @return array|hash|scalar|null
     */
    public function fieldSettingsToHash($fieldSettings)
    {
        return $this->innerFieldType->fieldSettingsToHash($fieldSettings);
    }

    /**
     * Converts the given $fieldSettingsHash to field settings of the type.
     *
     * This is the reverse operation of {@link fieldSettingsToHash()}.
     *
     * @param array|hash|scalar|null $fieldSettingsHash
     *
     * @return mixed
     */
    public function fieldSettingsFromHash($fieldSettingsHash)
    {
        return $this->innerFieldType->fieldSettingsFromHash($fieldSettingsHash);
    }

    /**
     * Converts the given $validatorConfiguration to a simple hash format.
     *
     * @param mixed $validatorConfiguration
     *
     * @return array|hash|scalar|null
     */
    public function validatorConfigurationToHash($validatorConfiguration)
    {
        return $this->innerFieldType->validatorConfigurationToHash($validatorConfiguration);
    }

    /**
     * Converts the given $validatorConfigurationHash to a validator
     * configuration of the type.
     *
     * @param array|hash|scalar|null $validatorConfigurationHash
     *
     * @return mixed
     */
    public function validatorConfigurationFromHash($validatorConfigurationHash)
    {
        return $this->innerFieldType->validatorConfigurationFromHash($validatorConfigurationHash);
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This methods determines if the given $validatorConfiguration is
     * structurally correct and complies to the validator configuration schema as defined in FieldType.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        return $this->innerFieldType->validateValidatorConfiguration($validatorConfiguration);
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This methods determines if the given $fieldSettings are structurally
     * correct and comply to the settings schema as defined in FieldType.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        return $this->innerFieldType->validateFieldSettings($fieldSettings);
    }

    /**
     * Validates a field value based on the validator configuration in the field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\SPI\FieldType\Value $value The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValue(FieldDefinition $fieldDef, Value $value)
    {
        return $this->innerFieldType->validate($fieldDef, $value);
    }
}
