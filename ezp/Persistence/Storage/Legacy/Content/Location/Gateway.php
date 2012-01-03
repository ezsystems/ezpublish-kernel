<?php
/**
 * File containing the Location Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location;
use ezp\Persistence\Content,
    ezp\Persistence\Content\Location\UpdateStruct,
    ezp\Persistence\Content\Location\CreateStruct;

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
     * Find all content in the given subtree
     *
     * @param mixed $sourceId
     * @return array
     */
    abstract public function getSubtreeContent( $sourceId );

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
     * @return void
     */
    abstract public function updateSubtreeModificationTime( $pathString );

    /**
     * Update node assignement table
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
     * @param \ezp\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @return \ezp\Persistence\Content\Location
     */
    abstract public function create( CreateStruct $createStruct, array $parentNode );

    /**
     * Create an entry in the node assignment table
     *
     * @param CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     * @return void
     */
    abstract public function createNodeAssignment( CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP );

    /**
     * Updates an existing location.
     *
     * @param \ezp\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     * @return boolean
     */
    abstract public function update( UpdateStruct $location, $locationId );

    /**
     * Removes all Locations under and includin $locationId.
     *
     * Performs a recursive delete on the location identified by $locationId,
     * including all of its child locations. Content which is not referred to
     * by any other location is automatically removed. Content which looses its
     * main Location will get the first of its other Locations assigned as the
     * new main Location.
     *
     * @param mixed $locationId
     * @return boolean
     */
    abstract public function removeSubtree( $locationId );

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     * @return boolean
     */
    abstract public function trashSubtree( $locationId );

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
     * @return boolean
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
     * Set section on all content objects in the subtree
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     * @return boolean
     */
    abstract public function setSectionForSubtree( $pathString, $sectionId );
}
