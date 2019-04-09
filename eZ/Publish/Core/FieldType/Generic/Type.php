<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class Type extends FieldType
{
    /** @var \Symfony\Component\Serializer\SerializerInterface */
    protected $serializer;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(Serializer $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;

        if (($settingsClass = $this->getSettingsClass()) !== null) {
            $this->settingsSchema = [
                'settings' => [
                    'type' => $settingsClass,
                    'default' => new $settingsClass,
                ]
            ];
        }
    }

    public function getName(SPIValue $value): string
    {
        throw new \RuntimeException('Name generation provided via NameableField set via "ezpublish.fieldType.nameable" service tag');
    }

    public function getEmptyValue()
    {
        $class = $this->getValueClass();

        return new $class();
    }

    public function fromHash($hash)
    {
        if ($hash) {
            return $this->serializer->denormalize($hash, $this->getValueClass(), 'json');
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $this->serializer->normalize($value, 'json');
    }

    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        $validationErrors = [];

        $errors = $this->validator->validate($value);
        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
        foreach ($errors as $error) {
            $validationErrors[] = new ValidationError(
                $error->getMessageTemplate(),
                null,
                $error->getParameters(),
                $error->getPropertyPath()
            );
        }

        return $validationErrors;
    }

    public function getSettingsClass(): ?string
    {
        return null;
    }

    public function validateFieldSettings($fieldSettings)
    {
        if ($this->getSettingsClass() === null) {
            return [
                new ValidationError(
                    "FieldType '%fieldType%' does not accept settings",
                    null,
                    [
                        'fieldType' => $this->getFieldTypeIdentifier(),
                    ],
                    'fieldType'
                ),
            ];
        }

        $validationErrors = [];

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $error */
        foreach ($this->validator->validate($fieldSettings['settings']) as $error) {
            $validationErrors[] = new ValidationError(
                $error->getMessageTemplate(),
                null,
                $error->getParameters(),
                $error->getPropertyPath()
            );
        }

        return $validationErrors;
    }

    protected function getValueClass(): string
    {
        $typeFQN  = get_called_class();
        $valueFQN = substr_replace($typeFQN, 'Value', strrpos($typeFQN, '\\') + 1);

        return $valueFQN;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = $this->serializer->deserialize($inputValue, $this->getValueClass(), 'json');
        }

        return $inputValue;
    }

    protected function checkValueStructure(BaseValue $value)
    {
        // Value is self-contained and strong typed
        return ;
    }
}
