<?php

/**
 * File containing the Location Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Location;

use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;

/**
 * Base class for location gateways.
 */
abstract class Gateway
{
    /**
     * Constants for node assignment op codes.
     */
    const NODE_ASSIGNMENT_OP_CODE_NOP = 0;
    const NODE_ASSIGNMENT_OP_CODE_EXECUTE = 1;
    const NODE_ASSIGNMENT_OP_CODE_CREATE_NOP = 2;
    const NODE_ASSIGNMENT_OP_CODE_CREATE = 3;
    const NODE_ASSIGNMENT_OP_CODE_MOVE_NOP = 4;
    const NODE_ASSIGNMENT_OP_CODE_MOVE = 5;
    const NODE_ASSIGNMENT_OP_CODE_REMOVE_NOP = 6;
    const NODE_ASSIGNMENT_OP_CODE_REMOVE = 7;
    const NODE_ASSIGNMENT_OP_CODE_SET_NOP = 8;
    const NODE_ASSIGNMENT_OP_CODE_SET = 9;

    /**
     * Returns an array with basic node data.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param mixed $nodeId
     * @param string[]|null $translations
     * @param bool $useAlwaysAvailable Respect always available flag on content when filtering on $translations.
     *
     * @return array
     */
    abstract public function getBasicNodeData($nodeId, array $translations = null, bool $useAlwaysAvailable = true);

    /**
     * Returns an array with node data for several locations.
     *
     * @param array $locationIds
     * @param string[]|null $translations
     * @param bool $useAlwaysAvailable Respect always available flag on content when filtering on $translations.
     *
     * @return array
     */
    abstract public function getNodeDataList(array $locationIds, array $translations = null, bool $useAlwaysAvailable = true): iterable;

    /**
     * Returns an array with basic node data for the node with $remoteId.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param mixed $remoteId
     * @param string[]|null $translations
     * @param bool $useAlwaysAvailable Respect always available flag on content when filtering on $translations.
     *
     * @return array
     */
    abstract public function getBasicNodeDataByRemoteId($remoteId, array $translations = null, bool $useAlwaysAvailable = true);

    /**
     * Loads data for all Locations for $contentId, optionally only in the
     * subtree starting at $rootLocationId.
     *
     * @param int $contentId
     * @param int $rootLocationId
     *
     * @return array
     */
    abstract public function loadLocationDataByContent($contentId, $rootLocationId = null);

    /**
     * Loads data for all parent Locations for unpublished Content by given $contentId.
     *
     * @param mixed $contentId
     *
     * @return array
     */
    abstract public function loadParentLocationsDataForDraftContent($contentId);

    /**
     * Find all content in the given subtree.
     *
     * @param mixed $sourceId
     * @param bool $onlyIds
     *
     * @return array
     */
    abstract public function getSubtreeContent($sourceId, $onlyIds = false);

    /**
     * Returns data for the first level children of the location identified by given $locationId.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    abstract public function getChildren($locationId);

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
    abstract public function moveSubtreeNodes(array $fromPathString, array $toPathString);

    /**
     * Updated subtree modification time for all nodes on path.
     *
     * @param string $pathString
     * @param int|null $timestamp
     */
    abstract public function updateSubtreeModificationTime($pathString, $timestamp = null);

    /**
     * Update node assignment table.
     *
     * @param int $contentObjectId
     * @param int $oldParent
     * @param int $newParent
     * @param int $opcode
     */
    abstract public function updateNodeAssignment($contentObjectId, $oldParent, $newParent, $opcode);

    /**
     * Create locations from node assignments.
     *
     * Convert existing node assignments into real locations.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    abstract public function createLocationsFromNodeAssignments($contentId, $versionNo);

    /**
     * Updates all Locations of content identified with $contentId with $versionNo.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     */
    abstract public function updateLocationsContentVersionNo($contentId, $versionNo);

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    abstract public function hideSubtree($pathString);

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hiding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    abstract public function unHideSubtree($pathString);

    /**
     * @param string $pathString
     **/
    abstract public function setNodeWithChildrenInvisible(string $pathString): void;

    /**
     * @param string $pathString
     **/
    abstract public function setNodeWithChildrenVisible(string $pathString): void;

    /**
     * @param string $pathString
     **/
    abstract public function setNodeHidden(string $pathString): void;

    /**
     * @param string $pathString
     **/
    abstract public function setNodeUnhidden(string $pathString): void;

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
    abstract public function swap(int $locationId1, int $locationId2): bool;

    /**
     * Creates a new location in given $parentNode.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    abstract public function create(CreateStruct $createStruct, array $parentNode);

    /**
     * Create an entry in the node assignment table.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     */
    abstract public function createNodeAssignment(CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP);

    /**
     * Deletes node assignment for given $contentId and $versionNo.
     *
     * @param int $contentId
     * @param int $versionNo
     */
    abstract public function deleteNodeAssignment($contentId, $versionNo = null);

    /**
     * Updates an existing location.
     *
     * Will not throw anything if location id is invalid or no entries are affected.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     */
    abstract public function update(UpdateStruct $location, $locationId);

    /**
     * Updates path identification string for given $locationId.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId
     * @param string $text
     */
    abstract public function updatePathIdentificationString($locationId, $parentLocationId, $text);

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id).
     *
     * @param mixed $locationId
     */
    abstract public function removeLocation($locationId);

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
    abstract public function getFallbackMainNodeData($contentId, $locationId);

    /**
     * Sends a single location identified by given $locationId to the trash.
     *
     * The associated content object is left untouched.
     *
     * @param mixed $locationId
     *
     * @return bool
     */
    abstract public function trashLocation($locationId);

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
    abstract public function untrashLocation($locationId, $newParentId = null);

    /**
     * Loads trash data specified by location ID.
     *
     * @param mixed $locationId
     *
     * @return array
     */
    abstract public function loadTrashByLocation($locationId);

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     */
    abstract public function cleanupTrash();

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
    abstract public function listTrashed($offset, $limit, array $sort = null);

    /**
     * Removes trashed element identified by $id from trash.
     * Will NOT remove associated content object nor attributes.
     *
     * @param int $id The trashed location Id
     */
    abstract public function removeElementFromTrash($id);

    /**
     * Set section on all content objects in the subtree.
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     *
     * @return bool
     */
    abstract public function setSectionForSubtree($pathString, $sectionId);

    /**
     * Returns how many locations given content object identified by $contentId has.
     *
     * @param int $contentId
     *
     * @return int
     */
    abstract public function countLocationsByContentId($contentId);

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
    abstract public function changeMainLocation($contentId, $locationId, $versionNo, $parentLocationId);

    /**
     * Get the total number of all Locations, except the Root node.
     *
     * @see loadAllLocationsData
     *
     * @return int
     */
    abstract public function countAllLocations();

    /**
     * Load data of every Location, except the Root node.
     *
     * @param int $offset Paginator offset
     * @param int $limit Paginator limit
     *
     * @return array
     */
    abstract public function loadAllLocationsData($offset, $limit);
}
