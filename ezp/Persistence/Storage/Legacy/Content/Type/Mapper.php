<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry as ConverterRegistry;

/**
 * Mapper for Content Type Handler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Converter registry
     *
     * @var ezp\Persistence\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistry;

    /**
     * Creates a new content type mapper
     *
     * @param ConverterRegistry $converterRegistry
     */
    public function __construct( ConverterRegistry $converterRegistry )
    {
        $this->converterRegistry = $converterRegistry;
    }

    /**
     * Creates a Group from its create struct.
     *
     * @param \ezp\Persistence\Content\Type\Group\CreateStruct $struct
     * @return Group
     * @todo $description is not supported by database, yet
     */
    public function createGroupFromCreateStruct( GroupCreateStruct $struct )
    {
        $group = new Group();

        $group->name = $struct->name;

        // Indentionally left out, since DB structure does not support it, yet
        // $group->description = $struct->description;

        $group->identifier = $struct->identifier;
        $group->created = $struct->created;
        $group->modified = $struct->modified;
        $group->creatorId = $struct->creatorId;
        $group->modifierId = $struct->modifierId;

        return $group;
    }

    /**
     * Extracts Group objects from theb given $rows.
     *
     * @param array $rows
     * @return ezp\Persistence\Content\Type\Group[]
     */
    public function extractGroupsFromRows( array $rows )
    {
        $groups = array();

        foreach ( $rows as $row )
        {
            $group             = new Group();
            $group->id         = (int) $row['id'];
            $group->created    = (int) $row['created'];
            $group->creatorId  = (int) $row['creator_id'];
            $group->modified   = (int) $row['modified'];
            $group->modifierId = (int) $row['modifier_id'];
            $group->identifier = $row['name'];

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     * @return array(Type)
     */
    public function extractTypesFromRows( array $rows )
    {
        $types = array();
        $fields = array();

        foreach ( $rows as $row )
        {
            $typeId = (int)$row['ezcontentclass_id'];
            if ( !isset( $types[$typeId] ) )
            {
                $types[$typeId] = $this->extractTypeFromRow( $row );
            }

            $fieldId = (int)$row['ezcontentclass_attribute_id'];
            if ( !isset( $fields[$fieldId] ) )
            {
                $field = $this->extractFieldFromRow( $row );
                $fields[$fieldId] = $field;
                $types[$typeId]->fieldDefinitions[] = $field;
            }

            $groupId = (int)$row['ezcontentclass_classgroup_group_id'];
            if ( !in_array( $groupId, $types[$typeId]->groupIds ) )
            {
                $types[$typeId]->groupIds[] = $groupId;
            }
        }

        // Re-index $types to avoid people relying on ID keys
        return array_values( $types );
    }

    /**
     * Creates a Type from the data in $row.
     *
     * @param array $row
     * @return Type
     */
    protected function extractTypeFromRow( array $row )
    {
        $type = new Type();

        $type->id = (int)$row['ezcontentclass_id'];
        $type->status = (int)$row['ezcontentclass_version'];
        $type->name = unserialize( $row['ezcontentclass_serialized_name_list'] );
        $type->description = unserialize( $row['ezcontentclass_serialized_description_list'] );
        $type->identifier = $row['ezcontentclass_identifier'];
        $type->created = (int)$row['ezcontentclass_created'];
        $type->modified = (int)$row['ezcontentclass_modified'];
        $type->modifierId = (int)$row['ezcontentclass_modifier_id'];
        $type->creatorId = (int)$row['ezcontentclass_creator_id'];
        $type->remoteId = $row['ezcontentclass_remote_id'];
        $type->urlAliasSchema = $row['ezcontentclass_url_alias_name'];
        $type->nameSchema = $row['ezcontentclass_contentobject_name'];
        $type->isContainer = ( $row['ezcontentclass_is_container'] == 1 );
        $type->initialLanguageId = (int)$row['ezcontentclass_initial_language_id'];
        $type->groupIds = array();
        $type->fieldDefinitions = array();

        return $type;
    }

    /**
     * Creates a FieldDefinition from the data in $row.
     *
     * @param array $row
     * @return FieldDefinition
     * @todo Handle field definition conversion.
     */
    protected function extractFieldFromRow( array $row )
    {
        $storageFieldDef = $this->extractStorageFieldFromRow( $row );

        $field = new FieldDefinition();

        $field->id = (int)$row['ezcontentclass_attribute_id'];
        $field->name = unserialize( $row['ezcontentclass_attribute_serialized_name_list'] );
        $field->description = unserialize( $row['ezcontentclass_attribute_serialized_description_list'] );
        $field->identifier = $row['ezcontentclass_attribute_identifier'];
        $field->fieldGroup = $row['ezcontentclass_attribute_category'];
        $field->fieldType = $row['ezcontentclass_attribute_data_type_string'];
        $field->isTranslatable = ( $row['ezcontentclass_attribute_can_translate'] == 1 );
        $field->isRequired = $row['ezcontentclass_attribute_is_required'] == 1;
        $field->isInfoCollector = $row['ezcontentclass_attribute_is_information_collector'] == 1;
        $field->defaultValue = unserialize( $row['ezcontentclass_attribute_serialized_data_text'] );

        $this->toFieldDefinition( $storageFieldDef, $field );

        return $field;
    }

    /**
     * Extracts a StorageFieldDefinition from $row
     *
     * @param array $row
     * @return StorageFieldDefinition
     */
    protected function extractStorageFieldFromRow( array $row )
    {
        $storageFieldDef = new StorageFieldDefinition();

        $storageFieldDef->dataFloat1 = (float) $row['ezcontentclass_attribute_data_float1'];
        $storageFieldDef->dataFloat2 = (float) $row['ezcontentclass_attribute_data_float2'];
        $storageFieldDef->dataFloat3 = (float) $row['ezcontentclass_attribute_data_float3'];
        $storageFieldDef->dataFloat4 = (float) $row['ezcontentclass_attribute_data_float4'];
        $storageFieldDef->dataInt1 = (int) $row['ezcontentclass_attribute_data_int1'];
        $storageFieldDef->dataInt2 = (int) $row['ezcontentclass_attribute_data_int2'];
        $storageFieldDef->dataInt3 = (int) $row['ezcontentclass_attribute_data_int3'];
        $storageFieldDef->dataInt4 = (int) $row['ezcontentclass_attribute_data_int4'];
        $storageFieldDef->dataText1 = $row['ezcontentclass_attribute_data_text1'];
        $storageFieldDef->dataText2 = $row['ezcontentclass_attribute_data_text2'];
        $storageFieldDef->dataText3 = $row['ezcontentclass_attribute_data_text3'];
        $storageFieldDef->dataText4 = $row['ezcontentclass_attribute_data_text4'];
        $storageFieldDef->dataText5 = $row['ezcontentclass_attribute_data_text5'];
        $storageFieldDef->serializedDataText = $row['ezcontentclass_attribute_serialized_data_text'];

        return $storageFieldDef;
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param Type $type
     * @param \ezp\Persistence\Content\Type\CreateStruct $struct
     * @return void
     */
    public function createTypeFromCreateStruct( CreateStruct $createStruct )
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

        return $type;
    }

    /**
     * Creates a create struct from an existing $type.
     *
     * @param Type $type
     * @return \ezp\Persistence\Content\Type\CreateStruct
     */
    public function createCreateStructFromType( Type $type )
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

        return $createStruct;
    }

    /**
     * Maps $fieldDef to the legacy storage specific StorageFieldDefinition
     *
     * @param FieldDefinition $fieldDef
     * @param StorageFieldDefinition $storageFieldDef
     * @return void
     */
    public function toStorageFieldDefinition(
        FieldDefinition $fieldDef, StorageFieldDefinition $storageFieldDef )
    {
        $converter = $this->converterRegistry->getConverter(
            $fieldDef->fieldType
        );
        $converter->toStorageFieldDefinition(
            $fieldDef,
            $storageFieldDef
        );
    }

    /**
     * Maps a FieldDefinition from the given $storageFieldDef
     *
     * @param StorageFieldDefinition $storageFieldDef
     * @param FieldDefinition $fieldDef
     * @return void
     */
    public function toFieldDefinition(
        StorageFieldDefinition $storageFieldDef, FieldDefinition $fieldDef )
    {
        $converter = $this->converterRegistry->getConverter(
            $fieldDef->fieldType
        );
        $converter->toFieldDefinition(
            $storageFieldDef,
            $fieldDef
        );
    }
}
