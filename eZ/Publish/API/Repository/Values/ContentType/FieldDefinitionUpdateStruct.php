<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to update a field definition
 *
 * @property $names the collection of names with languageCode keys. 
 *           the calls <code>$fdcs->names[$language] = "abc"</code> and <code>$fdcs->setName("abc",$language)</code> are equivalent
 * @property $descriptions the collection of descriptions with languageCode keys. 
 *           the calls <code>$fdcs->descriptions[$language] = "abc"</code> and <code>$fdcs->setDescription("abc",$language)</code> are equivalent
 * @property $valdiators the collection of validators with the validator names as keys
 * @property $fieldSettings the collection of fieldSettings 
 */
abstract class FieldDefinitionUpdateStruct extends ValueObject
{
    /**
     * If set the identifier of a field definition is changed to this value
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
     * if set the field group is changed to this name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * if set the position of the field in the content type
     *
     * @var int
     */
    public $position;

    /**
     * if set translatable flag is set to this value
     *
     * @var boolean
     */
    public $isTranslatable;

    /**
     * if set the required flag is set to this value
     *
     * @var boolean
     */
    public $isRequired;

    /**
     * if set the information collector flag is set to this value
     *
     * @var boolean
     */
    public $isInfoCollector;

    /**
     * sets a validator which has to be supported by the field type.
     * if a validator existis with the given name the validator parameters are replaced by the given ones.
     * Otherwise the given validator is added.
     *
     * @param Validator $validator
     */
    abstract public function setValidator( Validator $validator );

    /**
     * replaces the field settings map supported by the field type
     *
     * @param array $fieldSettings
     */
    abstract public function setFieldSettings( array $fieldSettings );

    /**
     * If set the default value for this field is changed to the given value
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * If set the the searchable flag is set to this value.
     *
     * @var boolean
     */
    public $isSearchable;
}
