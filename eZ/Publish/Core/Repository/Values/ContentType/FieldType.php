<?php

/**
 * File containing the eZ\Publish\API\Repository\FieldType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\FieldType as FieldTypeInterface;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldTypeInterface;
use eZ\Publish\SPI\FieldType\Value;

/**
 * This class represents a FieldType available to Public API users.
 *
 * @see \eZ\Publish\API\Repository\FieldType
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class FieldType implements FieldTypeInterface
{
    /**
     * Holds internal FieldType object.
     *
     * @var \eZ\Publish\Core\FieldType\FieldType
     */
    protected $internalFieldType;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     */
    public function __construct(SPIFieldTypeInterface $fieldType)
    {
        $this->internalFieldType = $fieldType;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return $this->internalFieldType->getFieldTypeIdentifier();
    }

    public function getName(Value $value, APIFieldDefinition $fieldDefinition, string $languageCode): string
    {
        return $this->internalFieldType->getName($value, $fieldDefinition, $languageCode);
    }

    /**
     * Returns a schema for the settings expected by the FieldType.
     *
     * Returns an arbitrary value, representing a schema for the settings of
     * the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementer to define and document a schema for
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
        return $this->internalFieldType->getSettingsSchema();
    }

    /**
     * Returns a schema for the validator configuration expected by the FieldType.
     *
     * Returns an arbitrary value, representing a schema for the validator
     * configuration of the FieldType.
     *
     * Explanation: There are no possible generic schemas for defining settings
     * input, which is why no schema for the return value of this method is
     * defined. It is up to the implementer to define and document a schema for
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
        return $this->internalFieldType->getValidatorConfigurationSchema();
    }

    /**
     * Indicates if the field type supports indexing and sort keys for searching.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return $this->internalFieldType->isSearchable();
    }

    /**
     * Indicates if the field definition of this type can appear only once in the same ContentType.
     *
     * @return bool
     */
    public function isSingular()
    {
        return $this->internalFieldType->isSingular();
    }

    /**
     * Indicates if the field definition of this type can be added to a ContentType with Content instances.
     *
     * @return bool
     */
    public function onlyEmptyInstance()
    {
        return $this->internalFieldType->onlyEmptyInstance();
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return $this->internalFieldType->getEmptyValue();
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
        return $this->internalFieldType->isEmptyValue($value);
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
        return $this->internalFieldType->fromHash($hash);
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
        return $this->internalFieldType->toHash($value);
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
        return $this->internalFieldType->fieldSettingsToHash($fieldSettings);
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
        return $this->internalFieldType->fieldSettingsFromHash($fieldSettingsHash);
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
        return $this->internalFieldType->validatorConfigurationToHash($validatorConfiguration);
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
        return $this->internalFieldType->validatorConfigurationFromHash($validatorConfigurationHash);
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
        return $this->internalFieldType->validateValidatorConfiguration($validatorConfiguration);
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
        return $this->internalFieldType->validateFieldSettings($fieldSettings);
    }

    /**
     * Validates a field value based on the validator configuration in the field definition.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDef The field definition of the field
     * @param \eZ\Publish\SPI\FieldType\Value $value The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValue(APIFieldDefinition $fieldDef, Value $value)
    {
        return $this->internalFieldType->validate($fieldDef, $value);
    }
}
