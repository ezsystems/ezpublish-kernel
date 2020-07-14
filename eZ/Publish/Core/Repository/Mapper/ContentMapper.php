<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Mapper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\SPI\FieldType\Value;
use eZ\Publish\SPI\Persistence\Content\Language\Handler;

/**
 * @internal Meant for internal use by Repository
 */
class ContentMapper
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler */
    private $contentLanguageHandler;

    /** @var \eZ\Publish\Core\FieldType\FieldTypeRegistry */
    private $fieldTypeRegistry;

    public function __construct(
        Handler $contentLanguageHandler,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * Returns an array of fields like $fields[$field->fieldDefIdentifier][$field->languageCode].
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException If field definition does not exist in the ContentType
     *                                                                          or value is set for non-translatable field in language
     *                                                                          other than main
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     *
     * @return array
     */
    public function mapFieldsForCreate(ContentCreateStruct $contentCreateStruct): array
    {
        $fields = [];

        foreach ($contentCreateStruct->fields as $field) {
            $fieldDefinition = $contentCreateStruct->contentType->getFieldDefinition($field->fieldDefIdentifier);

            if ($fieldDefinition === null) {
                throw new ContentValidationException(
                    "Field definition '%identifier%' does not exist in the given Content Type",
                    ['%identifier%' => $field->fieldDefIdentifier]
                );
            }

            if ($field->languageCode === null) {
                $field = $this->cloneField(
                    $field,
                    ['languageCode' => $contentCreateStruct->mainLanguageCode]
                );
            }

            if (!$fieldDefinition->isTranslatable && ($field->languageCode != $contentCreateStruct->mainLanguageCode)) {
                throw new ContentValidationException(
                    "You cannot set a value for the non-translatable Field definition '%identifier%' in language '%languageCode%'",
                    ['%identifier%' => $field->fieldDefIdentifier, '%languageCode%' => $field->languageCode]
                );
            }

            $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }

        return $fields;
    }

    /**
     * Returns all language codes used in given $fields.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     *
     * @return string[]
     */
    public function getLanguageCodesForCreate(ContentCreateStruct $contentCreateStruct): array
    {
        $languageCodes = [];

        foreach ($contentCreateStruct->fields as $field) {
            if ($field->languageCode === null || isset($languageCodes[$field->languageCode])) {
                continue;
            }

            $this->contentLanguageHandler->loadByLanguageCode(
                $field->languageCode
            );
            $languageCodes[$field->languageCode] = true;
        }

        if (!isset($languageCodes[$contentCreateStruct->mainLanguageCode])) {
            $this->contentLanguageHandler->loadByLanguageCode(
                $contentCreateStruct->mainLanguageCode
            );
            $languageCodes[$contentCreateStruct->mainLanguageCode] = true;
        }

        return array_keys($languageCodes);
    }

    /**
     * Returns an array of fields like $fields[$field->fieldDefIdentifier][$field->languageCode].
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException If field definition does not exist in the ContentType
     *                                                                          or value is set for non-translatable field in language
     *                                                                          other than main
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return array
     */
    public function mapFieldsForUpdate(
        ContentUpdateStruct $contentUpdateStruct,
        ContentType $contentType,
        ?string $mainLanguageCode = null
    ): array {
        $fields = [];

        foreach ($contentUpdateStruct->fields as $field) {
            $fieldDefinition = $contentType->getFieldDefinition($field->fieldDefIdentifier);

            if ($fieldDefinition === null) {
                throw new ContentValidationException(
                    "Field definition '%identifier%' does not exist in given Content Type",
                    ['%identifier%' => $field->fieldDefIdentifier]
                );
            }

            if ($field->languageCode === null) {
                if ($fieldDefinition->isTranslatable) {
                    $languageCode = $contentUpdateStruct->initialLanguageCode;
                } else {
                    $languageCode = $mainLanguageCode;
                }
                $field = $this->cloneField($field, ['languageCode' => $languageCode]);
            }

            if (!$fieldDefinition->isTranslatable && ($field->languageCode != $mainLanguageCode)) {
                throw new ContentValidationException(
                    "You cannot set a value for the non-translatable Field definition '%identifier%' in language '%languageCode%'",
                    ['%identifier%' => $field->fieldDefIdentifier, '%languageCode%' => $field->languageCode]
                );
            }

            $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }

        return $fields;
    }

    public function getFieldValueForCreate(
        FieldDefinition $fieldDefinition,
        ?Field $field
    ): Value {
        if (null !== $field) {
            $fieldValue = $field->value;
        } else {
            $fieldValue = $fieldDefinition->defaultValue;
        }

        $fieldType = $this->fieldTypeRegistry->getFieldType(
            $fieldDefinition->fieldTypeIdentifier
        );

        return $fieldType->acceptValue($fieldValue);
    }

    public function getFieldValueForUpdate(
        ?Field $newField,
        ?Field $previousField,
        FieldDefinition $fieldDefinition,
        bool $isLanguageNew
    ): Value {
        $isFieldUpdated = null !== $newField;

        if (!$isFieldUpdated && !$isLanguageNew) {
            $fieldValue = $previousField->value;
        } elseif (!$isFieldUpdated && $isLanguageNew && !$fieldDefinition->isTranslatable) {
            $fieldValue = $previousField->value;
        } elseif ($isFieldUpdated) {
            $fieldValue = $newField->value;
        } else {
            $fieldValue = $fieldDefinition->defaultValue;
        }

        $fieldType = $this->fieldTypeRegistry->getFieldType(
            $fieldDefinition->fieldTypeIdentifier
        );

        return $fieldType->acceptValue($fieldValue);
    }

    /**
     * Returns all language codes used in given $fields.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return string[]
     */
    public function getLanguageCodesForUpdate(ContentUpdateStruct $contentUpdateStruct, Content $content): array
    {
        $languageCodes = array_fill_keys($content->versionInfo->languageCodes, true);
        $languageCodes[$contentUpdateStruct->initialLanguageCode] = true;

        $updatedLanguageCodes = $this->getUpdatedLanguageCodes($contentUpdateStruct);
        foreach ($updatedLanguageCodes as $languageCode) {
            $languageCodes[$languageCode] = true;
        }

        return array_keys($languageCodes);
    }

    /**
     * Returns only updated language codes.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     *
     * @return string[]
     */
    public function getUpdatedLanguageCodes(ContentUpdateStruct $contentUpdateStruct): array
    {
        $languageCodes = [
            $contentUpdateStruct->initialLanguageCode => true,
        ];

        foreach ($contentUpdateStruct->fields as $field) {
            if ($field->languageCode === null || isset($languageCodes[$field->languageCode])) {
                continue;
            }

            $languageCodes[$field->languageCode] = true;
        }

        return array_keys($languageCodes);
    }

    /**
     * Clones $field with overriding specific properties from given $overrides array.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     * @param array $overrides
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field
     */
    private function cloneField(Field $field, array $overrides = []): Field
    {
        $fieldData = array_merge(
            [
                'id' => $field->id,
                'value' => $field->value,
                'languageCode' => $field->languageCode,
                'fieldDefIdentifier' => $field->fieldDefIdentifier,
                'fieldTypeIdentifier' => $field->fieldTypeIdentifier,
            ],
            $overrides
        );

        return new Field($fieldData);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $updatedFields
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    public function getFieldsForUpdate(array $updatedFields, Content $content): array
    {
        $contentType = $content->getContentType();
        $fields = [];

        foreach ($updatedFields as $updatedField) {
            $fieldDefinition = $contentType->getFieldDefinition($updatedField->fieldDefIdentifier);

            if ($fieldDefinition === null) {
                throw new ContentValidationException(
                    "Field definition '%identifier%' does not exist in given Content Type",
                    ['%identifier%' => $updatedField->fieldDefIdentifier]
                );
            }

            $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldTypeIdentifier);

            $field = $content->getField($updatedField->fieldDefIdentifier);
            $updatedFieldValue = $this->getFieldValueForUpdate(
                $updatedField,
                $field,
                $contentType->getFieldDefinition($updatedField->fieldDefIdentifier),
                !in_array($updatedField->languageCode, $content->versionInfo->languageCodes, true)
            );

            if (!empty($field)) {
                $updatedFieldHash = md5(json_encode($fieldType->toHash($updatedFieldValue)));
                $contentFieldHash = md5(json_encode($fieldType->toHash($field->value)));

                if ($updatedFieldHash !== $contentFieldHash) {
                    $fields[] = $updatedField;
                }
            }
        }

        return $fields;
    }

    public function getFieldsForCreate(array $createdFields, ContentType $contentType): array
    {
        $fields = [];

        /** @var \eZ\Publish\API\Repository\Values\Content\Field $createdField */
        foreach ($createdFields as $createdField) {
            $fieldDefinition = $contentType->getFieldDefinition($createdField->fieldDefIdentifier);

            if ($fieldDefinition === null) {
                throw new ContentValidationException(
                    "Field definition '%identifier%' does not exist in the given Content Type",
                    ['%identifier%' => $createdField->fieldDefIdentifier]
                );
            }

            $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldTypeIdentifier);

            $createdFieldValue = $this->getFieldValueForCreate(
                $fieldDefinition,
                $createdField
            );

            $createdFieldHash = md5(json_encode($fieldType->toHash($createdFieldValue)));
            $defaultFieldHash = md5(json_encode($fieldType->toHash($fieldDefinition->defaultValue)));

            if ($createdFieldHash !== $defaultFieldHash) {
                $fields[] = $createdField;
            }
        }

        return $fields;
    }
}
