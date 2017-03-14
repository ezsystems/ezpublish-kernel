<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinition class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a field definition.
 *
 * @property-read mixed $fieldSettings calls getFieldSettings()
 * @property-read mixed $validatorConfiguration calls getValidatorConfiguration()
 * @property-read mixed $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content type
 * @property-read string $fieldTypeIdentifier String identifier of the field type
 * @property-read bool $isTranslatable indicates if fields of this definition are translatable
 * @property-read bool $isRequired indicates if this field is required in the content object
 * @property-read bool $isSearchable indicates if the field is indexed for FullText search
 * @property-read bool $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
abstract class FieldDefinition extends ValueObject
{
    /**
     * the unique id of this field definition.
     *
     * @var mixed
     */
    protected $id;

    /**
     * Readable string identifier of a field definition.
     *
     * @var string
     */
    protected $identifier;

    /**
     * This method returns the human readable name of this field in all provided languages
     * of the content type.
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getNames();

    /**
     * This method returns the name of the field in the given language.
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none exists.
     */
    abstract public function getName($languageCode);

    /**
     * This method returns the human readable description of the field.
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    abstract public function getDescriptions();

    /**
     * This method returns the name of the field in the given language.
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none exists.
     */
    abstract public function getDescription($languageCode);

    /**
     * Field group name.
     *
     * @var string
     */
    protected $fieldGroup;

    /**
     * the position of the field definition in the content typr.
     *
     * @var int
     */
    protected $position;

    /**
     * String identifier of the field type.
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * If the field is translatable.
     *
     * @var bool
     */
    protected $isTranslatable;

    /**
     * Is the field required.
     *
     * @var bool
     */
    protected $isRequired;

    /**
     * the flag if this field is used for information collection.
     *
     * @var bool
     */
    protected $isInfoCollector;

    /**
     * This method returns the validator configuration of this field definition supported by the field type.
     *
     * @return mixed
     */
    abstract public function getValidatorConfiguration();

    /**
     * This method returns settings for the field definition supported by the field type.
     *
     * @return mixed
     */
    abstract public function getFieldSettings();

    /**
     * Default value of the field.
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Indicates if the field is indexed for FullText search.
     *
     * Means that if FieldType is indexable and this is true, then content for the Field will be indexed
     * for FullText search. Field either way will (if supported) still be queryable with Field specific Criteria.
     *
     * @var bool
     */
    protected $isSearchable;
}
