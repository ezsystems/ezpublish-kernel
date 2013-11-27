<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinition class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\MultiLanguageValueBase;

/**
 * This class represents a field definition
 *
 * @property-read mixed $fieldSettings calls getFieldSettings()
 * @property-read mixed $validatorConfiguration calls getValidatorConfiguration()
 * @property-read mixed $id the id of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read mixed $fieldGroupId the id of the field group
 * @property-read int $position the position of the field definition in the content type
 * @property-read string $fieldTypeIdentifier String identifier of the field type
 * @property-read boolean $isTranslatable indicates if fields of this definition are translatable
 * @property-read boolean $isRequired indicates if this field is required in the content object
 * @property-read boolean $isSearchable indicates if the field is searchable
 * @property-read boolean $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 * @property-read array $defaultValues the multi language default values of the field
 */
abstract class FieldDefinition extends MultiLanguageValueBase
{
    /**
     * the unique id of this field definition
     *
     * @var mixed
     */
    protected $id;

    /**
     * @deprecated
     *
     * Field group identifier.
     *
     * @var string
     */
    protected $fieldGroup;

    /**
     * the field group id
     *
     * @var mixed
     */
    protected $fieldGroupId;

    /**
     * the position of the field definition in the content typr
     *
     * @var int
     */
    protected $position;

    /**
     * String identifier of the field type
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * If the field is translatable
     *
     * @var boolean
     */
    protected $isTranslatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    protected $isRequired;

    /**
     * the flag if this field is used for information collection
     *
     * @var boolean
     */
    protected $isInfoCollector;

    /**
     * This method returns the validator configuration of this field definition supported by the field type
     *
     * @return mixed
     */
    abstract public function getValidatorConfiguration();

    /**
     * This method returns settings for the field definition supported by the field type
     *
     * @return mixed
     */
    abstract public function getFieldSettings();

    /**
     * @deprecated
     *
     * Default value of the field
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Multilingual default values of the field with language Code keys
     *
     * for non translatable fields the only key must be null
     *
     * @var array
     */
    protected $defaultValues;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var boolean
     */
    protected $isSearchable;
}
