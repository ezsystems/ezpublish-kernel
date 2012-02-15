<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to create a field definition
 * 
 * @property $names the collection of names with languageCode keys. 
 *           the calls <code>$fdcs->names[$language] = "abc"</code> and <code>$fdcs->setName("abc",$language)</code> are equivalent
 * @property $descriptions the collection of descriptions with languageCode keys. 
 *           the calls <code>$fdcs->descriptions[$language] = "abc"</code> and <code>$fdcs->setDescription("abc",$language)</code> are equivalent
 * @property $valdiators the collection of validators with the validator names as keys
 * @property $fieldSettings the collection of fieldSettings 
 */
abstract class FieldDefinitionCreateStruct extends ValueObject
{
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
     *
     * @param string $name
     * @param string $language
     */
    abstract public function setName( $name, $language );

    /**
     * set a  fie definition description for the given language
     *
     * @param string $description
     * @param string $language
     */
    abstract public function setDescription( $description, $language );

    /**
     * Field group name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * the position of the field definition in the content typr
     * if not set the field is added at the end
     *
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
     *
     * @param Validator $validator
     */
    abstract public function setValidator( Validator $validator );

    /**
     * sets a field settings map supported by the field type
     *
     * @param array $fieldSettings
     */
    abstract public function setFieldSettings( array $fieldSettings );

    /**
     * Default value of the field
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var boolean
     */
    public $isSearchable;
}
