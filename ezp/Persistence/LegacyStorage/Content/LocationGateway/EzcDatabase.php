<?php
/**
 * File containing the EzcDatabase location gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content\LocationGateway;
use ezp\Persistence\LegacyStorage\Content\LocationGateway;

/**
 * Location gateway implementation using the zeta database component.
 */
class EzcDatabase extends LocationGateway
{
    /**
     * Database handler
     *
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Constants for node assignment op codes
     */
    const
        NODE_ASSIGNMENT_OP_CODE_NOP        = 0,
        NODE_ASSIGNMENT_OP_CODE_EXECUTE    = 1,
        NODE_ASSIGNMENT_OP_CODE_CREATE_NOP = 2,
        NODE_ASSIGNMENT_OP_CODE_CREATE     = 3,
        NODE_ASSIGNMENT_OP_CODE_MOVE_NOP   = 4,
        NODE_ASSIGNMENT_OP_CODE_MOVE       = 5,
        NODE_ASSIGNMENT_OP_CODE_REMOVE_NOP = 6,
        NODE_ASSIGNMENT_OP_CODE_REMOVE     = 7,
        NODE_ASSIGNMENT_OP_CODE_SET_NOP    = 8,
        NODE_ASSIGNMENT_OP_CODE_SET        = 9;

    /**
     * Construct from database handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
    {
        $this->handler = $handler;
    }

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
    public function getBasicNodeData( $nodeId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( 'ezcontentobject_tree' )
            ->where( $query->expr->eq( 'node_id', $query->bindValue( $nodeId ) ) );
        $statement = $query->prepare();
        $statement->execute();
        return $statement->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads the data for the location identified by $locationId.
     *
     * @param int $locationId
     * @return ezp\Persistence\Content\Location
     */
    public function load( $locationId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

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
    public function copySubtree( $sourceId, $destinationParentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

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
    public function moveSubtreeNodes( $fromPathString, $toPathString )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( 'node_id', 'path_string' )
            ->from( 'ezcontentobject_tree' )
            ->where( $query->expr->like( 'path_string', $query->bindValue( $fromPathString . '%' ) ) );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll();
        $oldParentLocation = implode( '/', array_slice( explode( '/', $fromPathString ), 0, -2 ) ) . '/';
        foreach ( $rows as $row )
        {
            $query = $this->handler->createUpdateQuery();
            $query
                ->update( 'ezcontentobject_tree' )
                ->set( 'path_string', $query->bindValue( str_replace( $oldParentLocation, $toPathString, $row['path_string'] ) ) )
                ->where( $query->expr->eq( 'node_id', $query->bindValue( $row['node_id'] ) ) );
            $query->prepare()->execute();
        }
    }

    /**
     * Updated subtree modification time for all nodes on path
     *
     * @param string $pathString
     * @return void
     */
    public function updateSubtreeModificationTime( $pathString )
    {
        $nodes = array_filter( explode( '/', $pathString ) );
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( 'ezcontentobject_tree' )
            ->set( 'modified_subnode', $query->bindValue( time() ) )
            ->where( $query->expr->in( 'node_id', $nodes ) );
        $query->prepare()->execute();
    }

    /**
     * Update node assignement table
     *
     * @param mixed $nodeId
     * @return void
     */
    public function updateNodeAssignement( $contentObjectId, $newParent )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( 'eznode_assignment' )
            ->set( 'parent_node', $query->bindValue( $newParent ) )
            ->set( 'op_code', $query->bindValue( self::NODE_ASSIGNMENT_OP_CODE_MOVE ) )
            ->where( $query->expr->eq( 'contentobject_id', $query->bindValue( $contentObjectId ) ) );
        $query->prepare()->execute();
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    public function hideSubtree( $pathString )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( 'ezcontentobject_tree' )
            ->set( 'is_invisible', $query->bindValue( 1 ) )
            ->set( 'modified_subnode', $query->bindValue( time() ) )
            ->where( $query->expr->like( 'path_string', $query->bindValue( $pathString . '%' ) ) );
        $query->prepare()->execute();

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( 'ezcontentobject_tree' )
            ->set( 'is_hidden', $query->bindValue( 1 ) )
            ->where( $query->expr->eq( 'path_string', $query->bindValue( $pathString ) ) );
        $query->prepare()->execute();
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hidding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param mixed $id
     */
    public function unHide( $id )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

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
    public function swap( $locationId1, $locationId2 )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Updates an existing location position aka priority.
     *
     * @param int $locationId
     * @param int $position
     * @return boolean
     */
    public function updatePosition( $locationId, $position )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Creates a new location for $contentId rooted at $parentId.
     *
     * @param mixed $contentId
     * @param mixed $parentId
     * @return ezp\Persistence\Content\Location
     */
    public function createLocation( $contentId, $parentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

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
    public function removeSubtree( $locationId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param mixed $locationId
     * @return boolean
     */
    public function trashSubtree( $locationId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Returns a trashed subtree to normal state.
     *
     * The affected subtree is now again part of matching content queries.
     *
     * @param mixed $locationId
     * @return boolean
     */
    public function untrashSubtree( $locationId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Set section on all content objects in the subtree
     *
     * @param mixed $locationId
     * @param mixed $sectionId
     * @return boolean
     */
    public function setSectionForSubtree( $locationId, $sectionId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
