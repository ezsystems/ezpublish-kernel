<?php

/**
 * File containing the Author class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Author;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * Author field type.
 *
 * Field type representing a list of authors, consisting of author name, and
 * author email.
 */
class Type extends FieldType
{
    /**
     * Flag which stands for setting Author FieldType empty by default.
     * It is used in a Content Type edit view.
     */
    const DEFAULT_VALUE_EMPTY = 0;

    /**
     * Flag which stands for prefilling Author FieldType with current user by default.
     * It is used in a Content Type edit view.
     */
    const DEFAULT_CURRENT_USER = 1;

    protected $settingsSchema = [
        'defaultAuthor' => [
            'type' => 'choice',
            'default' => self::DEFAULT_VALUE_EMPTY,
        ],
    ];

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezauthor';
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param array|\eZ\Publish\Core\FieldType\Author\Value $inputValue
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!$value->authors instanceof AuthorCollection) {
            throw new InvalidArgumentType(
                '$value->authors',
                AuthorCollection::class,
                $value->authors
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortInfo(BaseValue $value)
    {
        if (empty($value->authors)) {
            return false;
        }

        $authors = [];
        foreach ($value->authors as $author) {
            $authors[] = $this->transformationProcessor->transformByGroup($author->name, 'lowercase');
        }

        sort($authors);

        return implode(',', $authors);
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\FieldType\Author\Value $value
     */
    public function fromHash($hash)
    {
        return new Value(
            array_map(
                function ($author) {
                    return new Author($author);
                },
                $hash
            )
        );
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \eZ\Publish\Core\FieldType\Author\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        return array_map(
            function ($author) {
                return (array)$author;
            },
            $value->authors->getArrayCopy()
        );
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param array $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = [];

        foreach ($fieldSettings as $name => $value) {
            $settingNameError = $this->validateSettingName($name);

            if ($settingNameError instanceof ValidationError) {
                $validationErrors[] = $settingNameError;
            }

            switch ($name) {
                case 'defaultAuthor':
                    $settingValueError = $this->validateDefaultAuthorSetting($name, $value);
                    if ($settingValueError instanceof ValidationError) {
                        $validationErrors[] = $settingValueError;
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Validates the fieldSetting name.
     *
     * @param string $name
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError|null
     */
    private function validateSettingName($name)
    {
        if (!isset($this->settingsSchema[$name])) {
            return new ValidationError(
                "Setting '%setting%' is unknown",
                null,
                [
                    '%setting%' => $name,
                ],
                "[$name]"
            );
        }

        return null;
    }

    /**
     * Validates if the defaultAuthor setting has one of the defined values.
     *
     * @param string $name
     * @param string $value
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError|null
     */
    private function validateDefaultAuthorSetting($name, $value)
    {
        $definedValues = [
            self::DEFAULT_VALUE_EMPTY,
            self::DEFAULT_CURRENT_USER,
        ];

        if (!in_array($value, $definedValues, true)) {
            return new ValidationError(
                "Setting '%setting%' has unknown default value",
                null,
                [
                    '%setting%' => $name,
                ],
                "[$name]"
            );
        }

        return null;
    }
}
