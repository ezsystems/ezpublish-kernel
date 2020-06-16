<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Mapper;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct as APIContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler;

/**
 * @internal Meant for internal use by Repository
 */
class ContentMapper
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler */
    private $contentLanguageHandler;

    public function __construct(
        Handler $contentLanguageHandler
    ) {
        $this->contentLanguageHandler = $contentLanguageHandler;
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
    public function mapFieldsForCreate(APIContentCreateStruct $contentCreateStruct): array
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
    public function getLanguageCodesForCreate(APIContentCreateStruct $contentCreateStruct): array
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
        APIContentUpdateStruct $contentUpdateStruct,
        ContentType $contentType,
        string $mainLanguageCode
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

    /**
     * Returns all language codes used in given $fields.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return string[]
     */
    public function getLanguageCodesForUpdate(APIContentUpdateStruct $contentUpdateStruct, APIContent $content): array
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
    public function getUpdatedLanguageCodes(APIContentUpdateStruct $contentUpdateStruct): array
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
}
