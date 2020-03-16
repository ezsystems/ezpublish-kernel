<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

/**
 * Content Type Gateway.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    public const CONTENT_TYPE_TO_GROUP_ASSIGNMENT_TABLE = 'ezcontentclass_classgroup';
    public const CONTENT_TYPE_GROUP_TABLE = 'ezcontentclassgroup';
    public const CONTENT_TYPE_TABLE = 'ezcontentclass';
    public const CONTENT_TYPE_NAME_TABLE = 'ezcontentclass_name';
    public const FIELD_DEFINITION_TABLE = 'ezcontentclass_attribute';
    public const MULTILINGUAL_FIELD_DEFINITION_TABLE = 'ezcontentclass_attribute_ml';

    public const CONTENT_TYPE_GROUP_SEQ = 'ezcontentclassgroup_id_seq';
    public const CONTENT_TYPE_SEQ = 'ezcontentclass_id_seq';
    public const FIELD_DEFINITION_SEQ = 'ezcontentclass_attribute_id_seq';

    abstract public function insertGroup(Group $group): int;

    abstract public function updateGroup(GroupUpdateStruct $group): void;

    abstract public function countTypesInGroup(int $groupId): int;

    abstract public function countGroupsForType(int $typeId, int $status): int;

    abstract public function deleteGroup(int $groupId): void;

    /**
     * @param int[] $groupIds
     */
    abstract public function loadGroupData(array $groupIds): array;

    abstract public function loadGroupDataByIdentifier(string $identifier): array;

    abstract public function loadAllGroupsData(): array;

    /**
     * Load data for all Content Types of the given status, belonging to the given Group.
     */
    abstract public function loadTypesDataForGroup(int $groupId, int $status): array;

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the given language does not exist
     */
    abstract public function insertType(Type $type, ?int $typeId = null): int;

    /**
     * Assign a Content Type of the given status (published, draft) to Content Type Group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the given Group does not exist
     */
    abstract public function insertGroupAssignment(int $groupId, int $typeId, int $status): void;

    /**
     * Delete a Group assignments for Content Type of the given status (published, draft).
     */
    abstract public function deleteGroupAssignment(int $groupId, int $typeId, int $status): void;

    /**
     * @param int $id Field Definition ID
     * @param int $status One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    abstract public function loadFieldDefinition(int $id, int $status): array;

    /**
     * Insert a Field Definition into Content Type.
     */
    abstract public function insertFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): int;

    abstract public function deleteFieldDefinition(
        int $typeId,
        int $status,
        int $fieldDefinitionId
    ): void;

    abstract public function updateFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): void;

    /**
     * Update a Content Type based on the given SPI Persistence Type Value Object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if at least one of the used languages does not exist
     */
    abstract public function updateType(int $typeId, int $status, Type $type): void;

    /**
     * Bulk-load an array with data about the given Content Types.
     *
     * @param int[] $typeIds
     */
    abstract public function loadTypesListData(array $typeIds): array;

    abstract public function loadTypeData(int $typeId, int $status): array;

    abstract public function loadTypeDataByIdentifier(string $identifier, int $status): array;

    abstract public function loadTypeDataByRemoteId(string $remoteId, int $status): array;

    abstract public function countInstancesOfType(int $typeId): int;

    /**
     * Permanently delete a Content Type of the given status.
     */
    abstract public function delete(int $typeId, int $status): void;

    abstract public function deleteFieldDefinitionsForType(int $typeId, int $status): void;

    /**
     * Delete a Content Type.
     *
     * Does not delete Field Definitions!
     */
    abstract public function deleteType(int $typeId, int $status): void;

    abstract public function deleteGroupAssignmentsForType(int $typeId, int $status): void;

    /**
     * Publish a Content Type including its Field Definitions.
     */
    abstract public function publishTypeAndFields(
        int $typeId,
        int $sourceStatus,
        int $targetStatus
    ): void;

    abstract public function getSearchableFieldMapData(): array;

    /**
     * Remove Field Definition data from multilingual table.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the given language does not exist
     */
    abstract public function removeFieldDefinitionTranslation(
        int $fieldDefinitionId,
        string $languageCode,
        int $status
    ): void;

    /**
     * Remove items created or modified by User.
     */
    abstract public function removeByUserAndVersion(int $userId, int $version): void;
}
