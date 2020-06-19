<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * Base class for location gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function getBasicNodeData($nodeId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getBasicNodeData($nodeId, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getNodeDataList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        try {
            return $this->innerGateway->getNodeDataList($locationIds, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getBasicNodeDataByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getBasicNodeDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadLocationDataByContent($contentId, $rootLocationId = null)
    {
        try {
            return $this->innerGateway->loadLocationDataByContent($contentId, $rootLocationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadLocationDataByTrashContent(int $contentId, ?int $rootLocationId = null): array
    {
        try {
            return $this->innerGateway->loadLocationDataByTrashContent($contentId, $rootLocationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadParentLocationsDataForDraftContent($contentId)
    {
        try {
            return $this->innerGateway->loadParentLocationsDataForDraftContent($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getSubtreeContent($sourceId, $onlyIds = false)
    {
        try {
            return $this->innerGateway->getSubtreeContent($sourceId, $onlyIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getChildren($locationId)
    {
        try {
            return $this->innerGateway->getChildren($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function moveSubtreeNodes(array $fromPathString, array $toPathString)
    {
        try {
            return $this->innerGateway->moveSubtreeNodes($fromPathString, $toPathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateSubtreeModificationTime($pathString, $timestamp = null)
    {
        try {
            return $this->innerGateway->updateSubtreeModificationTime($pathString, $timestamp);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode)
    {
        try {
            return $this->innerGateway->updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function createLocationsFromNodeAssignments($contentId, $versionNo)
    {
        try {
            return $this->innerGateway->createLocationsFromNodeAssignments($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateLocationsContentVersionNo($contentId, $versionNo)
    {
        try {
            return $this->innerGateway->updateLocationsContentVersionNo($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function hideSubtree($pathString)
    {
        try {
            return $this->innerGateway->hideSubtree($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function unHideSubtree($pathString)
    {
        try {
            return $this->innerGateway->unHideSubtree($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setNodeWithChildrenInvisible(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeWithChildrenInvisible($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setNodeHidden(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeHidden($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setNodeWithChildrenVisible(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeWithChildrenVisible($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setNodeUnhidden(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeUnhidden($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function swap(int $locationId1, int $locationId2): bool
    {
        try {
            return $this->innerGateway->swap($locationId1, $locationId2);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function create(CreateStruct $createStruct, array $parentNode)
    {
        try {
            return $this->innerGateway->create($createStruct, $parentNode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function createNodeAssignment(CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP)
    {
        try {
            return $this->innerGateway->createNodeAssignment($createStruct, $parentNodeId, $type);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteNodeAssignment($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteNodeAssignment($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function update(UpdateStruct $location, $locationId)
    {
        try {
            return $this->innerGateway->update($location, $locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updatePathIdentificationString($locationId, $parentLocationId, $text)
    {
        try {
            return $this->innerGateway->updatePathIdentificationString($locationId, $parentLocationId, $text);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeLocation($locationId)
    {
        try {
            return $this->innerGateway->removeLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getFallbackMainNodeData($contentId, $locationId)
    {
        try {
            return $this->innerGateway->getFallbackMainNodeData($contentId, $locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function trashLocation($locationId)
    {
        try {
            return $this->innerGateway->trashLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function untrashLocation($locationId, $newParentId = null)
    {
        try {
            return $this->innerGateway->untrashLocation($locationId, $newParentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadTrashByLocation($locationId)
    {
        try {
            return $this->innerGateway->loadTrashByLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function cleanupTrash()
    {
        try {
            return $this->innerGateway->cleanupTrash();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function listTrashed($offset, $limit, array $sort = null)
    {
        try {
            return $this->innerGateway->listTrashed($offset, $limit, $sort);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countTrashed(): int
    {
        try {
            return $this->innerGateway->countTrashed();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeElementFromTrash($id)
    {
        try {
            return $this->innerGateway->removeElementFromTrash($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setSectionForSubtree($pathString, $sectionId)
    {
        try {
            return $this->innerGateway->setSectionForSubtree($pathString, $sectionId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countLocationsByContentId($contentId)
    {
        try {
            return $this->innerGateway->countLocationsByContentId($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId)
    {
        try {
            return $this->innerGateway->changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countAllLocations()
    {
        try {
            return $this->innerGateway->countAllLocations();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllLocationsData($offset, $limit)
    {
        try {
            return $this->innerGateway->loadAllLocationsData($offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
