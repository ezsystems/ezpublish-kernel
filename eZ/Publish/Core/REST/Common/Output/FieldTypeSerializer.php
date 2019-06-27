<?php

/**
 * File containing the Generator base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output;

use eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Serializes FieldType related data for REST output.
 */
class FieldTypeSerializer
{
    /**
     * FieldTypeService.
     *
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /** @var \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry */
    protected $fieldTypeProcessorRegistry;

    /**
     * @param \eZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     * @param \eZ\Publish\Core\REST\Common\FieldTypeProcessorRegistry $fieldTypeProcessorRegistry
     */
    public function __construct(FieldTypeService $fieldTypeService, FieldTypeProcessorRegistry $fieldTypeProcessorRegistry)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->fieldTypeProcessorRegistry = $fieldTypeProcessorRegistry;
    }

    /**
     * Serializes the field value of $field through $generator.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function serializeFieldValue(Generator $generator, ContentType $contentType, Field $field)
    {
        $this->serializeValue(
            'fieldValue',
            $generator,
            $this->fieldTypeService->getFieldType(
                $contentType->getFieldDefinition($field->fieldDefIdentifier)->fieldTypeIdentifier
            ),
            $field->value
        );
    }

    /**
     * Serializes the $defaultValue for $fieldDefIdentifier through $generator.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param string $fieldTypeIdentifier
     * @param mixed $defaultValue
     */
    public function serializeFieldDefaultValue(Generator $generator, $fieldTypeIdentifier, $defaultValue)
    {
        $this->serializeValue(
            'defaultValue',
            $generator,
            $this->getFieldType($fieldTypeIdentifier),
            $defaultValue
        );
    }

    /**
     * Serializes $settings as fieldSettings for $fieldDefinition using
     * $generator.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param string $fieldTypeIdentifier
     * @param mixed $settings
     */
    public function serializeFieldSettings(Generator $generator, $fieldTypeIdentifier, $settings)
    {
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
        $hash = $fieldType->fieldSettingsToHash($settings);

        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessFieldSettingsHash($hash);
        }

        $this->serializeHash('fieldSettings', $generator, $hash);
    }

    /**
     * Serializes $validatorConfiguration for $fieldDefinition using $generator.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param string $fieldTypeIdentifier
     * @param mixed $validatorConfiguration
     */
    public function serializeValidatorConfiguration(Generator $generator, $fieldTypeIdentifier, $validatorConfiguration)
    {
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);
        $hash = $fieldType->validatorConfigurationToHash($validatorConfiguration);

        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessValidatorConfigurationHash($hash);
        }

        $this->serializeHash('validatorConfiguration', $generator, $hash);
    }

    /**
     * Returns the field type with $fieldTypeIdentifier.
     *
     * @param string $fieldTypeIdentifier
     *
     * @return \eZ\Publish\API\Repository\FieldType
     */
    protected function getFieldType($fieldTypeIdentifier)
    {
        return $this->fieldTypeService->getFieldType(
            $fieldTypeIdentifier
        );
    }

    /**
     * Serializes the given $value for $fieldType with $generator into
     * $elementName.
     *
     * @param string $elementName
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\API\Repository\FieldType $fieldType
     * @param mixed $value
     */
    protected function serializeValue($elementName, Generator $generator, FieldType $fieldType, $value)
    {
        $hash = $fieldType->toHash($value);

        $fieldTypeIdentifier = $fieldType->getFieldTypeIdentifier();
        if ($this->fieldTypeProcessorRegistry->hasProcessor($fieldTypeIdentifier)) {
            $processor = $this->fieldTypeProcessorRegistry->getProcessor($fieldTypeIdentifier);
            $hash = $processor->postProcessValueHash($hash);
        }

        $this->serializeHash($elementName, $generator, $hash);
    }

    /**
     * Serializes the given $hash with $generator into $elementName.
     *
     * @param string $elementName
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $hash
     */
    protected function serializeHash($elementName, Generator $generator, $hash)
    {
        $generator->generateFieldTypeHash($elementName, $hash);
    }
}
