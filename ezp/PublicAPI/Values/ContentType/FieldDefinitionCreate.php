<?php
namespace ezp\PublicAPI\Values\ContentType;

use ezp\PublicAPI\Values\ValueObject;

/**
 * this class is used to create a field definition
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
 */
abstract class FieldDefinitionCreate extends ValueObject {
	
    /**
     * String identifier of the field type
     *
     * @var string
     */
    public $fieldTypeIdentifier;
	
	
	/**
     * Readable string identifier of a field definition
     *
     * @var string
    */
    public $identifier;

    /**
     * set a field definition name for the given language
     * @param string $name
     * @param string $language 
     */
    public abstract function setName($name, $language);
        
    /**
     * set a  fie definition description for the given language
     * @param string $description
     * @param string $language 
     */
    public abstract function setDescription($description, $language);
    
    
    
    /**
     * Field group name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * the position of the field definition in the content typr
     * if not set the field is added at the end
     * @var int
     */
    public $position;


    /**
     * Indicates if the field is translatable
     *
     * @var boolean
     */
    public $isTranslatable;

    /**
     * Indicates if the field is required
     *
     * @var boolean
     */
    public $isRequired;

    /**
     * indicates if this attribute is used for information collection
     *
     * @var boolean
     */
    public $isInfoCollector;

    /**
     * sets a validator which has to be supported by the field type
     * @param Validator $validator
     */
    public abstract function setValidator(/*Validator*/ $validator);
    
     /**
     * 
     * sets a field settings map supported by the field type
     * @param array $fieldSettings
     */
     public abstract function setFieldSettings(array $fieldSettings);

    /**
     * Default value of the field
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var bool
     */
    public $isSearchable;
	
	
}