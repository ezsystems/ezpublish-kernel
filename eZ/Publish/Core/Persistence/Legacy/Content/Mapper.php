<?php

/**
 * File containing the Mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Relation;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry as Registry;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Mapper for Content Handler.
 *
 * Performs mapping of Content objects.
 */
class Mapper
{
    /**
     * FieldValue converter registry.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $converterRegistry;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new mapper.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry $converterRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(Registry $converterRegistry, LanguageHandler $languageHandler)
    {
        $this->converterRegistry = $converterRegistry;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates a Content from the given $struct and $currentVersionNo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    private function createContentInfoFromCreateStruct(CreateStruct $struct, $currentVersionNo = 1)
    {
        $contentInfo = new ContentInfo();

        $contentInfo->id = null;
        $contentInfo->contentTypeId = $struct->typeId;
        $contentInfo->sectionId = $struct->sectionId;
        $contentInfo->ownerId = $struct->ownerId;
        $contentInfo->alwaysAvailable = $struct->alwaysAvailable;
        $contentInfo->remoteId = $struct->remoteId;
        $contentInfo->mainLanguageCode = $this->languageHandler->load($struct->initialLanguageId)->languageCode;
        $contentInfo->name = isset($struct->name[$contentInfo->mainLanguageCode])
            ? $struct->name[$contentInfo->mainLanguageCode]
            : '';
        // For drafts published and modified timestamps should be 0
        $contentInfo->publicationDate = 0;
        $contentInfo->modificationDate = 0;
        $contentInfo->currentVersionNo = $currentVersionNo;
        $contentInfo->isPublished = false;

        return $contentInfo;
    }

    /**
     * Creates a new version for the given $struct and $versionNo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $versionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function createVersionInfoFromCreateStruct(CreateStruct $struct, $versionNo)
    {
        $versionInfo = new VersionInfo();

        $versionInfo->id = null;
        $versionInfo->contentInfo = $this->createContentInfoFromCreateStruct($struct, $versionNo);
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $struct->ownerId;
        $versionInfo->status = VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $this->languageHandler->load($struct->initialLanguageId)->languageCode;
        $versionInfo->creationDate = $struct->modified;
        $versionInfo->modificationDate = $struct->modified;
        $versionInfo->names = $struct->name;

        $languages = [];
        foreach ($struct->fields as $field) {
            if (!isset($languages[$field->languageCode])) {
                $languages[$field->languageCode] = true;
            }
        }
        $versionInfo->languageCodes = array_keys($languages);

        return $versionInfo;
    }

    /**
     * Creates a new version for the given $content.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param mixed $versionNo
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function createVersionInfoForContent(Content $content, $versionNo, $userId)
    {
        $versionInfo = new VersionInfo();

        $versionInfo->contentInfo = $content->versionInfo->contentInfo;
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $userId;
        $versionInfo->status = VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $content->versionInfo->initialLanguageCode;
        $versionInfo->creationDate = time();
        $versionInfo->modificationDate = $versionInfo->creationDate;
        $versionInfo->names = is_object($content->versionInfo) ? $content->versionInfo->names : array();
        $versionInfo->languageCodes = $content->versionInfo->languageCodes;

        return $versionInfo;
    }

    /**
     * Converts value of $field to storage value.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue
     */
    public function convertToStorageValue(Field $field)
    {
        $converter = $this->converterRegistry->getConverter(
            $field->type
        );
        $storageValue = new StorageFieldValue();
        $converter->toStorageValue(
            $field->value,
            $storageValue
        );

        return $storageValue;
    }

