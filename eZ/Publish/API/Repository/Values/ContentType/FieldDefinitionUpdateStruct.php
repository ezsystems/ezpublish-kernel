<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to update a field definition
 */
class FieldDefinitionUpdateStruct extends ValueObject
{
    /**
     * If set the identifier of a field definition is changed to this value
     *
     * @var string
     */
    public $identifier;

    /**
     * If set this array of names with languageCode keys replace the complete name collection
     * 
     * @var array an array of string
     */
    public $names;

    /**
     * If set this array of descriptions with languageCode keys replace the complete description collection
     * 
     * @var array an array of string
     */
    public $descriptions;

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
     * If set this array of validators  replace the complete validator collection
     * 
     * @var array an array of {@link eZ\Publish\API\Repository\Values\ContentType\Validator}
     */
    public $validators;

    /**
     * If set this array of fieldSettings replace the complete field settings collection
     * 
     * @var array an array of mixed
     */
    public $fieldSettings;

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
