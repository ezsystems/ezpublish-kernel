<?php
/**
 * File containing the FieldType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Content\FieldType\FieldSettings,
    ezp\Content\FieldType\Value,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Content\Type\FieldDefinition,
    ezp\Base\Configuration,
    ezp\Base\Exception\MissingClass;

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
 * A field type are the base building blocks of Content Types, and serve as
 * data containers for Content objects. Therefore, while field types can be used
 * independently, they are designed to be used as a part of a Content object.
 *
 * Field types are primed and pre-configured with the Field Definitions found in
 * Content Types.
 *
 * @todo Merge and optimize concepts for settings, validator data and field type properties.
 */
abstract class FieldType
{
    /**
     * @var \ezp\Content\FieldType\Value Fallback default value of field type when no such default
     *                                   value is provided in the field definition in content types.
     */
    protected $defaultValue;

    /**
     * @var FieldSettings Custom properties which are specific to the field
     *                      type. Typically these properties are used to
     *                      configure behaviour of field types and normally set
     *                      in the FieldDefinition on ContentTypes
     */
    protected $fieldSettings;

    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var array
     */
    protected $allowedSettings = array();

    /**
     * Validators which are supported for this field type.
     *
     * Key is the name of supported validators, value is an array of stored settings.
     *
     * @var array
     */
    protected $allowedValidators = array();

    /**
     * Value of field type.
     *
     * @var \ezp\Content\FieldType\Value
     */
    protected $value;

    /**
     * Constructs field type object, initializing internal data structures.
     */
    public function __construct()
    {
        $this->fieldSettings = new FieldSettings( $this->allowedSettings );
    }

    /**
     * Set $setting on field type.
     *
     * Allowed options are in {@link $allowedSettings}
     *
     * @param string $setting
     * @param mixed $value
     * @return void
     */
    public function setFieldSetting( $setting, $value )
    {
        $this->fieldSettings[$setting] = $value;
    }

    /**
     * Set all settings on field type.
     *
     * Useful to initialize field type from a field definition.
     *
     * @param array $values
     * @return void
     */
    public function initializeSettings( array $values )
    {
        $this->fieldSettings->exchangeArray( $values );
    }

    /**
     * Return a copy of the array of fieldSettings.
     *
     * @return array
     */
    public function getFieldTypeSettings()
    {
        return $this->fieldSettings->getArrayCopy();
    }

    /**
     * Keys of settings which are available on this fieldtype.
     * @return array
     */
    public function allowedSettings()
    {
        return array_keys( $this->allowedSettings );
    }

    /**
     * Return an array of allowed validators to operate on this field type.
     *
     * @return array
     */
    public function allowedValidators()
    {
        return $this->allowedValidators;
    }

    /**
     * Checks if $inputValue can be parsed.
     * If the $inputValue actually can be parsed, the value is returned.
     * Otherwise, an \ezp\Base\Exception\BadFieldTypeInput exception is thrown
     *
     * @abstract
     * @throws \ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param \ezp\Content\FieldType\Value $inputValue
     * @return \ezp\Content\FieldType\Value
     */
    abstract protected function canParseValue( Value $inputValue );

    /**
     * Injects the value of a field in the field type.
     *
     * @param \ezp\Content\FieldType\Value $inputValue
     * @return void
     */
    public function setValue( Value $inputValue )
    {
        $this->value = $this->canParseValue( $inputValue );
    }

    /**
     * Returns the value of associated field.
     *
     * If no value has yet been set, the default value of that field type is
     * returned.
     *
     * @return \ezp\Content\FieldType\Value|null
     */
    public function getValue()
    {
        if ( $this->value === null )
        {
            return $this->defaultValue;
        }
        return $this->value;
    }

    /**
     * Method to populate the FieldValue struct for field types
     *
     * @internal
     * @param \ezp\Persistence\Content\FieldValue $valueStruct The value struct which the field type data is packaged in for consumption by the storage engine.
     * @return void
     */
    public function setFieldValue( PersistenceFieldValue $valueStruct )
    {
        $valueStruct->data = $this->getValueData();
        $valueStruct->sortKey = $this->getSortInfo();
    }

    /**
     * Returns information for PersistenceFieldValue->$sortKey relevant to the field type.
     * Return value is an array where key is the sort type, value is field value to be used for sorting.
     * Sort type can be :
     *  - sort_key_string (sorting will be made with a string algorythm)
     *  - sort_key_int (sorting will be made with an integer algorythm)
     *
     * <code>
     * protected function getSortInfo()
     * {
     *     // Example for a text line type:
     *     return array( 'sort_key_string' => $this->value->text );
     *
     *     // Example for an int:
     *     // return array( 'sort_key_int' => 123 );
     * }
     * </code>
     *
     * @abstract
     * @return array
     */
    abstract protected function getSortInfo();

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @abstract
     * @return array
     */
    abstract protected function getValueData();

    /**
     * Used by the FieldDefinition to populate the fieldConstraints field.
     *
     * If validator is not allowed for a given field type, no data from that
     * validator is populated to $constraints.
     *
     * @todo Consider separating out the fieldTypeConstraints into a sepsrate object, so that it could be passed, and not the whole FieldDefinition object.
     *
     * @abstract
     * @internal
     * @param FieldDefinition $fieldDefinition
     * @param \ezp\Content\FieldType\Validator $validator
     * @return void
     */
     public function fillConstraintsFromValidator( FieldDefinition $fieldDefinition, $validator )
     {
         if ( in_array( $validator->name(), $this->allowedValidators() ) )
         {
             $fieldDefinition->fieldTypeConstraints = array_merge( $fieldDefinition->fieldTypeConstraints, $validator->getValidatorConstraints() );
         }
     }
}
