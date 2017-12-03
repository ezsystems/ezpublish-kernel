<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\ContentType\FieldDefinition class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\SPI\Repository\Values\MultiLanguageName;
use eZ\Publish\SPI\Repository\Values\MultiLanguageDescription;

/**
 * This class represents a field definition.
 *
 * @property-read mixed $fieldSettings calls getFieldSettings()
 * @property-read mixed $validatorConfiguration calls getValidatorConfiguration()
 * @property-read mixed $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content type
 * @property-read string $typeIdentifier String identifier of the field type
 * @property-read bool $isTranslatable indicates if fields of this definition are translatable
 * @property-read bool $isRequired indicates if this field is required in the content object
 * @property-read bool $isSearchable indicates if the field is searchable
 * @property-read bool $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
abstract class FieldDefinition extends ValueObject implements MultiLanguageName, MultiLanguageDescription
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
    protected $typeIdentifier;

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
     * Indicates if th the content is searchable by this attribute.
     *
     * @var bool
     */
    protected $isSearchable;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $properties = [])
    {
        if (array_key_exists('fieldTypeIdentifier', $properties)) {
            $this->triggerDeprecatedPropertyWarning('fieldTypeIdentifier', 'typeIdentifier');

            $properties['typeIdentifier'] = $properties['fieldTypeIdentifier'];

            // avoid setting non-existent property by the parent constructor
            unset($properties['fieldTypeIdentifier']);
        }

        parent::__construct($properties);
    }

    /**
     * BC accessor for fieldTypeIdentifier used by Twig.
     *
     * @see \twig_get_attribute()
     *
     * @deprecated Use FieldDefinition::typeIdentifier property instead
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        $this->triggerDeprecatedPropertyWarning('fieldTypeIdentifier', 'typeIdentifier');

        return $this->typeIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($property)
    {
        if ('fieldTypeIdentifier' === $property) {
            $this->triggerDeprecatedPropertyWarning('fieldTypeIdentifier', 'typeIdentifier');

            return $this->typeIdentifier;
        }

        return parent::__get($property);
    }
}
