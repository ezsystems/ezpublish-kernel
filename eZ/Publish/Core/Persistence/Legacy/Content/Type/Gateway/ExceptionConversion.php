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

    public function insertGroup(Group $group)
    {
        try {
            return $this->innerGateway->insertGroup($group);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateGroup(GroupUpdateStruct $group)
    {
        try {
            return $this->innerGateway->updateGroup($group);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countTypesInGroup($groupId)
    {
        try {
            return $this->innerGateway->countTypesInGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countGroupsForType($typeId, $status)
    {
        try {
            return $this->innerGateway->countGroupsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroup($groupId)
    {
        try {
            return $this->innerGateway->deleteGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadGroupData(array $groupIds)
    {
        try {
            return $this->innerGateway->loadGroupData($groupIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadGroupDataByIdentifier($identifier)
    {
        try {
            return $this->innerGateway->loadGroupDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllGroupsData()
    {
        try {
            return $this->innerGateway->loadAllGroupsData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTypesDataForGroup($groupId, $status)
    {
        try {
            return $this->innerGateway->loadTypesDataForGroup($groupId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertType(Type $type, $typeId = null)
    {
        try {
            return $this->innerGateway->insertType($type, $typeId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertGroupAssignment($typeId, $status, $groupId)
    {
        try {
            return $this->innerGateway->insertGroupAssignment($typeId, $status, $groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroupAssignment($groupId, $typeId, $status)
    {
        try {
            return $this->innerGateway->deleteGroupAssignment($groupId, $typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadFieldDefinition($id, $status)
    {
        try {
            return $this->innerGateway->loadFieldDefinition($id, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ) {
        try {
            return $this->innerGateway->insertFieldDefinition($typeId, $status, $fieldDefinition, $storageFieldDef);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteFieldDefinition($typeId, $status, $fieldDefinitionId)
    {
        try {
            return $this->innerGateway->deleteFieldDefinition($typeId, $status, $fieldDefinitionId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateFieldDefinition(
        $typeId,
        $status,
        FieldDefinition $fieldDefinition,
        StorageFieldDefinition $storageFieldDef
    ) {
        try {
            return $this->innerGateway->updateFieldDefinition($typeId, $status, $fieldDefinition, $storageFieldDef);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateType($typeId, $status, Type $type)
    {
        try {
            return $this->innerGateway->updateType($typeId, $status, $type);
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

    public function loadTypeDataByIdentifier($identifier, $status)
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

    public function countInstancesOfType($typeId)
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

    public function deleteFieldDefinitionsForType($typeId, $status)
    {
        try {
            return $this->innerGateway->deleteFieldDefinitionsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteType($typeId, $status)
    {
        try {
            return $this->innerGateway->deleteType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteGroupAssignmentsForType($typeId, $status)
    {
        try {
            return $this->innerGateway->deleteGroupAssignmentsForType($typeId, $status);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function publishTypeAndFields($typeId, $sourceStatus, $targetStatus)
    {
        try {
            return $this->innerGateway->publishTypeAndFields($typeId, $sourceStatus, $targetStatus);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getSearchableFieldMapData()
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
