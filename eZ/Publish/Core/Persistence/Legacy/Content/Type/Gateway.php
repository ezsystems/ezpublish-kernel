<?php

/**
 * File containing the Content Type Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

/**
 * Base class for content type gateways.
 */
abstract class Gateway
{
    /**
     * Inserts the given $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group $group
     *
     * @return mixed Group ID
     */
    abstract public function insertGroup(Group $group);

    /**
     * Updates a group with data in $group.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct $group
     */
    abstract public function updateGroup(GroupUpdateStruct $group);

    /**
     * Returns the number of types in a certain group.
     *
     * @param int $groupId
     *
     * @return int
     */
    abstract public function countTypesInGroup($groupId);

    /**
     * Returns the number of Groups the type is assigned to.
     *
     * @param int $typeId
     * @param int $status
     *
     * @return int
     */
    abstract public function countGroupsForType($typeId, $status);

    /**
     * Deletes the Group with the given $groupId.
     *
     * @param int $groupId
     */
    abstract public function deleteGroup($groupId);

    /**
     * Returns an array with data about the Group(s) with $groupIds.
     *
     * @param int[] $groupIds
     *
     * @return array
     */
    abstract public function loadGroupData(array $groupIds);

    /**
     * Returns an array with data about the Group with $identifier.
     *
     * @param string $identifier
     *
     * @return array
     */
    abstract public function loadGroupDataByIdentifier($identifier);

    /**
     * Returns an array with data about all Group objects.
     *
     * @return array
     */
    abstract public function loadAllGroupsData();

    /**
     * Loads data for all Types in $status in $groupId.
     *
     * @param mixed $groupId
     * @param int $status
     *
     * @return string[][]
     */
    abstract public function loadTypesDataForGroup($groupId, $status);

    /**
     * Inserts a new content type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     * @param mixed|null $typeId
     *
     * @return mixed Type ID
     */
    abstract public function insertType(Type $type, $typeId = null);

    /**
     * Insert assignment of $typeId to $groupId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $groupId
     */
    abstract public function insertGroupAssignment($typeId, $status, $groupId);

    /**
     * Deletes a group assignments for a Type.
     *
     * @param mixed $groupId
     * @param mixed $typeId
     * @param int $status
     */
    abstract public function deleteGroupAssignment($groupId, $typeId, $status);

    /**
     * Loads an array with data about field definition referred $id and $status.
     *
     * @param mixed $id field definition id
     * @param int $status field definition status
     *
     * @return array Data rows.
     */
    abstract public function loadFieldDefinition($id, $status);

    /**
     * Inserts a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     *
     * @return mixed Field definition ID
     */
    abstract public function insertFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    );

    /**
     * Deletes a field definition.
     *
     * @param mixed $typeId
     * @param int $status
     * @param mixed $fieldDefinitionId
     */
    abstract public function deleteFieldDefinition($typeId, $status, $fieldDefinitionId);

    /**
     * Updates a $fieldDefinition for $typeId.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageFieldDef
     */
    abstract public function updateFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    );

    /**
     * Update a type with $updateStruct.
     *
     * @param mixed $typeId
     * @param int $status
     * @param \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct $updateStruct
     */
    abstract public function updateType($typeId, $status, UpdateStruct $updateStruct);

    /**
     * Loads an array with data about several Types in defined status..
     *
     * @param array $typeIds
     *
     * @return array Data rows.
     */
    abstract public function loadTypesDataList(array $typeIds): array;

    /**
     * Loads an array with data about $typeId in $status.
     *
     * @param mixed $typeId
     * @param int $status
     *
     * @return array Data rows.
     */
    abstract public function loadTypeData($typeId, $status);

    /**
     * Loads an array with data about the type referred to by $identifier in
     * $status.
     *
     * @param string $identifier
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    abstract public function loadTypeDataByIdentifier($identifier, $status);

    /**
     * Loads an array with data about the type referred to by $remoteId in
     * $status.
     *
     * @param mixed $remoteId
     * @param int $status
     *
     * @return array(int=>array(string=>mixed)) Data rows.
     */
    abstract public function loadTypeDataByRemoteId($remoteId, $status);

    /**
     * Counts the number of instances that exists of the identified type.
     *
     * @param int $typeId
     *
     * @return int
     */
    abstract public function countInstancesOfType($typeId);

    /**
     * Deletes a Type completely.
     *
     * @param mixed $typeId
     * @param int $status
     */
    abstract public function delete($typeId, $status);

    /**
     * Deletes all field definitions of a Type.
     *
     * @param mixed $typeId
     * @param int $status
     */
    abstract public function deleteFieldDefinitionsForType($typeId, $status);

    /**
     * Deletes a the Type.
     *
     * Does no delete the field definitions!
     *
     * @param mixed $typeId
     * @param int $status
     */
    abstract public function deleteType($typeId, $status);

    /**
     * Deletes all group assignments for a Type.
     *
     * @param mixed $typeId
     * @param int $status
     */
    abstract public function deleteGroupAssignmentsForType($typeId, $status);

    /**
     * Publishes the Type with $typeId from $sourceVersion to $targetVersion,
     * including its fields.
     *
     * @param int $typeId
     * @param int $sourceStatus
     * @param int $targetStatus
     */
    abstract public function publishTypeAndFields($typeId, $sourceStatus, $targetStatus);

    /**
     * Returns searchable fields mapping data.
     *
     * @return array
     */
    abstract public function getSearchableFieldMapData();
}
