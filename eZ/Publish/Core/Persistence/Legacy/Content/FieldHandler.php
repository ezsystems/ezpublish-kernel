<?php

/**
 * File containing the Content FieldHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\FieldTypeRegistry;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Field Handler.
 */
class FieldHandler
{
    /**
     * Content Gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $contentGateway;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Content Mapper.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapper;

    /**
     * Storage Handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * FieldType registry.
     *
     * @var \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    protected $fieldTypeRegistry;

    /**
     * Hash of SPI FieldTypes or callable callbacks to generate one.
     *
     * @var array
     */
    protected $fieldTypes;

    /**
     * Creates a new Field Handler.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $contentGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler $storageHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        Gateway $contentGateway,
        Mapper $mapper,
        StorageHandler $storageHandler,
        LanguageHandler $languageHandler,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentGateway = $contentGateway;
        $this->mapper = $mapper;
        $this->storageHandler = $storageHandler;
        $this->languageHandler = $languageHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * Creates new fields in the database from $content of $contentType.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     */
    public function createNewFields(Content $content, Type $contentType)
    {
        $fieldsToCopy = array();
        $languageCodes = array();
        $fields = $this->getFieldMap($content->fields, $languageCodes);
        $languageCodes[$content->versionInfo->contentInfo->mainLanguageCode] = true;

        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            foreach (array_keys($languageCodes) as $languageCode) {
                // Create fields passed from struct
                if (isset($fields[$fieldDefinition->id][$languageCode])) {
                    $field = $fields[$fieldDefinition->id][$languageCode];
                    $this->createNewField($field, $content);
                } elseif (
                    !$fieldDefinition->isTranslatable
                    && isset($fields[$fieldDefinition->id][$content->versionInfo->contentInfo->mainLanguageCode])
                ) {
                    // Copy only for untranslatable field and when field in main language exists
                    // Only register here, process later as field copied should be already stored
                    $fieldsToCopy[$fieldDefinition->id][$languageCode] =
                        $fields[$fieldDefinition->id][$content->versionInfo->contentInfo->mainLanguageCode];
                } else { // In all other cases create empty field
                    $field = $this->getEmptyField($fieldDefinition, $languageCode);
                    $content->fields[] = $field;
                    $this->createNewField($field, $content);
                }
            }
        }

