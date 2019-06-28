<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class Type extends FieldType
{
    /** @var \eZ\Publish\Core\FieldType\Generic\ValueSerializerInterface */
    protected $serializer;

    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface */
    protected $validator;

    public function __construct(ValueSerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return (string)$value;
    }

    public function getEmptyValue()
    {
        $class = $this->getValueClass();

        return new $class();
    }

    public function fromHash($hash)
    {
        if ($hash) {
            return $this->serializer->denormalize($hash, $this->getValueClass());
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $this->serializer->normalize($value);
    }

    public function validate(FieldDefinition $fieldDefinition, SPIValue $value): array
    {
        if ($this->isEmptyValue($value)) {
            return [];
        }

        return $this->mapConstraintViolationList(
            $this->validator->validate($value, $this->getFieldValueConstraints($fieldDefinition))
        );
    }

    public function validateFieldSettings($fieldSettings): array
    {
        if (empty($this->settingsSchema) && !empty($fieldSettings)) {
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

        if (empty($fieldSettings)) {
            return [];
        }

        return $this->mapConstraintViolationList(
            $this->validator->validate($fieldSettings, $this->getFieldSettingsConstraints())
        );
    }

    /**
     * @see https://symfony.com/doc/current/validation/raw_values.html
     */
    protected function getFieldSettingsConstraints(): ?Assert\Collection
    {
        return null;
    }

    protected function getFieldValueConstraints(FieldDefinition $fieldDefinition): ?Assert\Collection
    {
        return null;
    }

    protected function mapConstraintViolationList(ConstraintViolationListInterface $constraintViolationList): array
    {
        $errors = [];

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation */
        foreach ($constraintViolationList as $constraintViolation) {
            $errors[] = new ValidationError(
                $constraintViolation->getMessageTemplate(),
                null,
                $constraintViolation->getParameters(),
                $constraintViolation->getPropertyPath()
            );
        }

        return $errors;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = $this->serializer->denormalize(
                $this->serializer->decode($inputValue),
                $this->getValueClass()
            );
        }

        return $inputValue;
    }

    protected function checkValueStructure(BaseValue $value): void
    {
        // Value is self-contained and strong typed
    }

    /**
     * Returns FQN of class representing Field Type Value.
     *
     * @return string
     */
    protected static function getValueClass(): string
    {
        return substr_replace(static::class, 'Value', strrpos(static::class, '\\') + 1);
    }

    protected static function checkValueType($value): void
    {
        $valueClass = self::getValueClass();
        if (!$value instanceof $valueClass) {
            throw new InvalidArgumentType('$value', $valueClass, $value);
        }
    }
}
