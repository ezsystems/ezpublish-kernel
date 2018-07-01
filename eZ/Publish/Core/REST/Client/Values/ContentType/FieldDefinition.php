<?php

/**
 * File containing the FieldDefinition class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;

/**
 * This class represents a field definition.
 *
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read mixed $validators calls getValidatorConfiguration()
 * @property-read int $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content typr
 * @property-read string $fieldType String identifier of the field type
 * @property-read boolean $isTranslatable indicates if fields of this definition are translatable
 * @property-read boolean $isRequired indicates if this field is required in the content object
 * @property-read boolean $isSearchable indicates if the field is searchable
 * @property-read boolean $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
class FieldDefinition extends APIFieldDefinition
{
    /**
     * Contains the human readable name of this field in all provided languages
     * of the content type.
     *
     * @var string[]
     */
    protected $names;

    /**
     * Contains the human readable description of the field.
     *
     * @var string[]
     */
    protected $descriptions;

    /**
     * Contains the validators of this field definition supported by the field type#.
     *
     * @var \eZ\Publish\Core\FieldType\Validator[]
     */
    protected $validators;

    /**
     * Contains settings for the field definition supported by the field type.
     *
     * @var array
     */
    protected $fieldSettings;

    public function __construct(array $data = array())
    {
        foreach ($data as $propertyName => $propertyValue) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * {@inheritdoc}
     */
    public function getName($languageCode = null)
    {
        return $this->names[$languageCode];
    }

    /**
     * {@inheritdoc}
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($languageCode = null)
    {
        return $this->descriptions[$languageCode];
    }

    /**
     * This method returns the validators of this field definition supported by the field type.
     *
     * @return \eZ\Publish\Core\FieldType\Validator[]
     */
    public function getValidatorConfiguration()
    {
        return $this->validators;
    }

    /**
     * This method returns settings for the field definition supported by the field type.
     *
     * @return array
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }
}
