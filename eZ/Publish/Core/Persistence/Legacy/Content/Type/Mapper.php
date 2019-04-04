<?php

/**
 * File containing the Mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\MultilingualStorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface;

/**
 * Mapper for Content Type Handler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Converter registry.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface
     */
    protected $converterRegistry;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    private $maskGenerator;

    /**
     * Creates a new content type mapper.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistryInterface $converterRegistry
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     */
    public function __construct(ConverterRegistryInterface $converterRegistry, MaskGenerator $maskGenerator)
    {
        $this->converterRegistry = $converterRegistry;
        $this->maskGenerator = $maskGenerator;
    }

    /**
     * Creates a Group from its create struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $struct
     *
     * @todo $description is not supported by database, yet
     *
     * @return Group
     */
    public function createGroupFromCreateStruct(GroupCreateStruct $struct)
    {
        $group = new Group();

        $group->name = $struct->name;

        // $group->description is intentionally left out, since DB structure does not support it, yet

        $group->identifier = $struct->identifier;
        $group->created = $struct->created;
        $group->modified = $struct->modified;
        $group->creatorId = $struct->creatorId;
        $group->modifierId = $struct->modifierId;

        return $group;
    }

    /**
     * Extracts Group objects from the given $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group[]
     */
    public function extractGroupsFromRows(array $rows)
    {
        $groups = array();

        foreach ($rows as $row) {
            $group = new Group();
            $group->id = (int)$row['id'];
            $group->created = (int)$row['created'];
            $group->creatorId = (int)$row['creator_id'];
            $group->modified = (int)$row['modified'];
            $group->modifierId = (int)$row['modifier_id'];
            $group->identifier = $row['name'];

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     * @param bool $keepTypeIdAsKey
     *
     * @return array(Type)
     */
    public function extractTypesFromRows(array $rows, bool $keepTypeIdAsKey = false)
    {
        $types = array();
        $fields = array();

        foreach ($rows as $row) {
            $typeId = (int)$row['ezcontentclass_id'];
            if (!isset($types[$typeId])) {
                $types[$typeId] = $this->extractTypeFromRow($row);
            }

            $fieldId = (int)$row['ezcontentclass_attribute_id'];

            if ($fieldId && !isset($fields[$fieldId])) {
                $fieldDataRows = array_filter($rows, function (array $row) use ($fieldId) {
                    return (int) $row['ezcontentclass_attribute_id'] === (int) $fieldId;
                });

                $multilingualData = $this->extractMultilingualData($fieldDataRows);

                $types[$typeId]->fieldDefinitions[] = $fields[$fieldId] = $this->extractFieldFromRow($row, $multilingualData);
            }

            $groupId = (int)$row['ezcontentclass_classgroup_group_id'];
            if (!in_array($groupId, $types[$typeId]->groupIds)) {
                $types[$typeId]->groupIds[] = $groupId;
            }
        }

        foreach ($types as $type) {
            sort($type->groupIds);
        }

        if ($keepTypeIdAsKey) {
            return $types;
        }

        // Re-index $types to avoid people relying on ID keys
        return array_values($types);
    }

    public function extractMultilingualData(array $fieldDefinitionRows): array
    {
        return array_map(function (array $fieldData) {
            return [
                'ezcontentclass_attribute_multilingual_name' => $fieldData['ezcontentclass_attribute_multilingual_name'] ?? null,
                'ezcontentclass_attribute_multilingual_description' => $fieldData['ezcontentclass_attribute_multilingual_description'] ?? null,
                'ezcontentclass_attribute_multilingual_language_id' => $fieldData['ezcontentclass_attribute_multilingual_language_id'] ?? null,
                'ezcontentclass_attribute_multilingual_data_text' => $fieldData['ezcontentclass_attribute_multilingual_data_text'] ?? null,
                'ezcontentclass_attribute_multilingual_data_json' => $fieldData['ezcontentclass_attribute_multilingual_data_json'] ?? null,
            ];
        }, $fieldDefinitionRows);
    }

    /**
     * Creates a Type from the data in $row.
     *
     * @param array $row
     *
     * @return Type
     */
    protected function extractTypeFromRow(array $row)
    {
        $type = new Type();

        $type->id = (int)$row['ezcontentclass_id'];
        $type->status = (int)$row['ezcontentclass_version'];
        $type->name = $this->unserialize($row['ezcontentclass_serialized_name_list']);
        $type->description = $this->unserialize($row['ezcontentclass_serialized_description_list']);
        // Unset redundant data
        unset(
            $type->name['always-available'],
            $type->name[0],
            $type->description['always-available'],
            $type->description[0]
        );
        $type->identifier = $row['ezcontentclass_identifier'];
        $type->created = (int)$row['ezcontentclass_created'];
        $type->modified = (int)$row['ezcontentclass_modified'];
        $type->modifierId = (int)$row['ezcontentclass_modifier_id'];
        $type->creatorId = (int)$row['ezcontentclass_creator_id'];
        $type->remoteId = $row['ezcontentclass_remote_id'];
        $type->urlAliasSchema = $row['ezcontentclass_url_alias_name'];
        $type->nameSchema = $row['ezcontentclass_contentobject_name'];
        $type->isContainer = ($row['ezcontentclass_is_container'] == 1);
        $type->initialLanguageId = (int)$row['ezcontentclass_initial_language_id'];
        $type->defaultAlwaysAvailable = ($row['ezcontentclass_always_available'] == 1);
        $type->sortField = (int)$row['ezcontentclass_sort_field'];
        $type->sortOrder = (int)$row['ezcontentclass_sort_order'];
        $type->languageCodes = $this->maskGenerator->extractLanguageCodesFromMask((int)$row['ezcontentclass_language_mask']);

        $type->groupIds = array();
        $type->fieldDefinitions = array();

        return $type;
    }

    /**
     * Creates a FieldDefinition from the data in $row.
     *
     * @param array $row
     *
     * @param array $multilingualData
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function extractFieldFromRow(array $row, array $multilingualData = [])
    {
        $storageFieldDef = $this->extractStorageFieldFromRow($row, $multilingualData);

        $field = new FieldDefinition();

        $field->id = (int)$row['ezcontentclass_attribute_id'];
        $field->name = $this->unserialize($row['ezcontentclass_attribute_serialized_name_list']);
        $field->description = $this->unserialize($row['ezcontentclass_attribute_serialized_description_list']);
        // Unset redundant data
        unset(
            $field->name['always-available'],
            $field->name[0],
            $field->description['always-available'],
            $field->description[0]
        );
        $field->identifier = $row['ezcontentclass_attribute_identifier'];
        $field->fieldGroup = $row['ezcontentclass_attribute_category'];
        $field->fieldType = $row['ezcontentclass_attribute_data_type_string'];
        $field->isTranslatable = ($row['ezcontentclass_attribute_can_translate'] == 1);
        $field->isRequired = $row['ezcontentclass_attribute_is_required'] == 1;
        $field->isInfoCollector = $row['ezcontentclass_attribute_is_information_collector'] == 1;

        $field->isSearchable = (bool)$row['ezcontentclass_attribute_is_searchable'];
        $field->position = (int)$row['ezcontentclass_attribute_placement'];

        $mainLanguageCode = $this->maskGenerator->extractLanguageCodesFromMask((int)$row['ezcontentclass_initial_language_id']);
        $field->mainLanguageCode = array_shift($mainLanguageCode);

        $this->toFieldDefinition($storageFieldDef, $field);

        return $field;
    }

    /**
     * Extracts a StorageFieldDefinition from $row.
     *
     * @param array $row
     *
     * @param array $multilingualDataRow
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition
     */
    protected function extractStorageFieldFromRow(array $row, array $multilingualDataRow = [])
    {
        $storageFieldDef = new StorageFieldDefinition();

        $storageFieldDef->dataFloat1 = isset($row['ezcontentclass_attribute_data_float1'])
            ? (float)$row['ezcontentclass_attribute_data_float1']
            : null;
        $storageFieldDef->dataFloat2 = isset($row['ezcontentclass_attribute_data_float2'])
            ? (float)$row['ezcontentclass_attribute_data_float2']
            : null;
        $storageFieldDef->dataFloat3 = isset($row['ezcontentclass_attribute_data_float3'])
            ? (float)$row['ezcontentclass_attribute_data_float3']
            : null;
        $storageFieldDef->dataFloat4 = isset($row['ezcontentclass_attribute_data_float4'])
            ? (float)$row['ezcontentclass_attribute_data_float4']
            : null;
        $storageFieldDef->dataInt1 = isset($row['ezcontentclass_attribute_data_int1'])
            ? (int)$row['ezcontentclass_attribute_data_int1']
            : null;
        $storageFieldDef->dataInt2 = isset($row['ezcontentclass_attribute_data_int2'])
            ? (int)$row['ezcontentclass_attribute_data_int2']
            : null;
        $storageFieldDef->dataInt3 = isset($row['ezcontentclass_attribute_data_int3'])
            ? (int)$row['ezcontentclass_attribute_data_int3']
            : null;
        $storageFieldDef->dataInt4 = isset($row['ezcontentclass_attribute_data_int4'])
            ? (int)$row['ezcontentclass_attribute_data_int4']
            : null;
        $storageFieldDef->dataText1 = $row['ezcontentclass_attribute_data_text1'];
        $storageFieldDef->dataText2 = $row['ezcontentclass_attribute_data_text2'];
        $storageFieldDef->dataText3 = $row['ezcontentclass_attribute_data_text3'];
        $storageFieldDef->dataText4 = $row['ezcontentclass_attribute_data_text4'];
        $storageFieldDef->dataText5 = $row['ezcontentclass_attribute_data_text5'];
        $storageFieldDef->serializedDataText = $row['ezcontentclass_attribute_serialized_data_text'];

        foreach ($multilingualDataRow as $languageDataRow) {
            $languageCodes = $this->maskGenerator->extractLanguageCodesFromMask((int)$languageDataRow['ezcontentclass_attribute_multilingual_language_id']);

            if (empty($languageCodes)) {
                continue;
            }
            $languageCode = reset($languageCodes);

            $multilingualData = new MultilingualStorageFieldDefinition();

            $nameList = $this->unserialize($row['ezcontentclass_attribute_serialized_name_list']);
            $name = $nameList[$languageCode] ?? reset($nameList);
            $description = $this->unserialize($row['ezcontentclass_attribute_serialized_description_list'])[$languageCode] ?? null;

            $multilingualData->name = $languageDataRow['ezcontentclass_attribute_multilingual_name'] ?? $name;
            $multilingualData->description = $languageDataRow['ezcontentclass_attribute_multilingual_description'] ?? $description;
            $multilingualData->dataText = $languageDataRow['ezcontentclass_attribute_multilingual_data_text'];
            $multilingualData->dataJson = $languageDataRow['ezcontentclass_attribute_multilingual_data_json'];
            $multilingualData->languageId = (int)$languageDataRow['ezcontentclass_attribute_multilingual_language_id'];

            $storageFieldDef->multilingualData[$languageCode] = $multilingualData;
        }

        return $storageFieldDef;
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function createTypeFromCreateStruct(CreateStruct $createStruct)
    {
        $type = new Type();

        $type->name = $createStruct->name;
        $type->status = $createStruct->status;
        $type->description = $createStruct->description;
        $type->identifier = $createStruct->identifier;
        $type->created = $createStruct->created;
        $type->modified = $createStruct->modified;
        $type->creatorId = $createStruct->creatorId;
        $type->modifierId = $createStruct->modifierId;
        $type->remoteId = $createStruct->remoteId;
        $type->urlAliasSchema = $createStruct->urlAliasSchema;
        $type->nameSchema = $createStruct->nameSchema;
        $type->isContainer = $createStruct->isContainer;
        $type->initialLanguageId = $createStruct->initialLanguageId;
        $type->groupIds = $createStruct->groupIds;
        $type->fieldDefinitions = $createStruct->fieldDefinitions;
        $type->defaultAlwaysAvailable = $createStruct->defaultAlwaysAvailable;
        $type->sortField = $createStruct->sortField;
        $type->sortOrder = $createStruct->sortOrder;
        $type->languageCodes = array_keys($createStruct->name);

        return $type;
    }

    /**
     * Creates a create struct from an existing $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    public function createCreateStructFromType(Type $type)
    {
        $createStruct = new CreateStruct();

        $createStruct->name = $type->name;
        $createStruct->status = $type->status;
        $createStruct->description = $type->description;
        $createStruct->identifier = $type->identifier;
        $createStruct->created = $type->created;
        $createStruct->modified = $type->modified;
        $createStruct->creatorId = $type->creatorId;
        $createStruct->modifierId = $type->modifierId;
        $createStruct->remoteId = $type->remoteId;
        $createStruct->urlAliasSchema = $type->urlAliasSchema;
        $createStruct->nameSchema = $type->nameSchema;
        $createStruct->isContainer = $type->isContainer;
        $createStruct->initialLanguageId = $type->initialLanguageId;
        $createStruct->groupIds = $type->groupIds;
        $createStruct->fieldDefinitions = $type->fieldDefinitions;
        $createStruct->defaultAlwaysAvailable = $type->defaultAlwaysAvailable;
        $createStruct->sortField = $type->sortField;
        $createStruct->sortOrder = $type->sortOrder;

        return $createStruct;
    }

    /**
     * Creates an update struct from an existing $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    public function createUpdateStructFromType(Type $type)
    {
        $updateStruct = new UpdateStruct();

        $updateStruct->name = $type->name;
        $updateStruct->description = $type->description;
        $updateStruct->identifier = $type->identifier;
        $updateStruct->modified = $type->modified;
        $updateStruct->modifierId = $type->modifierId;
        $updateStruct->remoteId = $type->remoteId;
        $updateStruct->urlAliasSchema = $type->urlAliasSchema;
        $updateStruct->nameSchema = $type->nameSchema;
        $updateStruct->isContainer = $type->isContainer;
        $updateStruct->initialLanguageId = $type->initialLanguageId;
        $updateStruct->defaultAlwaysAvailable = $type->defaultAlwaysAvailable;
        $updateStruct->sortField = $type->sortField;
        $updateStruct->sortOrder = $type->sortOrder;

        return $updateStruct;
    }

    /**
     * Maps $fieldDef to the legacy storage specific StorageFieldDefinition.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     */
    public function toStorageFieldDefinition(
        FieldDefinition $fieldDef,
        StorageFieldDefinition $storageFieldDef
    ) {
        foreach (array_keys($fieldDef->name) as $languageCode) {
            $multilingualData = new MultilingualStorageFieldDefinition();
            $multilingualData->name = $fieldDef->name[$languageCode];
            $multilingualData->description = $fieldDef->description[$languageCode] ?? null;
            $multilingualData->languageId =
                $this->maskGenerator->generateLanguageMaskFromLanguageCodes([$languageCode]);

            $storageFieldDef->multilingualData[$languageCode] = $multilingualData;
        }

        $converter = $this->converterRegistry->getConverter(
            $fieldDef->fieldType
        );

        $converter->toStorageFieldDefinition(
            $fieldDef,
            $storageFieldDef
        );
    }

    /**
     * Maps a FieldDefinition from the given $storageFieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(
        StorageFieldDefinition $storageFieldDef,
        FieldDefinition $fieldDef
    ) {
        $converter = $this->converterRegistry->getConverter(
            $fieldDef->fieldType
        );
        $converter->toFieldDefinition(
            $storageFieldDef,
            $fieldDef
        );
    }

    /**
     * Wrap unserialize to set default value in case of empty serialization.
     *
     * @param string $serialized Serialized structure to process
     * @param mixed $default Default value in case of empty serialization
     *
     * @return array|mixed
     */
    protected function unserialize($serialized, $default = [])
    {
        return $serialized
            ? unserialize($serialized)
            : $default;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $updateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function createTypeFromUpdateStruct(UpdateStruct $updateStruct): Type
    {
        $type = new Type();

        $type->name = $updateStruct->name;
        $type->description = $updateStruct->description;
        $type->identifier = $updateStruct->identifier;
        $type->modified = $updateStruct->modified;
        $type->modifierId = $updateStruct->modifierId;
        $type->remoteId = $updateStruct->remoteId;
        $type->urlAliasSchema = $updateStruct->urlAliasSchema;
        $type->nameSchema = $updateStruct->nameSchema;
        $type->isContainer = $updateStruct->isContainer;
        $type->initialLanguageId = $updateStruct->initialLanguageId;
        $type->defaultAlwaysAvailable = $updateStruct->defaultAlwaysAvailable;
        $type->sortField = $updateStruct->sortField;
        $type->sortOrder = $updateStruct->sortOrder;
        $type->languageCodes = array_keys($updateStruct->name);

        return $type;
    }

    public function extractMultilingualDataFromRows(array $mlFieldDefinitionsRows): array
    {
        $mlFieldDefinitionData = [];
        foreach ($mlFieldDefinitionsRows as $row) {
            $mlStorageFieldDefinition = new MultilingualStorageFieldDefinition();
            $mlStorageFieldDefinition->name = $row['ezcontentclass_attribute_multilingual_name'];
            $mlStorageFieldDefinition->description = $row['ezcontentclass_attribute_multilingual_description'];
            $mlStorageFieldDefinition->languageId = $row['ezcontentclass_attribute_multilingual_language_id'];
            $mlStorageFieldDefinition->dataText = $row['ezcontentclass_attribute_multilingual_data_text'];
            $mlStorageFieldDefinition->dataJson = $row['ezcontentclass_attribute_multilingual_data_json'];

            $mlFieldDefinitionData[] = $mlStorageFieldDefinition;
        }

        return $mlFieldDefinitionData;
    }
}
