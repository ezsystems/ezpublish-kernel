<?php
/**
 * File containing the Location Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;

/**
 * Base class for location gateways.
 */
abstract class Gateway
{
    /**
     * Constants for node assignment op codes
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
     * Returns an array with basic node data
     *
     * We might want to cache this, since this method is used by about every
     * method in the location handler.
     *
     * @optimze
     * @param mixed $nodeId
     * @return array
     */
    abstract public function getBasicNodeData( $nodeId );

    /**
     * Returns an array with basic node data for the node with $remoteId
     *
     * @optimze
     * @param mixed $remoteId
     * @return array
     */
    abstract public function getBasicNodeDataByRemoteId( $remoteId );

    /**
     * Loads data for all Locations for $contentId, optionally only in the
     * subtree starting at $rootLocationId
     *
     * @param int $contentId
     * @param int $rootLocationId
     * @return array
     */
    abstract public function loadLocationDataByContent( $contentId, $rootLocationId = null );

    /**
     * Find all content in the given subtree
     *
     * @param mixed $sourceId
     * @return array
     */
    abstract public function getSubtreeContent( $sourceId );

    /**
     * Returns data for the first level children of the location identified by given $locationId
     *
     * @param mixed $locationId
     * @return array
     */
    abstract public function getChildren( $locationId );

    /**
     * Update path strings to move nodes in the ezcontentobject_tree table
     *
     * This query can likely be optimized to use some more advanced string
     * operations, which then depend on the respective database.
     *
     * @optimize
     * @param string $fromPathString
     * @param string $toPathString
     * @return void
     */
    abstract public function moveSubtreeNodes( $fromPathString, $toPathString );

    /**
     * Updated subtree modification time for all nodes on path
     *
     * @param string $pathString
     * @param int|null $timestamp
     *
     * @return void
     */
    abstract public function updateSubtreeModificationTime( $pathString, $timestamp = null );

    /**
     * Update node assignment table
     *
     * @param int $contentObjectId
     * @param int $oldParent
     * @param int $newParent
     * @param int $opcode
     * @return void
     */
    abstract public function updateNodeAssignment( $contentObjectId, $oldParent, $newParent, $opcode );

    /**
     * Create locations from node assignments
     *
     * Convert existing node assignments into real locations.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     * @return void
     */
    abstract public function createLocationsFromNodeAssignments( $contentId, $versionNo );

    /**
     * Updates all Locations of content identified with $contentId with $versionNo
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     *
     * @return void
     */
    abstract public function updateLocationsContentVersionNo( $contentId, $versionNo );

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    abstract public function hideSubtree( $pathString );

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hidding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    abstract public function unHideSubtree( $pathString );

    /**
     * Swaps the content object being pointed to by a location object.
     *
     * Make the location identified by $locationId1 refer to the Content
     * referred to by $locationId2 and vice versa.
     *
     * @param mixed $locationId1
     * @param mixed $locationId2
     * @return boolean
     */
    abstract public function swap( $locationId1, $locationId2 );

    /**
     * Creates a new location in given $parentNode
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    abstract public function create( CreateStruct $createStruct, array $parentNode );

    /**
     * Create an entry in the node assignment table
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     * @return void
     */
    abstract public function createNodeAssignment( CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP );

    /**
     * Deletes node assignment for given $contentId and $versionNo
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return void
     */
    abstract public function deleteNodeAssignment( $contentId, $versionNo = null );

    /**
     * Updates an existing location.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     * @return boolean
     */
    abstract public function update( UpdateStruct $location, $locationId );

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id)
     *
     * @param mixed $locationId
     */
    abstract public function removeLocation( $locationId );

    /**
     * Returns id of the next in line node to be set as a new main node
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
    abstract public function getFallbackMainNodeData( $contentId, $locationId );

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param string $pathString
     * @return boolean
     */
    abstract public function trashSubtree( $pathString );

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
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    abstract public function untrashLocation( $locationId, $newParentId = null );

    /**
     * Load trash data specified by location ID
     *
     * @param mixed $locationId
     * @return array
     */
    abstract public function loadTrashByLocation( $locationId );

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     *
     * @return void
     */
    abstract public function cleanupTrash();

    /**
     * Lists trashed items.
     * Returns entries from ezcontentobject_trash.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     * @return array
     */
    abstract public function listTrashed( $offset, $limit, array $sort = null );

    /**
     * Removes trashed element identified by $id from trash.
     * Will NOT remove associated content object nor attributes.
     *
     * @param int $id The trashed location Id
     * @return void
     */
    abstract public function removeElementFromTrash( $id );

    /**
     * Set section on all content objects in the subtree
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     * @return boolean
     */
    abstract public function setSectionForSubtree( $pathString, $sectionId );

    /**
     * Returns how many locations given content object identified by $contentId has
     *
     * @param int $contentId
     * @return int
     */
    abstract public function countLocationsByContentId( $contentId );

    /**
     * Changes main location of content identified by given $contentId to location identified by given $locationId
     *
     * Updates ezcontentobject_tree table for the given $contentId and eznode_assignment table for the given
     * $contentId, $parentLocationId and $versionNo
     *
     * @param mixed $contentId
     * @param mixed $locationId
     * @param mixed $versionNo version number, needed to update eznode_assignment table
     * @param mixed $parentLocationId parent location of location identified by $locationId, needed to update
     *        eznode_assignment table
     *
     * @return void
     */
    abstract public function changeMainLocation( $contentId, $locationId, $versionNo, $parentLocationId );
}
