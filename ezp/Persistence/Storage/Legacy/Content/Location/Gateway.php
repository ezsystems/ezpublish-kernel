<?php
/**
 * File containing the Location Gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location;
use ezp\Persistence\Content,
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
     * Copy location object identified by $sourceId, into destination identified by $destinationParentId.
     *
     * Performs a deep copy of the location identified by $sourceId and all of
     * its child locations, copying the most recent published content object
     * for each location to a new content object without any additional version
     * information. Relations are not copied. URLs are not touched at all.
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     * @return Location the newly created Location.
     */
    abstract public function copySubtree( $sourceId, $destinationParentId );

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
     * @param mixed $nodeId
     * @return void
     */
    abstract public function updateNodeAssignement( $contentObjectId, $newParent );

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
     * Updates an existing location priority.
     *
     * @param int $locationId
     * @param int $priority
     * @return boolean
     */
    abstract public function updatePriority( $locationId, $priority );

    /**
     * Creates a new location in given $parentNode
     *
     * @param \ezp\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @return \ezp\Persistence\Content\Location
     */
    abstract public function createLocation( CreateStruct $createStruct, array $parentNode );

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
     * Returns a trashed subtree to normal state.
     *
     * The affected subtree is now again part of matching content queries.
     *
     * @param mixed $locationId
     * @return boolean
     */
    abstract public function untrashSubtree( $locationId );

    /**
     * Set section on all content objects in the subtree
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     * @return boolean
     */
    abstract public function setSectionForSubtree( $pathString, $sectionId );
}
