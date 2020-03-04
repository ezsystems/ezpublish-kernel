<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * @internal Internal exception conversion layer.
 */
final class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    private $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function insertGroup(Group $group): int
    {
        try {
            return $this->innerGateway->insertGroup($group);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateGroup(GroupUpdateStruct $group): void
    {
        try {
            $this->innerGateway->updateGroup($group);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countTypesInGroup(int $groupId): int
    {
        try {
            return $this->innerGateway->countTypesInGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countGroupsForType(int $typeId, int $status): int
    {
        try {
            return $this->innerGateway->countGroupsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroup(int $groupId): void
    {
        try {
            $this->innerGateway->deleteGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadGroupData(array $groupIds): array
    {
        try {
            return $this->innerGateway->loadGroupData($groupIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadGroupDataByIdentifier(string $identifier): array
    {
        try {
            return $this->innerGateway->loadGroupDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllGroupsData(): array
    {
        try {
            return $this->innerGateway->loadAllGroupsData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypesDataForGroup(int $groupId, int $status): array
    {
        try {
            return $this->innerGateway->loadTypesDataForGroup($groupId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertType(Type $type, ?int $typeId = null): int
    {
        try {
            return $this->innerGateway->insertType($type, $typeId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertGroupAssignment(int $groupId, int $typeId, int $status): void
    {
        try {
            $this->innerGateway->insertGroupAssignment($groupId, $typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroupAssignment(int $groupId, int $typeId, int $status): void
    {
        try {
            $this->innerGateway->deleteGroupAssignment($groupId, $typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadFieldDefinition(int $id, int $status): array
    {
        try {
            return $this->innerGateway->loadFieldDefinition($id, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): int {
        try {
            return $this->innerGateway->insertFieldDefinition(
                $typeId,
                $status,
                $fieldDefinition,
                $storageFieldDef
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteFieldDefinition(
        int $typeId,
        int $status,
        int $fieldDefinitionId
    ): void {
        try {
            $this->innerGateway->deleteFieldDefinition($typeId, $status, $fieldDefinitionId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateFieldDefinition(
        int $typeId,
        int $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ): void {
        try {
            $this->innerGateway->updateFieldDefinition($typeId, $status, $fieldDefinition, $storageFieldDef);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateType(int $typeId, int $status, Type $type): void
    {
        try {
            $this->innerGateway->updateType($typeId, $status, $type);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypesListData(array $typeIds): array
    {
        try {
            return $this->innerGateway->loadTypesListData($typeIds);
        } catch (PDOException | DBALException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypeData($typeId, $status)
    {
        try {
            return $this->innerGateway->loadTypeData($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypeDataByIdentifier(string $identifier, int $status): array
    {
        try {
            return $this->innerGateway->loadTypeDataByIdentifier($identifier, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypeDataByRemoteId($remoteId, $status)
    {
        try {
            return $this->innerGateway->loadTypeDataByRemoteId($remoteId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countInstancesOfType(int $typeId): int
    {
        try {
            return $this->innerGateway->countInstancesOfType($typeId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function delete($typeId, $status)
    {
        try {
            return $this->innerGateway->delete($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteFieldDefinitionsForType(int $typeId, int $status): void
    {
        try {
            $this->innerGateway->deleteFieldDefinitionsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteType(int $typeId, int $status): void
    {
        try {
            $this->innerGateway->deleteType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroupAssignmentsForType(int $typeId, int $status): void
    {
        try {
            $this->innerGateway->deleteGroupAssignmentsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function publishTypeAndFields(int $typeId, int $sourceStatus, int $targetStatus): void
    {
        try {
            $this->innerGateway->publishTypeAndFields($typeId, $sourceStatus, $targetStatus);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getSearchableFieldMapData(): array
    {
        try {
            return $this->innerGateway->getSearchableFieldMapData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeFieldDefinitionTranslation(
        int $fieldDefinitionId,
        string $languageCode,
        int $status
    ): void {
        try {
            $this->innerGateway->removeFieldDefinitionTranslation(
                $fieldDefinitionId,
                $languageCode,
                $status
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeByUserAndVersion(int $userId, int $version): void
    {
        try {
            $this->innerGateway->removeByUserAndVersion($userId, $version);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
