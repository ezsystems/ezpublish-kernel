<?php
/**
 * File contains Content Type Field (content class attribute) class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type;
use ezp\Base\Model,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Content\Type,
    ezp\Persistence\Content\Type\FieldDefinition as FieldDefinitionValue,
    ezp\Content\FieldType\Factory as FieldTypeFactory,
    ezp\Content\FieldType\Validator,
    ezp\Content\FieldType\Value as FieldValue,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    ezp\Persistence\Content\FieldTypeConstraints;

/**
 * Content Type Field (content class attribute) class
 *
 * @property-read mixed $id
 * @property string[] $name
 * @property string[] $description
 * @property string $identifier
 * @property string $fieldGroup
 * @property int $position
 * @property-read string $fieldType
 * @property bool $isTranslatable
 * @property bool $isSearchable
 * @property bool $isRequired
 * @property bool $isInfoCollector
 * @property-read \ezp\Content\FieldTypeConstraints $fieldTypeConstraints
 * @property \ezp\Content\FieldType\Value $defaultValue
 * @property-read \ezp\Content\Type $contentType ContentType object
 * @property-read \ezp\Content\FieldType $type FieldType object
 */
class FieldDefinition extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'name' => true,
        'description' => true,
        'identifier' => true,
        'fieldGroup' => true,
        'position' => true,
        'fieldType' => false,
        'isTranslatable' => true,
        'isSearchable' => true,
        'isRequired' => true,
        'isInfoCollector' => true,
        'fieldTypeConstraints' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'contentType' => false,
        'type' => false,
        'defaultValue' => true
    );

    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * @var \ezp\Content\FieldType
     */
    protected $type;

    /**
     * @var \ezp\Content\FieldType\Value
     */
    protected $defaultValue;

    /**
     * Array holding all the validator objects for this field definition.
     * The array is indexed by validator FQN (Full Qualified Name), i.e. class name with namespace without first slash.
     *
     * @var \ezp\Content\FieldType\Validator[]
     */
    protected $validators;

    /**
     * Constructor, sets up value object, fieldType string and attach $contentType
     *
     * @param \ezp\Content\Type $contentType
     * @param string $fieldType
     */
    public function __construct( Type $contentType, $fieldType )
    {
        $this->contentType = $contentType;
        $this->type = FieldTypeFactory::build( $fieldType );
        $this->properties = new FieldDefinitionValue(
            array(
                'fieldType' => $fieldType,
                'fieldTypeConstraints' => new FieldTypeConstraints
            )
        );
        $this->properties->fieldTypeConstraints->fieldSettings = $this->type->getFieldTypeSettings();
        $this->defaultValue = $this->type->getValue();
        $this->attach( $this->type, 'field/setValue' );
    }

    /**
     * Return content type object
     *
     * @return \ezp\Content\Type
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Return field type object
     *
     * @return \ezp\Content\FieldType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets a validator for this field.
     * Only one kind of validator can exist at a time in a Field Definition.
     * If a similar validator (same Full Qualified Name) already exists, it will be replaced by $validator.
     * To update a validator, get it with {@link \ezp\Content\Type\FieldDefinition::getValidators()}, update its properties
     * and set it again with this method.
     *
     * Examples:
     * <code>
     * // Assume $fieldDefinition is of Integer field type
     *
     * // Adding a new validator
     * $validator = new \ezp\Content\FieldType\Integer\IntegerValueValidator;
     * $validator->minIntegerValue = -6;
     * $validator->maxIntegerValue = 6;
     * $fieldDefinition->setValidator( $validator );
     *
     * // Updating a validator
     * // $allValidators is an array indexed by validator FQN
     * $allValidators = $fieldDefinition->getValidators();
     * $validator = $allValidators["ezp\Content\FieldType\Integer\IntegerValueValidator"];
     * $validator->minIntegerValue = -5;
     * $fieldDefinition->setValidator( $validator );
     * </code>
     *
     * @param \ezp\Content\FieldType\Validator $validator
     * @return void
     */
    public function setValidator( Validator $validator )
    {
        $this->initializeValidators();
        $this->type->fillConstraintsFromValidator( $this->fieldTypeConstraints, $validator );
        $this->validators[] = $validator;
    }

    /**
     * Returns a validator object for this field definition, identified by $validatorName
     *
     * @param string $validatorName Validator's FQN (Full Qualified Name, class name with namespace, without first slash).
     *                              e.g. \ezp\Content\FieldType\Integer\IntegerValueValidator
     * @return \ezp\Content\FieldType\Validator
     * @throws \ezp\Base\Exception\InvalidArgumentValue If no validator is referenced with $validatorName
     */
    public function getValidator( $validatorName )
    {
        $this->initializeValidators();
        if ( !isset( $this->validators[$validatorName] ) )
            throw new InvalidArgumentValue( '$validatorName', $validatorName, get_class( $this ) );

        return $this->validators[$validatorName];
    }

    /**
     * Initializes the validators map with constraints if it does not already exist.
     *
     * @return void
     */
    private function initializeValidators()
    {
        // We'll initialize the map with constraints if it does not already exist.
        if ( !isset( $this->properties->fieldTypeConstraints->validators ) )
        {
            $this->properties->fieldTypeConstraints->validators = array();
            $this->validators = array();
        }
        else
        {
            if ( !isset( $this->validators ) )
                $this->validators = $this->getValidators();
        }
    }

    /**
     * Removes a validator, identified by $validatorName, from the field definition.
     *
     * @param string $validatorName Validator's FQN (Full Qualified Name, class name with namespace, without first slash).
     *                              e.g. \ezp\Content\FieldType\Integer\IntegerValueValidator
     */
    public function removeValidator( $validatorName )
    {
        $this->initializeValidators();
        if ( isset( $this->validators[$validatorName] ) )
        {
            unset(
                $this->validators[$validatorName],
                $this->properties->fieldTypeConstraints->validators[$validatorName]
            );
        }
    }

    /**
     * Returns default value for current field definition
     *
     * @return \ezp\Content\FieldType\Value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets a new default value for current field definition
     *
     * @param \ezp\Content\FieldType\Value $value
     */
    public function setDefaultValue( FieldValue $value )
    {
        $this->defaultValue = $value;
        $this->notify( 'field/setValue', array( 'value' => $value ) );
        $this->properties->defaultValue = $this->type->toFieldValue();
    }

    /**
     * Returns registered validators for current field definition
     *
     * @return \ezp\Content\FieldType\Validator[]
     */
    public function getValidators()
    {
        if ( !isset( $this->validators ) )
        {
            $this->validators = array();
            if ( isset( $this->properties->fieldTypeConstraints->validators ) )
            {
                foreach ( $this->properties->fieldTypeConstraints->validators as $validatorClass => $constraints )
                {
                    $validator = new $validatorClass;
                    $validator->initializeWithConstraints( $constraints );
                    $this->validators[$validatorClass] = $validator;
                }
            }
            else
            {
                $this->properties->fieldTypeConstraints->validators = array();
            }
        }

        return $this->validators;
    }

    /**
     * Sets a field setting, according to field type allowed settings
     *
     * @see \ezp\Content\FieldType::$allowedSettings
     * @param string $settingName
     * @param mixed $value
     */
    public function setFieldSetting( $settingName, $value )
    {
        $this->type->setFieldSetting( $settingName, $value );
    }

    /**
     * Gets a field setting, identified by $settingName
     *
     * @param string $settingName
     * @return mixed
     */
    public function getFieldSetting( $settingName )
    {
        return $this->type->getFieldSetting( $settingName );
    }
}