        $this->copyFields($fieldsToCopy, $content);
    }

    /**
     * Returns empty Field object for given field definition and language code.
     *
     * Uses FieldType to create empty field value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field
     */
    protected function getEmptyField(FieldDefinition $fieldDefinition, $languageCode)
    {
        $fieldType = $this->fieldTypeRegistry->getFieldType($fieldDefinition->fieldType);

        return new Field(
            array(
                'fieldDefinitionId' => $fieldDefinition->id,
                'type' => $fieldDefinition->fieldType,
                'value' => $fieldType->getEmptyValue(),
                'languageCode' => $languageCode,
            )
        );
    }

    /**
     * Creates existing fields in a new version for $content.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function createExistingFieldsInNewVersion(Content $content)
    {
        foreach ($content->fields as $field) {
            $this->createExistingFieldInNewVersion($field, $content);
        }
    }

    /**
     * Creates a new field in the database.
     *
     * Used by self::createNewFields() and self::updateFields()
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function createNewField(Field $field, Content $content)
    {
        $field->versionNo = $content->versionInfo->versionNo;

        $field->id = $this->contentGateway->insertNewField(
            $content,
            $field,
            $this->mapper->convertToStorageValue($field)
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ($this->storageHandler->storeFieldData($content->versionInfo, $field) === true) {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue($field)
            );
        }
    }

    /**
     * @param array $fields
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function copyFields(array $fields, Content $content)
    {
        foreach ($fields as $languageFields) {
            foreach ($languageFields as $languageCode => $field) {
                $this->copyField($field, $languageCode, $content);
            }
        }
    }

    /**
     * Copies existing field to new field for given $languageCode.
     *
     * Used by self::createNewFields() and self::updateFields()
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param string $languageCode
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function copyField(Field $originalField, $languageCode, Content $content)
    {
        $originalField->versionNo = $content->versionInfo->versionNo;
        $field = clone $originalField;
        $field->languageCode = $languageCode;

        $field->id = $this->contentGateway->insertNewField(
            $content,
            $field,
            $this->mapper->convertToStorageValue($field)
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ($this->storageHandler->copyFieldData($content->versionInfo, $field, $originalField) === true) {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue($field)
            );
        }

        $content->fields[] = $field;
    }

    /**
     * Updates an existing field in the database.
     *
     * Used by self::createNewFields() and self::updateFields()
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function updateField(Field $field, Content $content)
    {
        $this->contentGateway->updateField(
            $field,
            $this->mapper->convertToStorageValue($field)
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ($this->storageHandler->storeFieldData($content->versionInfo, $field) === true) {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue($field)
            );
        }
    }

    /**
     * Creates an existing field in a new version, no new ID is generated.
     *
     * Used to insert a field with an existing ID but a new version number.
     * $content is used for new version data, needed by Content gateway and external storage.
     *
     * External data is being copied here as some FieldTypes require original field external data.
     * By default copying falls back to storing, it is upon external storage implementation to override
     * the behaviour as needed.
     *
     * @param Field $field
     * @param Content $content
     */
    protected function createExistingFieldInNewVersion(Field $field, Content $content)
    {
        $originalField = clone $field;
        $field->versionNo = $content->versionInfo->versionNo;

        $this->contentGateway->insertExistingField(
            $content,
            $field,
            $this->mapper->convertToStorageValue($field)
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ($this->storageHandler->copyFieldData($content->versionInfo, $field, $originalField) === true) {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue($field)
            );
        }
    }

    /**
     * Performs external loads for the fields in $content.
     *
     * @param Content $content
     */
    public function loadExternalFieldData(Content $content)
    {
        foreach ($content->fields as $field) {
            $this->storageHandler->getFieldData($content->versionInfo, $field);
        }
    }

    /**
     * Updates the fields in for content identified by $contentId and $versionNo in the database in respect to $updateStruct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $updateStruct
     * @param \eZ\Publish\SPI\Persistence\Content\Type $contentType
     */
    public function updateFields(Content $content, UpdateStruct $updateStruct, Type $contentType)
    {
        $updatedFields = array();
        $fieldsToCopy = array();
        $nonTranslatableCopiesUpdateSet = array();
        $mainLanguageCode = $content->versionInfo->contentInfo->mainLanguageCode;
        $languageCodes = $existingLanguageCodes = array_fill_keys($content->versionInfo->languageCodes, true);
        $contentFieldMap = $this->getFieldMap($content->fields);
        $updateFieldMap = $this->getFieldMap($updateStruct->fields, $languageCodes);
        $initialLanguageCode = $this->languageHandler->load($updateStruct->initialLanguageId)->languageCode;
        $languageCodes[$initialLanguageCode] = true;

        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            foreach (array_keys($languageCodes) as $languageCode) {
                if (isset($updateFieldMap[$fieldDefinition->id][$languageCode])) {
                    $field = clone $updateFieldMap[$fieldDefinition->id][$languageCode];
                    $field->versionNo = $content->versionInfo->versionNo;
                    if (isset($field->id)) {
                        $this->updateField($field, $content);
                        $updatedFields[$fieldDefinition->id][$languageCode] = $field;
                    } else {
                        $this->createNewField($field, $content);
                    }
                } elseif (!isset($existingLanguageCodes[$languageCode])) {
                    // If field is not set for new language
                    if ($fieldDefinition->isTranslatable) {
                        // Use empty value for translatable field
                        $field = $this->getEmptyField($fieldDefinition, $languageCode);
                        $this->createNewField($field, $content);
                    } else {
                        // Use value from main language code for untranslatable field
                        $fieldsToCopy[$fieldDefinition->id][$languageCode] =
                            isset($updateFieldMap[$fieldDefinition->id][$mainLanguageCode])
                                ? $updateFieldMap[$fieldDefinition->id][$mainLanguageCode]
                                : $contentFieldMap[$fieldDefinition->id][$mainLanguageCode];
                    }
                } elseif (!$fieldDefinition->isTranslatable
                    && isset($updateFieldMap[$fieldDefinition->id][$mainLanguageCode])
                ) {
                    // If field is not set for existing language and is untranslatable and main language is updated,
                    // also update copied field data
                    // Register for processing after all given fields are updated
                    $nonTranslatableCopiesUpdateSet[$fieldDefinition->id][] = $languageCode;
                }

                // If no above conditions were met - do nothing
            }
        }

        foreach ($nonTranslatableCopiesUpdateSet as $fieldDefinitionId => $languageCodes) {
            foreach ($languageCodes as $languageCode) {
                $this->updateCopiedField(
                    $contentFieldMap[$fieldDefinitionId][$languageCode],
                    $updateFieldMap[$fieldDefinitionId][$mainLanguageCode],
                    $updatedFields[$fieldDefinitionId][$mainLanguageCode],
                    $content
                );
            }
        }

        $this->copyFields($fieldsToCopy, $content);
    }

    /**
     * Updates a language copy of a non-translatable field.
     *
     * External data is being copied here as some FieldTypes require original field external data.
     * By default copying falls back to storing, it is upon external storage implementation to override
     * the behaviour as needed.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $updateField
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function updateCopiedField(Field $field, Field $updateField, Field $originalField, Content $content)
    {
        $field->versionNo = $content->versionInfo->versionNo;
        $field->value = clone $updateField->value;

        $this->contentGateway->updateField(
            $field,
            $this->mapper->convertToStorageValue($field)
        );

        // If the storage handler returns true, it means that $field value has been modified
        // So we need to update it in order to store those modifications
        // Field converter is called once again via the Mapper
        if ($this->storageHandler->copyFieldData($content->versionInfo, $field, $originalField) === true) {
            $this->contentGateway->updateField(
                $field,
                $this->mapper->convertToStorageValue($field)
            );
        }
    }

    /**
     * Returns given $fields structured in hash array with field definition ids and language codes as keys.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param array $languageCodes
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Field[][]
     */
    protected function getFieldMap(array $fields, &$languageCodes = null)
    {
        $fieldMap = array();
        foreach ($fields as $field) {
            if (isset($languageCodes)) {
                $languageCodes[$field->languageCode] = true;
            }
            $fieldMap[$field->fieldDefinitionId][$field->languageCode] = $field;
        }

        return $fieldMap;
    }

    /**
     * Deletes the fields for $contentId in $versionInfo from the database.
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     */
    public function deleteFields($contentId, VersionInfo $versionInfo)
    {
        foreach ($this->contentGateway->getFieldIdsByType($contentId, $versionInfo->versionNo) as $fieldType => $ids) {
            $this->storageHandler->deleteFieldData($fieldType, $versionInfo, $ids);
        }
        $this->contentGateway->deleteFields($contentId, $versionInfo->versionNo);
    }
}
