<?php
namespace ezp\PublicAPI\Values\ContentType;

use ezp\PublicAPI\Values\ValueObject;

/**
 * this class is used to update a field definition
 *
 * @property-write array $names $names[$language] calls setName($language)
 * @property-write string $name calls setName() for setting a namein the initial language
 * @property-write array $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write string $description calls setDescription() for setting a description in an initial language
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
    public abstract function setName( $name, $language = null );

    /**
     * set a  fie definition description for the given language
     *
     * @param string $description
     * @param string $language
     */
    public abstract function setDescription( $description, $language = null );

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
    public abstract function setValidator( $validator );

    /**
     * replaces the field settings map supported by the field type
     *
     * @param array $fieldSettings
     */
    public abstract function setFieldSettings( array $fieldSettings );

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
