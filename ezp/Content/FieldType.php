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
    ezp\Content\FieldType\Validator,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Persistence\Content\FieldTypeConstraints,
    ezp\Base\Observer,
    ezp\Base\Observable,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\InvalidArgumentType;

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
abstract class FieldType implements Observer
{
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
     * Full Qualified Class Name should be registered here.
     * Example:
     * <code>
     * protected $allowedValidators = array(
     *     "ezp\\Content\\FieldType\\BinaryFile\\FileSizeValidator"
     * );
     * </code>
     *
     * @var array
     */
    protected $allowedValidators = array();

    /**
     * Value of field type.
     *
     * @var \ezp\Content\FieldType\Value
     */
    private $value;

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
        return $this->value ?: $this->getDefaultValue();
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \ezp\Content\FieldType\Value
     */
    abstract protected function getDefaultValue();

    /**
     * Method to populate the FieldValue struct for field types
     *
     * @internal
     * @return void
     */
    public final function toFieldValue()
    {
        // @todo Evaluate if creating the sortKey in every case is really needed
        //       Couldn't this be retrieved with a method, which would initialize
        //       that info on request only?
        return new PersistenceFieldValue(
            array(
                "data" => $this->getValue(),
                "sortKey" => $this->getSortInfo(),
                "fieldSettings" => $this->getFieldTypeSettings()
            )
        );
    }

    /**
     * Returns information for PersistenceFieldValue->$sortKey relevant to the field type.
     * Return value is an array where key is the sort type, value is field value to be used for sorting.
     * Sort type can be :
     *  - sort_key_string (sorting will be made with a string algorithm)
     *  - sort_key_int (sorting will be made with an integer algorithm)
     *
     * <code>
     * protected function getSortInfo()
     * {
     *     // Example for a text line type:
     *     return array( 'sort_key_string' => $this->getValue()->text );
     *
     *     // Example for an int:
     *     // return array( 'sort_key_int' => 123 );
     * }
     * </code>
     *
     * @abstract
     * @return array|bool Array with sortInfo, or false if the Type doesn't support sorting
     */
    abstract protected function getSortInfo();

    /**
     * Used by the FieldDefinition to populate the $fieldTypeConstraints->validators field.
     *
     * If validator is not allowed for a given field type, no data from that
     * validator is populated to $constraints.
     *
     * @internal
     * @param \ezp\Persistence\Content\FieldTypeConstraints $fieldTypeConstraints
     * @param \ezp\Content\FieldType\Validator $validator
     * @return void
     */
     public final function fillConstraintsFromValidator( FieldTypeConstraints $fieldTypeConstraints, Validator $validator )
     {
         $validatorClass = get_class( $validator );
         if ( !in_array( $validatorClass, $this->allowedValidators() ) )
             throw new InvalidArgumentType( '$validator', implode( ', ', $this->allowedValidators() ) );

         $fieldTypeConstraints->validators = array(
             $validatorClass => $validator->getValidatorConstraints()
         ) + $fieldTypeConstraints->validators;
     }

     /**
      * Called when subject has been updated
      * Supported events:
      *   - field/setValue Should be triggered when a field has been set a value. Will inject the value in the field type
      *
      * @param \ezp\Base\Observable $subject
      * @param string $event
      * @param array $arguments
      * @throws \ezp\Base\Exception\InvalidArgumentValue
      */
     public function update( Observable $subject, $event = 'update', array $arguments = null )
     {
         switch ( $event )
         {
             case 'field/setValue':
                 if ( $arguments === null || !isset( $arguments['value'] ) )
                     throw new InvalidArgumentValue( 'arguments', $arguments, get_class() );

                 $this->onFieldSetValue( $subject, $arguments['value'] );
                 break;

             case 'content/publish':
                 // @todo Implement
                 break;
         }
     }

     /**
      * This method is called when a "field/setValue" event is triggered by $subject.
      * Override this method if you need to manipulate $value when "field/setValue" event is triggered.
      * By default, it injects $value in the field type, without any manipulation.
      * When overriding this method, the parent must always be called:
      * <code>
      * protected function onFieldSetValue( Observable $subject, Value $value )
      * {
      *     parent::onFieldSetValue( $subject, $value );
      *     // Do something with $value and $subject
      * }
      * </code>
      *
      * @param \ezp\Base\Observable $subject
      * @param \ezp\Content\FieldType\Value $value
      */
     protected function onFieldSetValue( Observable $subject, Value $value )
     {
         $this->setValue( $value );
     }
}
