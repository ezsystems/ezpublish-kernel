<?php
/**
 * File containing the EzcDatabase location gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Location\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\UpdateStruct,
    ezp\Persistence\Content\Location\CreateStruct;

/**
 * Location gateway implementation using the zeta database component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param EzcDbHandler $handler
     * @return void
     */
    public function __construct( EzcDbHandler $handler )
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
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $nodeId )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new \ezp\Base\Exception\NotFound( 'location', $nodeId );
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
            ->select(
                $this->handler->quoteColumn( 'node_id' ),
                $this->handler->quoteColumn( 'path_string' )
            )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->like(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $fromPathString . '%' )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll();
        $oldParentLocation = implode( '/', array_slice( explode( '/', $fromPathString ), 0, -2 ) ) . '/';
        foreach ( $rows as $row )
        {
            $newLocation = str_replace( $oldParentLocation, $toPathString, $row['path_string'] );

            $query = $this->handler->createUpdateQuery();
            $query
                ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
                ->set(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $newLocation )
                )
                ->set(
                    $this->handler->quoteColumn( 'depth' ),
                    $query->bindValue( substr_count( $newLocation, '/' ) - 2 )
                )
                ->where(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'node_id' ),
                        $query->bindValue( $row['node_id'] )
                    )
                );
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
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'modified_subnode' ),
                $query->bindValue( time() )
            )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( 'node_id' ),
                    $nodes
                )
            );
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
            ->update( $this->handler->quoteTable( 'eznode_assignment' ) )
            ->set(
                $this->handler->quoteColumn( 'parent_node' ),
                $query->bindValue( $newParent )
            )
            ->set(
                $this->handler->quoteColumn( 'op_code' ),
                $query->bindValue( self::NODE_ASSIGNMENT_OP_CODE_MOVE )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentObjectId )
                )
            );
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
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'is_invisible' ),
                $query->bindValue( 1 )
            )
            ->set(
                $this->handler->quoteColumn( 'modified_subnode' ),
                $query->bindValue( time() )
            )
            ->where(
                $query->expr->like(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $pathString . '%' )
                )
            );
        $query->prepare()->execute();

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'is_hidden' ),
                $query->bindValue( 1 )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $pathString )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hidding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    public function unHideSubtree( $pathString )
    {
        // Unhide the requested node
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'is_hidden' ),
                $query->bindValue( 0 )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $pathString )
                )
            );
        $query->prepare()->execute();

        // Check if any parent nodes are explicitely hidden
        $query = $this->handler->createSelectQuery();
        $query
            ->select( $this->handler->quoteColumn( 'path_string' ) )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'is_hidden' ),
                        $query->bindValue( 1 )
                    ),
                    $query->expr->in(
                        $this->handler->quoteColumn( 'node_id' ),
                        array_filter( explode( '/', $pathString ) )
                    )
                )
            );
        $statement = $query->prepare();
        $statement->execute();
        if ( count( $statement->fetchAll( \PDO::FETCH_COLUMN ) ) )
        {
            // There are parent nodes set hidden, so that we can skip marking
            // something visible again.
            return;
        }

        // Find nodes of explicitely hidden subtrees in the subtree which
        // should be unhidden
        $query = $this->handler->createSelectQuery();
        $query
            ->select( $this->handler->quoteColumn( 'path_string' ) )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'is_hidden' ),
                        $query->bindValue( 1 )
                    ),
                    $query->expr->like(
                        $this->handler->quoteColumn( 'path_string' ),
                        $query->bindValue( $pathString . '%' )
                    )
                )
            );
        $statement = $query->prepare();
        $statement->execute();
        $hiddenSubtrees = $statement->fetchAll( \PDO::FETCH_COLUMN );

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'is_invisible' ),
                $query->bindValue( 0 )
            )
            ->set(
                $this->handler->quoteColumn( 'modified_subnode' ),
                $query->bindValue( time() )
            );

        // Build where expression selecting the nodes, which should be made
        // visible again
        $where = $query->expr->like(
            $this->handler->quoteColumn( 'path_string' ),
            $query->bindValue( $pathString . '%' )
        );
        if ( count( $hiddenSubtrees ) )
        {
            $handler = $this->handler;
            $where = $query->expr->lAnd(
                $where,
                $query->expr->lAnd(
                    array_map(
                        function ( $pathString ) use ( $query, $handler )
                        {
                            return $query->expr->not(
                                $query->expr->like(
                                    $handler->quoteColumn( 'path_string' ),
                                    $query->bindValue( $pathString . '%' )
                                )
                            );
                        },
                        $hiddenSubtrees
                    )
                )
            );
        }
        $query->where( $where );
        $statement = $query->prepare()->execute();
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
        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $this->handler->quoteColumn( 'node_id' ),
                $this->handler->quoteColumn( 'contentobject_id' )
            )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( 'node_id' ),
                    array( $locationId1, $locationId2 )
                )
            );
        $statement = $query->prepare();
        $statement->execute();
        foreach ( $statement->fetchAll() as $row )
        {
            $contentObjects[$row['node_id']] = $row['contentobject_id'];
        }

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentObjects[$locationId2] )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $locationId1 )
                )
            );
        $query->prepare()->execute();

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentObjects[$locationId1] )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $locationId2 )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Creates a new location in given $parentNode
     *
     * @param \ezp\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @return \ezp\Persistence\Content\Location
     */
    public function create( CreateStruct $createStruct, array $parentNode, $published = false )
    {
        $location = new Location();
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $location->contentId = $createStruct->contentId, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'contentobject_is_published' ),
                $query->bindValue( (int) $published, null, \PDO::PARAM_INT ) // Will be set to 1, once the contentt object has been published
            )->set(
                $this->handler->quoteColumn( 'contentobject_version' ),
                $query->bindValue( $createStruct->contentVersion, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'depth' ),
                $query->bindValue( $location->depth = $parentNode['depth'] + 1, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'is_hidden' ),
                $query->bindValue( $location->hidden = $createStruct->hidden, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'is_invisible' ),
                $query->bindValue( $location->invisible = $createStruct->invisible, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'modified_subnode' ),
                $query->bindValue( $location->modifiedSubLocation = time(), null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'node_id' ),
                $this->handler->getAutoIncrementValue( 'ezcontentobject_tree', 'node_id' )
            )->set(
                $this->handler->quoteColumn( 'parent_node_id' ),
                $query->bindValue( $location->parentId = $parentNode['node_id'], null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'path_identification_string' ),
                $query->bindValue( null ) // Set later by the publishing operation
            )->set(
                $this->handler->quoteColumn( 'path_string' ),
                $query->bindValue( 'dummy' ) // Set later
            )->set(
                $this->handler->quoteColumn( 'priority' ),
                $query->bindValue( $location->priority = $createStruct->priority, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'remote_id' ),
                $query->bindValue( $location->remoteId = $createStruct->remoteId )
            )->set(
                $this->handler->quoteColumn( 'sort_field' ),
                $query->bindValue( $location->sortField = $createStruct->sortField, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'sort_order' ),
                $query->bindValue( $location->sortOrder = $createStruct->sortOrder, null, \PDO::PARAM_INT )
            );
        $query->prepare()->execute();

        $location->id = $this->handler->lastInsertId( $this->handler->getSequenceName( 'ezcontentobject_tree', 'node_id' ) );
        $location->mainLocationId = $createStruct->mainLocationId === true ? $location->id : $createStruct->mainLocationId;
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'path_string' ),
                $query->bindValue( $parentNode['path_string'] . $location->id . '/' )
            )
            ->set(
                $this->handler->quoteColumn( 'main_node_id' ),
                $query->bindValue( $location->mainLocationId, null, \PDO::PARAM_INT )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $location->id, null, \PDO::PARAM_INT )
                )
            );
        $query->prepare()->execute();

        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( 'eznode_assignment' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $createStruct->contentId, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'contentobject_version' ),
                $query->bindValue( $createStruct->contentVersion, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'from_node_id' ),
                $query->bindValue( 0, null, \PDO::PARAM_INT ) // unused field
            )->set(
                $this->handler->quoteColumn( 'id' ),
                $this->handler->getAutoIncrementValue( 'eznode_assignment', 'id' )
            )->set(
                $this->handler->quoteColumn( 'is_main' ),
                $query->bindValue( 0, null, \PDO::PARAM_INT ) // Changed by the business layer, later
            )->set(
                $this->handler->quoteColumn( 'op_code' ),
                $query->bindValue( self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'parent_node' ),
                $query->bindValue( $parentNode['node_id'], null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'parent_remote_id' ),
                $query->bindValue( '' )
            )->set(
                $this->handler->quoteColumn( 'remote_id' ),
                $query->bindValue( 0, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'sort_field' ),
                $query->bindValue( 2, null, \PDO::PARAM_INT ) // eZContentObjectTreeNode::SORT_FIELD_PUBLISHED
            )->set(
                $this->handler->quoteColumn( 'sort_order' ),
                $query->bindValue( 0, null, \PDO::PARAM_INT ) // eZContentObjectTreeNode::SORT_ORDER_DESC
            );
        $query->prepare()->execute();

        return $location;
    }

    /**
     * Updates an existing location.
     *
     * @param \ezp\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     * @return boolean
     */
    public function update( UpdateStruct $location, $locationId )
    {
        $query = $this->handler->createUpdateQuery();

        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'priority' ),
                $query->bindValue( $location->priority )
            )
            ->set(
                $this->handler->quoteColumn( 'remote_id' ),
                $query->bindValue( $location->remoteId )
            )
            ->set(
                $this->handler->quoteColumn( 'sort_order' ),
                $query->bindValue( $location->sortOrder )
            )
            ->set(
                $this->handler->quoteColumn( 'sort_field' ),
                $query->bindValue( $location->sortField )
            )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $locationId
                )
            );
        $query->prepare()->execute();
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
    public function trashSubtree( $pathString )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->like(
                    $this->handler->quoteColumn( 'path_string' ),
                    $query->bindValue( $pathString . '%' )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $nodeIds = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            unset( $row['contentobject_is_published'] );
            $query = $this->handler->createInsertQuery();
            $query->insertInto( $this->handler->quoteTable( 'ezcontentobject_trash' ) );

            foreach ( $row as $key => $value )
            {
                $query->set( $key, $query->bindValue( $value ) );
            }

            $query->prepare()->execute();
            $nodeIds[] = $row['node_id'];
        }

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( 'ezcontentobject_tree' )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( 'node_id' ),
                    $nodeIds
                )
            );
        $query->prepare()->execute();
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
     * @return boolean
     */
    public function untrashLocation( $locationId, $newParentId = null )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_trash' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $locationId )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $nodeIds = array();
        if ( !( $row = $statement->fetch( \PDO::FETCH_ASSOC ) ) )
        {
            throw new \ezp\Base\Exception\NotFound( 'trashed location', $locationId );
        }

        $newParentId = $newParentId ?: $row['parent_node_id'];
        $parentData = $this->getBasicNodeData( $newParentId );

        if ( $row['main_node_id'] === $row['node_id'] )
        {
            $row['main_node_id'] = true;
        }

        $this->create(
            new CreateStruct(
                array(
                    'priority' => $row['priority'],
                    'hidden' => $row['is_hidden'],
                    'invisible' => $row['is_invisible'],
                    'remoteId' => $row['remote_id'],
                    'contentId' => $row['contentobject_id'],
                    'contentVersion' => $row['contentobject_version'],
                    'mainLocationId' => $row['main_node_id'],
                    'sortField' => $row['sort_field'],
                    'sortOrder' => $row['sort_order'],
                )
            ),
            $parentData,
            true
        );

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( 'ezcontentobject_trash' )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $locationId
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Load trash data specified by location ID
     *
     * @param mixed $locationId
     * @return array
     */
    public function loadTrashByLocation( $locationId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_trash' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $query->bindValue( $locationId )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new \ezp\Base\Exception\NotFound( 'trash', $locationId );
    }

    /**
     * Set section on all content objects in the subtree
     *
     * @param mixed $pathString
     * @param mixed $sectionId
     * @return boolean
     */
    public function setSectionForSubtree( $pathString, $sectionId )
    {
        $query = $this->handler->createUpdateQuery();

        $subSelect = $query->subSelect();
        $subSelect
            ->select( $this->handler->quoteColumn( 'contentobject_id' ) )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $subSelect->expr->like(
                    $this->handler->quoteColumn( 'path_string' ),
                    $subSelect->bindValue( $pathString . '%' )
                )
            );

        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->set(
                $this->handler->quoteColumn( 'section_id' ),
                $query->bindValue( $sectionId )
            )
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn( 'id' ),
                    $subSelect
                )
            );
        $query->prepare()->execute();
    }
}