    /**
     * Extracts Content objects (and nested) from database result $rows.
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     * @param array $nameRows
     *
     * @return \eZ\Publish\SPI\Persistence\Content[]
     */
    public function extractContentFromRows(array $rows, array $nameRows)
    {
        $versionedNameData = array();
        foreach ($nameRows as $row) {
            $contentId = (int)$row['ezcontentobject_name_contentobject_id'];
            $versionNo = (int)$row['ezcontentobject_name_content_version'];
            $versionedNameData[$contentId][$versionNo][$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
        }

        $contentInfos = array();
        $versionInfos = array();
        $fields = array();

        foreach ($rows as $row) {
            $contentId = (int)$row['ezcontentobject_id'];
            if (!isset($contentInfos[$contentId])) {
                $contentInfos[$contentId] = $this->extractContentInfoFromRow($row, 'ezcontentobject_');
            }
            if (!isset($versionInfos[$contentId])) {
                $versionInfos[$contentId] = array();
            }

            $versionId = (int)$row['ezcontentobject_version_id'];
            if (!isset($versionInfos[$contentId][$versionId])) {
                $versionInfos[$contentId][$versionId] = $this->extractVersionInfoFromRow($row);
            }

            $fieldId = (int)$row['ezcontentobject_attribute_id'];
            if (!isset($fields[$contentId][$versionId][$fieldId])) {
                $fields[$contentId][$versionId][$fieldId] = $this->extractFieldFromRow($row);
            }
        }

        $results = array();
        foreach ($contentInfos as $contentId => $contentInfo) {
            foreach ($versionInfos[$contentId] as $versionId => $versionInfo) {
                // Fallback to just main language name if versioned name data is missing
                if (isset($versionedNameData[$contentId][$versionInfo->versionNo])) {
                    $names = $versionedNameData[$contentId][$versionInfo->versionNo];
                } else {
                    $names = [$contentInfo->mainLanguageCode => $contentInfo->name];
                }

                $content = new Content();
                $content->versionInfo = $versionInfo;
                $content->versionInfo->names = $names;
                $content->versionInfo->contentInfo = $contentInfo;
                $content->fields = array_values($fields[$contentId][$versionId]);
                $results[] = $content;
            }
        }

        return $results;
    }

    /**
     * Extracts a ContentInfo object from $row.
     *
     * @param array $row
     * @param string $prefix Prefix for row keys, which are initially mapped by ezcontentobject fields
     * @param string $treePrefix Prefix for tree row key, which are initially mapped by ezcontentobject_tree_ fields
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function extractContentInfoFromRow(array $row, $prefix = '', $treePrefix = 'ezcontentobject_tree_')
    {
        $contentInfo = new ContentInfo();
        $contentInfo->id = (int)$row["{$prefix}id"];
        $contentInfo->name = $row["{$prefix}name"];
        $contentInfo->contentTypeId = (int)$row["{$prefix}contentclass_id"];
        $contentInfo->sectionId = (int)$row["{$prefix}section_id"];
        $contentInfo->currentVersionNo = (int)$row["{$prefix}current_version"];
        $contentInfo->isPublished = (bool)($row["{$prefix}status"] == ContentInfo::STATUS_PUBLISHED);
        $contentInfo->ownerId = (int)$row["{$prefix}owner_id"];
        $contentInfo->publicationDate = (int)$row["{$prefix}published"];
        $contentInfo->modificationDate = (int)$row["{$prefix}modified"];
        $contentInfo->alwaysAvailable = (int)$row["{$prefix}language_mask"] & 1;
        $contentInfo->mainLanguageCode = $this->languageHandler->load($row["{$prefix}initial_language_id"])->languageCode;
        $contentInfo->remoteId = $row["{$prefix}remote_id"];
        $contentInfo->mainLocationId = ($row["{$treePrefix}main_node_id"] !== null ? (int)$row["{$treePrefix}main_node_id"] : null);

        return $contentInfo;
    }

    /**
     * Extracts ContentInfo objects from $rows.
     *
     * @param array $rows
     * @param string $prefix Prefix for row keys, which are initially mapped by ezcontentobject fields
     * @param string $treePrefix Prefix for tree row key, which are initially mapped by ezcontentobject_tree_ fields
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo[]
     */
    public function extractContentInfoFromRows(array $rows, $prefix = '', $treePrefix = 'ezcontentobject_tree_')
    {
        $contentInfoObjects = array();
        foreach ($rows as $row) {
            $contentInfoObjects[] = $this->extractContentInfoFromRow($row, $prefix, $treePrefix);
        }

        return $contentInfoObjects;
    }

    /**
     * Extracts a VersionInfo object from $row.
     *
     * This method will return VersionInfo with incomplete data. It is intended to be used only by
     * {@link self::extractContentFromRows} where missing data will be filled in.
     *
     * @param array $row
     * @param array $names
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    private function extractVersionInfoFromRow(array $row, array $names = array())
    {
        $versionInfo = new VersionInfo();
        $versionInfo->id = (int)$row['ezcontentobject_version_id'];
        $versionInfo->contentInfo = null;
        $versionInfo->versionNo = (int)$row['ezcontentobject_version_version'];
        $versionInfo->creatorId = (int)$row['ezcontentobject_version_creator_id'];
        $versionInfo->creationDate = (int)$row['ezcontentobject_version_created'];
        $versionInfo->modificationDate = (int)$row['ezcontentobject_version_modified'];
        $versionInfo->initialLanguageCode = $this->languageHandler->load($row['ezcontentobject_version_initial_language_id'])->languageCode;
        $versionInfo->languageCodes = $this->extractLanguageCodesFromMask($row['ezcontentobject_version_language_mask']);
        $versionInfo->status = (int)$row['ezcontentobject_version_status'];
        $versionInfo->names = $names;

        return $versionInfo;
    }

    /**
     * Extracts a VersionInfo object from $row.
     *
     * @param array $rows
     * @param array $nameRows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo[]
     */
    public function extractVersionInfoListFromRows(array $rows, array $nameRows)
    {
        $nameData = array();
        foreach ($nameRows as $row) {
            $versionId = $row['ezcontentobject_name_contentobject_id'] . '_' . $row['ezcontentobject_name_content_version'];
            $nameData[$versionId][$row['ezcontentobject_name_content_translation']] = $row['ezcontentobject_name_name'];
        }

        $versionInfoList = array();
        foreach ($rows as $row) {
            $versionId = $row['ezcontentobject_id'] . '_' . $row['ezcontentobject_version_version'];
            if (!isset($versionInfoList[$versionId])) {
                $versionInfo = new VersionInfo();
                $versionInfo->id = (int)$row['ezcontentobject_version_id'];
                $versionInfo->contentInfo = $this->extractContentInfoFromRow($row, 'ezcontentobject_');
                $versionInfo->versionNo = (int)$row['ezcontentobject_version_version'];
                $versionInfo->creatorId = (int)$row['ezcontentobject_version_creator_id'];
                $versionInfo->creationDate = (int)$row['ezcontentobject_version_created'];
                $versionInfo->modificationDate = (int)$row['ezcontentobject_version_modified'];
                $versionInfo->initialLanguageCode = $this->languageHandler->load($row['ezcontentobject_version_initial_language_id'])->languageCode;
                $versionInfo->languageCodes = $this->extractLanguageCodesFromMask((int)$row['ezcontentobject_version_language_mask']);
                $versionInfo->status = (int)$row['ezcontentobject_version_status'];
                $versionInfo->names = $nameData[$versionId];
                $versionInfoList[$versionId] = $versionInfo;
            }
        }

        return array_values($versionInfoList);
    }

    /**
     * @param int $languageMask
     *
     * @return string[]
     */
    public function extractLanguageCodesFromMask($languageMask)
    {
        $exp = 2;
        $result = [];

        // Decomposition of $languageMask into its binary components.
        while ($exp <= $languageMask) {
            if ($languageMask & $exp) {
                $result[] = $this->languageHandler->load($exp)->languageCode;
            }

            $exp *= 2;
        }

        return $result;
    }

    /**
     * Extracts a Field from $row.
     *
     * @param array $row
     *
     * @return Field
     */
    protected function extractFieldFromRow(array $row)
    {
        $field = new Field();

        $field->id = (int)$row['ezcontentobject_attribute_id'];
        $field->fieldDefinitionId = (int)$row['ezcontentobject_attribute_contentclassattribute_id'];
        $field->type = $row['ezcontentobject_attribute_data_type_string'];
        $field->value = $this->extractFieldValueFromRow($row, $field->type);
        $field->languageCode = $row['ezcontentobject_attribute_language_code'];
        $field->versionNo = (int)$row['ezcontentobject_attribute_version'];

        return $field;
    }

    /**
     * Extracts a FieldValue of $type from $row.
     *
     * @param array $row
     * @param string $type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @throws \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Exception\NotFound
     *         if the necessary converter for $type could not be found.
     */
    protected function extractFieldValueFromRow(array $row, $type)
    {
        $storageValue = new StorageFieldValue();

        // Nullable field
        $storageValue->dataFloat = isset($row['ezcontentobject_attribute_data_float'])
            ? (float)$row['ezcontentobject_attribute_data_float']
            : null;
        // Nullable field
        $storageValue->dataInt = isset($row['ezcontentobject_attribute_data_int'])
            ? (int)$row['ezcontentobject_attribute_data_int']
            : null;
        $storageValue->dataText = $row['ezcontentobject_attribute_data_text'];
        // Not nullable field
        $storageValue->sortKeyInt = (int)$row['ezcontentobject_attribute_sort_key_int'];
        $storageValue->sortKeyString = $row['ezcontentobject_attribute_sort_key_string'];

        $fieldValue = new FieldValue();

        $converter = $this->converterRegistry->getConverter($type);
        $converter->toFieldValue($storageValue, $fieldValue);

        return $fieldValue;
    }

    /**
     * Creates CreateStruct from $content.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    public function createCreateStructFromContent(Content $content)
    {
        $struct = new CreateStruct();
        $struct->name = $content->versionInfo->names;
        $struct->typeId = $content->versionInfo->contentInfo->contentTypeId;
        $struct->sectionId = $content->versionInfo->contentInfo->sectionId;
        $struct->ownerId = $content->versionInfo->contentInfo->ownerId;
        $struct->locations = array();
        $struct->alwaysAvailable = $content->versionInfo->contentInfo->alwaysAvailable;
        $struct->remoteId = md5(uniqid(get_class($this), true));
        $struct->initialLanguageId = $this->languageHandler->loadByLanguageCode($content->versionInfo->initialLanguageCode)->id;
        $struct->modified = time();

        foreach ($content->fields as $field) {
            $newField = clone $field;
            $newField->id = null;
            $struct->fields[] = $newField;
        }

        return $struct;
    }

    /**
     * Extracts relation objects from $rows.
     */
    public function extractRelationsFromRows(array $rows)
    {
        $relations = array();

        foreach ($rows as $row) {
            $id = (int)$row['ezcontentobject_link_id'];
            if (!isset($relations[$id])) {
                $relations[$id] = $this->extractRelationFromRow($row);
            }
        }

        return $relations;
    }

    /**
     * Extracts a Relation object from a $row.
     *
     * @param array $row Associative array representing a relation
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    protected function extractRelationFromRow(array $row)
    {
        $relation = new Relation();
        $relation->id = (int)$row['ezcontentobject_link_id'];
        $relation->sourceContentId = (int)$row['ezcontentobject_link_from_contentobject_id'];
        $relation->sourceContentVersionNo = (int)$row['ezcontentobject_link_from_contentobject_version'];
        $relation->destinationContentId = (int)$row['ezcontentobject_link_to_contentobject_id'];
        $relation->type = (int)$row['ezcontentobject_link_relation_type'];

        $contentClassAttributeId = (int)$row['ezcontentobject_link_contentclassattribute_id'];
        if ($contentClassAttributeId > 0) {
            $relation->sourceFieldDefinitionId = $contentClassAttributeId;
        }

        return $relation;
    }

    /**
     * Creates a Content from the given $struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function createRelationFromCreateStruct(RelationCreateStruct $struct)
    {
        $relation = new Relation();

        $relation->destinationContentId = $struct->destinationContentId;
        $relation->sourceContentId = $struct->sourceContentId;
        $relation->sourceContentVersionNo = $struct->sourceContentVersionNo;
        $relation->sourceFieldDefinitionId = $struct->sourceFieldDefinitionId;
        $relation->type = $struct->type;

        return $relation;
    }
}
