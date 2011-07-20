<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\Type;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\ContentTypeUpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group;

/**
 * Mapper for ContentTypeHandler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     * @return array(Type)
     */
    public function extractTypesFromRows( array $rows )
    {
        $types  = array();
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
            if ( !in_array( $groupId, $types[$typeId]->contentTypeGroupIds ) )
            {
                $types[$typeId]->contentTypeGroupIds[] = $groupId;
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

        $type->id                  = (int)$row['ezcontentclass_id'];
        $type->version             = (int)$row['ezcontentclass_version'];
        $type->name                = unserialize( $row['ezcontentclass_serialized_name_list'] );
        $type->description         = unserialize( $row['ezcontentclass_serialized_description_list'] );
        $type->identifier          = $row['ezcontentclass_identifier'];
        $type->created             = (int)$row['ezcontentclass_created'];
        $type->modified            = (int)$row['ezcontentclass_modified'];
        $type->modifierId          = (int)$row['ezcontentclass_modifier_id'];
        $type->creatorId           = (int)$row['ezcontentclass_creator_id'];
        $type->remoteId            = (int)$row['ezcontentclass_remote_id'];
        $type->urlAliasSchema      = (int)$row['ezcontentclass_url_alias_name'];
        $type->nameSchema          = (int)$row['ezcontentclass_contentobject_name'];
        $type->isContainer         = ( $row['ezcontentclass_is_container'] == 1 );
        $type->initialLanguageId   = (int)$row['ezcontentclass_initial_language_id'];
        $type->contentTypeGroupIds = array();
        $type->fieldDefinitions    = array();

        return $type;
    }

    /**
     * Creates a FieldDefinition from the data in $row.
     *
     * @param array $row
     * @return FieldDefinition
     */
    protected function extractFieldFromRow( array $row )
    {
        $field = new FieldDefinition();

        $field->id = (int)$row['ezcontentclass_attribute_id'];
        $field->name = unserialize( $row['ezcontentclass_attribute_serialized_name_list'] );
        $field->description = unserialize( $row['ezcontentclass_attribute_serialized_description_list'] );
        $field->identifier = $row['ezcontentclass_attribute_identifier'];
        $field->fieldGroup = $row['ezcontentclass_attribute_category'];
        $field->fieldType = $row['ezcontentclass_attribute_data_type_string'];
        $field->isTranslatable = ( $row['ezcontentclass_attribute_can_translate'] == 1 );
        $field->isRequired =  ( $row['ezcontentclass_attribute_is_required'] == 1 );
        $field->isInfoCollector = ( $row['ezcontentclass_attribute_is_information_collector'] == 1 );
        // $field->fieldTypeConstra(int)?
        $field->defaultValue = unserialize( $row['ezcontentclass_attribute_serialized_data_text'] );
        // Correct ^?

        return $field;
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param Type $type
     * @param ContentTypeCreateStruct $struct
     * @return void
     */
    public function createTypeFromCreateStruct( ContentTypeCreateStruct $createStruct )
    {
        $type = new Type();

        $type->name                = $createStruct->name;
        $type->description         = $createStruct->description;
        $type->identifier          = $createStruct->identifier;
        $type->created             = $createStruct->created;
        $type->modified            = $createStruct->modified;
        $type->creatorId           = $createStruct->creatorId;
        $type->modifierId          = $createStruct->modifierId;
        $type->remoteId            = $createStruct->remoteId;
        $type->urlAliasSchema      = $createStruct->urlAliasSchema;
        $type->nameSchema          = $createStruct->nameSchema;
        $type->isContainer         = $createStruct->isContainer;
        $type->initialLanguageId   = $createStruct->initialLanguageId;
        $type->contentTypeGroupIds = $createStruct->contentTypeGroupIds;
        $type->fieldDefinitions    = $createStruct->fieldDefinitions;

        return $type;
    }
}
