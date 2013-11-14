<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\MultiLanguageCreateStructBase;

/**
 * this class is used to create a field definition
 */
class FieldDefinitionCreateStruct extends MultiLanguageCreateStructBase
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
     * Field group name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * The position of the field definition in the content type
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
