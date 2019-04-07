<?php

/**
 * File containing the FieldType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\FieldType as FieldTypeInterface;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Base class for field types, the most basic storage unit of data inside eZ Publish.
 *
 * All other field types extend FieldType providing the specific functionality
 * desired in each case.
 *
 * The capabilities supported by each individual field type is decided by which
 * interfaces the field type implements support for. These individual
 * capabilities can also be checked via the supports*() methods.
 *
 * Field types are the base building blocks of Content Types, and serve as
 * data containers for Content objects. Therefore, while field types can be used
 * independently, they are designed to be used as a part of a Content object.
 *
 * Field types are primed and pre-configured with the Field Definitions found in
 * Content Types.
 */
abstract class FieldType implements FieldTypeInterface
{
    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var mixed
     */
    protected $settingsSchema = array();

    /**
     * The validator configuration schema.
     *
     * This is a base implementation, containing an empty array() that indicates
     * that no validators are supported. Overwrite in derived types, if
     * validation is supported.
     *
     * @see getValidatorConfigurationSchema()
     *
     * @var mixed
     */
    protected $validatorConfigurationSchema = array();

    /**
     * String transformation processor, used to normalize sort string as needed.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     */
    public function setTransformationProcessor(TransformationProcessor $transformationProcessor)
    {
        $this->transformationProcessor = $transformationProcessor;
    }

    /**
     * Returns a schema for the settings expected by the FieldType.
     *
     * This implementation returns an array.
     * where the key is the setting name, and the value is the default value for given
     * setting and set to null if no particular default should be set.
     *
     * @return mixed
     */
    public function getSettingsSchema()
    {
        return $this->settingsSchema;
    }

    /**
     * Returns a schema for the validator configuration expected by the FieldType.
     *
     * @see FieldTypeInterface::getValidatorConfigurationSchema()
     *
     * This implementation returns a three dimensional map containing for each validator configuration
     * referenced by identifier a map of supported parameters which are defined by a type and a default value
     * (see example).
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
        return $this->validatorConfigurationSchema;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * This is a base implementation, returning an empty array() that indicates
     * that no validation errors occurred. Overwrite in derived types, if
     * validation is supported.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $value The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        return array();
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $validatorConfiguration is complete, for this purpose method
     * {@link self::applyDefaultValidatorConfiguration()} is provided.
     *
     * This is a base implementation, returning a validation error for each
     * specified validator, since by default no validators are supported.
     * Overwrite in derived types, if validation is supported.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = array();

        foreach ((array)$validatorConfiguration as $validatorIdentifier => $constraints) {
            $validationErrors[] = new ValidationError(
                "Validator '%validator%' is unknown",
                null,
                array(
                    'validator' => $validatorIdentifier,
                ),
                "[$validatorIdentifier]"
            );
        }

        return $validationErrors;
    }

    /**
     * Applies the default values to the given $validatorConfiguration of a FieldDefinitionCreateStruct.
     *
     * This is a base implementation, expecting best practice validator configuration format used by
     * field types in standard eZ publish installation. Overwrite in derived types if needed.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param mixed $validatorConfiguration
     */
    public function applyDefaultValidatorConfiguration(&$validatorConfiguration)
    {
        if ($validatorConfiguration !== null && !is_array($validatorConfiguration)) {
            throw new InvalidArgumentType('$validatorConfiguration', 'array|null', $validatorConfiguration);
        }

        foreach ($this->getValidatorConfigurationSchema() as $validatorName => $configurationSchema) {
            // Set configuration of specific validator to empty array if it is not already provided
            if (!isset($validatorConfiguration[$validatorName])) {
                $validatorConfiguration[$validatorName] = array();
            }

            foreach ($configurationSchema as $settingName => $settingConfiguration) {
                // Check that a default entry exists in the configuration schema for the validator but that no value has been provided
                if (!isset($validatorConfiguration[$validatorName][$settingName]) && array_key_exists('default', $settingConfiguration)) {
                    $validatorConfiguration[$validatorName][$settingName] = $settingConfiguration['default'];
                }
            }
        }
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $fieldSettings are complete, for this purpose method
     * {@link self::applyDefaultSettings()} is provided.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        if (!empty($fieldSettings)) {
            return array(
                new ValidationError(
                    "FieldType '%fieldType%' does not accept settings",
                    null,
                    array(
                        'fieldType' => $this->getFieldTypeIdentifier(),
                    ),
                    'fieldType'
                ),
            );
        }

        return array();
    }

