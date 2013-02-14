<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * this class is used to create a field definition
 */
class FieldDefinitionCreateStruct extends ValueObject
{
    /**
     * String identifier of the field type
     *
     * @required
     *
     * @var string
     */
    public $fieldTypeIdentifier;

    /**
     * Readable string identifier of a field definition
     *
     * Needs to be unique within the context of the Content Type this is added to.
     *
     * @required
     *
     * @var string
     */
    public $identifier;

    /**
     * An array of names with languageCode keys
     *
     * @var array an array of string
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @var array an array of string
     */
    public $descriptions;

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
     * Indicates if this attribute is used for information collection
     *
     * @var boolean
     */
    public $isInfoCollector;

    /**
     * The validator configuration supported by the field type
     *
     * @var mixed
     */
    public $validatorConfiguration;

    /**
     * The settings supported by the field type
     *
     * @var mixed
     */
    public $fieldSettings;

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
