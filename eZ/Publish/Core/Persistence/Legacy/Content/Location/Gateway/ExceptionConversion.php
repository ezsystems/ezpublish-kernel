<?php

/**
 * File containing the Location Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
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

    /**
     * {@inheritdoc}
     */
    public function getBasicNodeData($nodeId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getBasicNodeData($nodeId, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeDataList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable
    {
        try {
            return $this->innerGateway->getNodeDataList($locationIds, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBasicNodeDataByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getBasicNodeDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Loads data for all Locations for $contentId, optionally only in the
     * subtree starting at $rootLocationId.
     *
     * @param int $contentId
     * @param int $rootLocationId
     *
     * @return array
     */
    public function loadLocationDataByContent($contentId, $rootLocationId = null)
    {
        try {
            return $this->innerGateway->loadLocationDataByContent($contentId, $rootLocationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @see \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway::loadParentLocationsDataForDraftContent
     */
    public function loadParentLocationsDataForDraftContent($contentId)
    {
        try {
            return $this->innerGateway->loadParentLocationsDataForDraftContent($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Find all content in the given subtree.
     *
     * @param mixed $sourceId
     * @param bool $onlyIds
     *
     * @return array
     */
    public function getSubtreeContent($sourceId, $onlyIds = false)
    {
        try {
            return $this->innerGateway->getSubtreeContent($sourceId, $onlyIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns data for the first level children of the location identified by given $locationId.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    public function getChildren($locationId)
    {
        try {
            return $this->innerGateway->getChildren($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Update path strings to move nodes in the ezcontentobject_tree table.
     *
     * This query can likely be optimized to use some more advanced string
     * operations, which then depend on the respective database.
     *
     * @todo optimize
     *
     * @param array $fromPathString
     * @param array $toPathString
     */
    public function moveSubtreeNodes(array $fromPathString, array $toPathString)
    {
        try {
            return $this->innerGateway->moveSubtreeNodes($fromPathString, $toPathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Updated subtree modification time for all nodes on path.
     *
     * @param string $pathString
     * @param int|null $timestamp
     */
    public function updateSubtreeModificationTime($pathString, $timestamp = null)
    {
        try {
            return $this->innerGateway->updateSubtreeModificationTime($pathString, $timestamp);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Update node assignment table.
     *
     * @param int $contentObjectId
     * @param int $oldParent
     * @param int $newParent
     * @param int $opcode
     */
    public function updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode)
    {
        try {
            return $this->innerGateway->updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Create locations from node assignments.
     *
     * Convert existing node assignments into real locations.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function createLocationsFromNodeAssignments($contentId, $versionNo)
    {
        try {
            return $this->innerGateway->createLocationsFromNodeAssignments($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Updates all Locations of content identified with $contentId with $versionNo.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    public function updateLocationsContentVersionNo($contentId, $versionNo)
    {
        try {
            return $this->innerGateway->updateLocationsContentVersionNo($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    public function hideSubtree($pathString)
    {
        try {
            return $this->innerGateway->hideSubtree($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hiding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    public function unHideSubtree($pathString)
    {
        try {
            return $this->innerGateway->unHideSubtree($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @param string $pathString
     **/
    public function setNodeWithChildrenInvisible(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeWithChildrenInvisible($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @param string $pathString
     **/
    public function setNodeHidden(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeHidden($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @param string $pathString
     **/
    public function setNodeWithChildrenVisible(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeWithChildrenVisible($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * @param string $pathString
     **/
    public function setNodeUnhidden(string $pathString): void
    {
        try {
            $this->innerGateway->setNodeUnhidden($pathString);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Swaps the content object being pointed to by a location object.
     *
     * Make the location identified by $locationId1 refer to the Content
     * referred to by $locationId2 and vice versa.
     *
     * @param int $locationId1
     * @param int $locationId2
     *
     * @return bool
     */
    public function swap(int $locationId1, int $locationId2): bool
    {
        try {
            return $this->innerGateway->swap($locationId1, $locationId2);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Creates a new location in given $parentNode.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function create(CreateStruct $createStruct, array $parentNode)
    {
        try {
            return $this->innerGateway->create($createStruct, $parentNode);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Create an entry in the node assignment table.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     */
    public function createNodeAssignment(CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP)
    {
        try {
            return $this->innerGateway->createNodeAssignment($createStruct, $parentNodeId, $type);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Deletes node assignment for given $contentId and $versionNo.
     *
     * @param int $contentId
     * @param int $versionNo
     */
    public function deleteNodeAssignment($contentId, $versionNo = null)
    {
        try {
            return $this->innerGateway->deleteNodeAssignment($contentId, $versionNo);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Updates an existing location.
     *
     * Will not throw anything if location id is invalid or no entries are affected.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     */
    public function update(UpdateStruct $location, $locationId)
    {
        try {
            return $this->innerGateway->update($location, $locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Updates path identification string for given $locationId.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId
     * @param string $text
     */
    public function updatePathIdentificationString($locationId, $parentLocationId, $text)
    {
        try {
            return $this->innerGateway->updatePathIdentificationString($locationId, $parentLocationId, $text);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id).
     *
     * @param mixed $locationId
     */
    public function removeLocation($locationId)
    {
        try {
            return $this->innerGateway->removeLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns id of the next in line node to be set as a new main node.
     *
     * This returns lowest node id for content identified by $contentId, and not of
     * the node identified by given $locationId (current main node).
     * Assumes that content has more than one location.
     *
     * @param mixed $contentId
     * @param mixed $locationId
     *
     * @return array
     */
    public function getFallbackMainNodeData($contentId, $locationId)
    {
        try {
            return $this->innerGateway->getFallbackMainNodeData($contentId, $locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Sends a single location identified by given $locationId to the trash.
     *
     * The associated content object is left untouched.
     *
     * @param mixed $locationId
     *
     * @return bool
     */
    public function trashLocation($locationId)
    {
        try {
            return $this->innerGateway->trashLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns a trashed location to normal state.
     *
     * Recreates the originally trashed location in the new position. If no new
     * position has been specified, it will be tried to re-create the location
     * at the old position. If this is not possible ( because the old location
     * does not exist any more) and exception is thrown.
     *
     * @param mixed $locationId
     * @param mixed $newParentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function untrashLocation($locationId, $newParentId = null)
    {
        try {
            return $this->innerGateway->untrashLocation($locationId, $newParentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Loads trash data specified by location ID.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    public function loadTrashByLocation($locationId)
    {
        try {
            return $this->innerGateway->loadTrashByLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     */
    public function cleanupTrash()
    {
        try {
            return $this->innerGateway->cleanupTrash();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Lists trashed items.
     * Returns entries from ezcontentobject_trash.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     *
     * @return array
     */
    public function listTrashed($offset, $limit, array $sort = null)
    {
        try {
            return $this->innerGateway->listTrashed($offset, $limit, $sort);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Removes trashed element identified by $id from trash.
     * Will NOT remove associated content object nor attributes.
     *
     * @param int $id The trashed location Id
     */
    public function removeElementFromTrash($id)
    {
        try {
            return $this->innerGateway->removeElementFromTrash($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Set section on all content objects in the subtree.
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     *
     * @return bool
     */
    public function setSectionForSubtree($pathString, $sectionId)
    {
        try {
            return $this->innerGateway->setSectionForSubtree($pathString, $sectionId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Returns how many locations given content object identified by $contentId has.
     *
     * @param int $contentId
     *
     * @return int
     */
    public function countLocationsByContentId($contentId)
    {
        try {
            return $this->innerGateway->countLocationsByContentId($contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Changes main location of content identified by given $contentId to location identified by given $locationId.
     *
     * Updates ezcontentobject_tree table for the given $contentId and eznode_assignment table for the given
     * $contentId, $parentLocationId and $versionNo
     *
     * @param mixed $contentId
     * @param mixed $locationId
     * @param mixed $versionNo version number, needed to update eznode_assignment table
     * @param mixed $parentLocationId parent location of location identified by $locationId, needed to update
     *        eznode_assignment table
     */
    public function changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId)
    {
        try {
            return $this->innerGateway->changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Get the total number of all Locations, except the Root node.
     *
     * @see loadAllLocationsData
     *
     * @return int
     */
    public function countAllLocations()
    {
        try {
            return $this->innerGateway->countAllLocations();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    /**
     * Load data of every Location, except the Root node.
     *
     * @param int $offset Paginator offset
     * @param int $limit Paginator limit
     *
     * @return array
     */
    public function loadAllLocationsData($offset, $limit)
    {
        try {
            return $this->innerGateway->loadAllLocationsData($offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