    /**
     * Applies the default values to the fieldSettings of a FieldDefinitionCreateStruct.
     *
     * This is a base implementation, expecting best practice field settings format used by
     * field types in standard eZ publish installation. Overwrite in derived types if needed.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param mixed $fieldSettings
     */
    public function applyDefaultSettings(&$fieldSettings)
    {
        if ($fieldSettings !== null && !is_array($fieldSettings)) {
            throw new InvalidArgumentType('$fieldSettings', 'array|null', $fieldSettings);
        }

        foreach ($this->getSettingsSchema() as $settingName => $settingConfiguration) {
            // Checking that a default entry exists in the settingsSchema but that no value has been provided
            if (!array_key_exists($settingName, (array)$fieldSettings) && array_key_exists('default', $settingConfiguration)) {
                $fieldSettings[$settingName] = $settingConfiguration['default'];
            }
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * Return value is mixed. It should be something which is sensible for
     * sorting.
     *
     * It is up to the persistence implementation to handle those values.
     * Common string and integer values are safe.
     *
     * For the legacy storage it is up to the field converters to set this
     * value in either sort_key_string or sort_key_int.
     *
     * In case of multi value, values should be string and separated by "-" or ",".
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return mixed
     */
    protected function getSortInfo(Value $value)
    {
        return null;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        // @todo Evaluate if creating the sortKey in every case is really needed
        //       Couldn't this be retrieved with a method, which would initialize
        //       that info on request only?
        return new PersistenceValue(
            array(
                'data' => $this->toHash($value),
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \eZ\Publish\Core\FieldType\Value
     */
    public function fromPersistenceValue(PersistenceValue $fieldValue)
    {
        return $this->fromHash($fieldValue->data);
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * Indicates if the field definition of this type can appear only once in the same ContentType.
     *
     * @return bool
     */
    public function isSingular()
    {
        return false;
    }

    /**
     * Indicates if the field definition of this type can be added to a ContentType with Content instances.
     *
     * @return bool
     */
    public function onlyEmptyInstance()
    {
        return false;
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * Default implementation, which performs a "==" check with the value
     * returned by {@link getEmptyValue()}. Overwrite in the specific field
     * type, if necessary.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        return $value === null || $value == $this->getEmptyValue();
    }

    /**
     * Potentially builds and checks the type and structure of the $inputValue.
     *
     * This method first inspects $inputValue and convert it into a dedicated
     * value object.
     *
     * After that, the value is checked for structural validity.
     * Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * Note that this method must also cope with the empty value for the field
     * type as e.g. returned by {@link getEmptyValue()}.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Value The potentially converted and structurally plausible value.
     */
    final public function acceptValue($inputValue)
    {
        if ($inputValue === null) {
            return $this->getEmptyValue();
        }

        $value = $this->createValueFromInput($inputValue);

        static::checkValueType($value);

        if ($this->isEmptyValue($value)) {
            return $this->getEmptyValue();
        }

        $this->checkValueStructure($value);

        return $value;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * If given $inputValue could not be converted or is already an instance of dedicate value object,
     * the method should simply return it.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     * Example implementation:
     * <code>
     *  protected function createValueFromInput( $inputValue )
     *  {
     *      if ( is_array( $inputValue ) )
     *      {
     *          $inputValue = \eZ\Publish\Core\FieldType\CookieJar\Value( $inputValue );
     *      }
     *
     *      return $inputValue;
     *  }
     * </code>
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted input value.
     */
    abstract protected function createValueFromInput($inputValue);

    /**
     * Throws an exception if the given $value is not an instance of the supported value subtype.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     * Default implementation expects the value class to reside in the same namespace as its
     * FieldType class and is named "Value".
     *
     * Example implementation:
     * <code>
     *  static protected function checkValueType( $value )
     *  {
     *      if ( !$inputValue instanceof \eZ\Publish\Core\FieldType\CookieJar\Value ) )
     *      {
     *          throw new InvalidArgumentException( "Given value type is not supported." );
     *      }
     *  }
     * </code>
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the parameter is not an instance of the supported value subtype.
     *
     * @param mixed $value A value returned by {@see createValueFromInput()}.
     */
    protected static function checkValueType($value)
    {
        $fieldTypeFQN = get_called_class();
        $valueFQN = substr_replace($fieldTypeFQN, 'Value', strrpos($fieldTypeFQN, '\\') + 1);

        if (!$value instanceof $valueFQN) {
            throw new InvalidArgumentType('$value', $valueFQN, $value);
        }
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     * Example implementation:
     * <code>
     *  protected function checkValueStructure( Value $value )
     *  {
     *      if ( !is_array( $value->cookies ) )
     *      {
     *          throw new InvalidArgumentException( "An array of assorted cookies was expected." );
     *      }
     *  }
     * </code>
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     */
    abstract protected function checkValueStructure(Value $value);

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
        return $fieldSettings;
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
        return $fieldSettingsHash;
    }

    /**
     * Converts the given $validatorConfiguration to a simple hash format.
     *
     * Default implementation, which just returns the given
     * $validatorConfiguration, which is by convention an array for all
     * internal field types. Overwrite this method, if necessary.
     *
     * @param mixed $validatorConfiguration
     *
     * @return array|hash|scalar|null
     */
    public function validatorConfigurationToHash($validatorConfiguration)
    {
        return $validatorConfiguration;
    }

    /**
     * Converts the given $validatorConfigurationHash to a validator
     * configuration of the type.
     *
     * Default implementation, which just returns the given
     * $validatorConfigurationHash, since the validator configuration is by
     * convention an array for all internal field types. Overwrite this method,
     * if necessary.
     *
     * @param array|hash|scalar|null $validatorConfigurationHash
     *
     * @return mixed
     */
    public function validatorConfigurationFromHash($validatorConfigurationHash)
    {
        return $validatorConfigurationHash;
    }

    /**
     * Returns relation data extracted from value.
     *
     * Not intended for \eZ\Publish\API\Repository\Values\Content\Relation::COMMON type relations,
     * there is an API for handling those.
     *
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue
     *
     * @return array Hash with relation type as key and array of destination content ids as value.
     *
     * Example:
     * <code>
     *  array(
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK => array(
     *          "contentIds" => array( 12, 13, 14 ),
     *          "locationIds" => array( 24 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED => array(
     *          "contentIds" => array( 12 ),
     *          "locationIds" => array( 24, 45 )
     *      ),
     *      \eZ\Publish\API\Repository\Values\Content\Relation::FIELD => array( 12 )
     *  )
     * </code>
     */
    public function getRelations(SPIValue $fieldValue)
    {
        return array();
    }
}
