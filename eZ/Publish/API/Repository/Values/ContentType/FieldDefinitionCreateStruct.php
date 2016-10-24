<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to create a field definition.
 */
class FieldDefinitionCreateStruct extends ValueObject
{
    /**
     * String identifier of the field type.
     *
     * Required.
     *
     * @var string
     */
    public $fieldTypeIdentifier;

    /**
     * Readable string identifier of a field definition.
     *
     * Needs to be unique within the context of the Content Type this is added to.
     *
     * Required.
     *
     * @var string
     */
    public $identifier;

    /**
     * An array of names with languageCode keys.
     *
     * @var array an array of string
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys.
     *
     * @var array an array of string
     */
    public $descriptions;

    /**
     * Field group name.
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * The position of the field definition in the content type
     * if not set the field is added at the end.
     *
     * @var int
     */
    public $position;

    /**
     * Indicates if the field is translatable.
     *
     * @var bool
     */
    public $isTranslatable;

    /**
     * Indicates if the field is required.
     *
     * @var bool
     */
    public $isRequired;

    /**
     * Indicates if this attribute is used for information collection.
     *
     * @var bool
     */
    public $isInfoCollector;

    /**
     * The validator configuration supported by the field type.
     *
     * @var mixed
     */
    public $validatorConfiguration;

    /**
     * The settings supported by the field type.
     *
     * @var mixed
     */
    public $fieldSettings;

    /**
     * Default value of the field.
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * Indicates if the field is indexed for FullText search.
     *
     * Means that if FieldType is indexable and this is true, then content for the Field will be indexed
     * for FullText search. Field either way will (if supported) still be queryable with Field specific Criteria.
     *
     * @var bool
     */
    public $isSearchable;
}
