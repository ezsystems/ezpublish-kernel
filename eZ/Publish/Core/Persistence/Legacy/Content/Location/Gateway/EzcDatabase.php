<?php
/**
 * File containing the EzcDatabase location gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    RuntimeException;

/**
 * Location gateway implementation using the zeta database component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var \EzcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $handler
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

        throw new NotFound( 'location', $nodeId );
    }

    /**
     * Returns an array with basic node data
     *
     * @optimze
     * @param mixed $remoteId
     * @return array
     */
    public function getBasicNodeDataByRemoteId( $remoteId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'remote_id' ),
                    $query->bindValue( $remoteId )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            return $row;
        }

        throw new NotFound( 'location', $remoteId );
    }

    /**
     * Loads data for all Locations for $contentId, optionally only in the
     * subtree starting at $rootLocationId
     *
     * @param int $contentId
     * @param int $rootLocationId
     * @return array
     */
    public function loadLocationDataByContent( $contentId, $rootLocationId = null )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId )
                )
            );

        if ( $rootLocationId !== null )
        {
            $this->applySubtreeLimitation( $query, $rootLocationId );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Find all content in the given subtree
     *
     * @param mixed $sourceId
     * @return array
     */
    public function getSubtreeContent( $sourceId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select( '*' )->from(
            $this->handler->quoteTable( 'ezcontentobject_tree' )
        );
        $this->applySubtreeLimitation( $query, $sourceId );
        $query->orderBy(
            $this->handler->quoteColumn( 'path_string', 'ezcontentobject_tree' )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Limits the given $query to the subtree starting at $rootLocationId
     *
     * @param \ezcQuery $query
     * @param string $rootLocationId
     * @return void
     */
    protected function applySubtreeLimitation( \ezcQuery $query, $rootLocationId )
    {
        $query->where(
            $query->expr->like(
                $this->handler->quoteColumn( 'path_string', 'ezcontentobject_tree' ),
                $query->bindValue( '%/' . $rootLocationId . '/%' )
            )
        );
    }

    /**
     * Returns data for the first level children of the location identified by given $locationId
     *
     * @param mixed $locationId
     * @return array
     */
    public function getChildren( $locationId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select( "*" )->from(
            $this->handler->quoteTable( "ezcontentobject_tree" )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( "parent_node_id", "ezcontentobject_tree" ),
                $query->bindValue( $locationId, null, \PDO::PARAM_INT )
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
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
                $this->handler->quoteColumn( 'parent_node_id' ),
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

            $newParentId = $row['parent_node_id'];
            if ( $row['path_string'] === $fromPathString )
            {
                $newParentId = (int) implode( '', array_slice( explode( '/', $newLocation ), -3, 1 ) );
            }

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
                ->set(
                    $this->handler->quoteColumn( 'parent_node_id' ),
                    $query->bindValue( $newParentId )
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
     * @param int|null $timestamp
     *
     * @return void
     */
    public function updateSubtreeModificationTime( $pathString, $timestamp = null )
    {
        $nodes = array_filter( explode( '/', $pathString ) );
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'modified_subnode' ),
                $query->bindValue(
                    $timestamp ?: time()
                )
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

        // Find nodes of explicitly hidden subtrees in the subtree which
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
                $this->handler->quoteColumn( 'contentobject_id' ),
                $this->handler->quoteColumn( 'contentobject_version' )
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
            $contentObjects[$row['node_id']] = $row;
        }

        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentObjects[$locationId2]['contentobject_id'] )
            )
            ->set(
                $this->handler->quoteColumn( 'contentobject_version' ),
                $query->bindValue( $contentObjects[$locationId2]['contentobject_version'] )
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
                $query->bindValue( $contentObjects[$locationId1]['contentobject_id'] )
            )
            ->set(
                $this->handler->quoteColumn( 'contentobject_version' ),
                $query->bindValue( $contentObjects[$locationId1]['contentobject_version'] )
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
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @param bool $published
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
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
                $query->bindValue( (int)$published, null, \PDO::PARAM_INT ) // Will be set to 1, once the content object has been published
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
                $query->bindValue( null ) // Set after creation
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
        $location->pathString = $parentNode['path_string'] . $location->id . '/';
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->set(
                $this->handler->quoteColumn( 'path_string' ),
                $query->bindValue( $location->pathString )
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

        return $location;
    }

    /**
     * Create an entry in the node assignment table
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param mixed $parentNodeId
     * @param int $type
     * @return void
     */
    public function createNodeAssignment( CreateStruct $createStruct, $parentNodeId, $type = self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP )
    {
        $isMain = ( $createStruct->mainLocationId === true ? 1 : 0 );

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
                $query->bindValue( $isMain, null, \PDO::PARAM_INT ) // Changed by the business layer, later
            )->set(
                $this->handler->quoteColumn( 'op_code' ),
                $query->bindValue( $type, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'parent_node' ),
                $query->bindValue( $parentNodeId, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'parent_remote_id' ),
                $query->bindValue( '' )
            )->set(
                $this->handler->quoteColumn( 'remote_id' ),
                $query->bindValue( $createStruct->remoteId, null, \PDO::PARAM_STR )
            )->set(
                $this->handler->quoteColumn( 'sort_field' ),
                $query->bindValue( Location::SORT_FIELD_PUBLISHED, null, \PDO::PARAM_INT )
            )->set(
                $this->handler->quoteColumn( 'sort_order' ),
                $query->bindValue( Location::SORT_ORDER_DESC, null, \PDO::PARAM_INT )
            );
        $query->prepare()->execute();
    }

    /**
     * Deletes node assignment for given $contentId and $versionNo
     *
     * If $versionNo is not passed all node assignments for given $contentId are deleted
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return void
     */
    public function deleteNodeAssignment( $contentId, $versionNo = null )
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom(
            'eznode_assignment'
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );
        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_version' ),
                    $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            );
        }
        $query->prepare()->execute();
    }

    /**
     * Update node assignment table
     *
     * @param int $contentObjectId
     * @param int $oldParent
     * @param int $newParent
     * @param int $opcode
     * @return void
     */
    public function updateNodeAssignment( $contentObjectId, $oldParent, $newParent, $opcode )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'eznode_assignment' ) )
            ->set(
                $this->handler->quoteColumn( 'parent_node' ),
                $query->bindValue( $newParent, null, \PDO::PARAM_INT )
            )
            ->set(
                $this->handler->quoteColumn( 'op_code' ),
                $query->bindValue( $opcode, null, \PDO::PARAM_INT )
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $contentObjectId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'parent_node' ),
                        $query->bindValue( $oldParent, null, \PDO::PARAM_INT )
                    )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Create locations from node assignments
     *
     * Convert existing node assignments into real locations.
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     * @return void
     */
    public function createLocationsFromNodeAssignments( $contentId, $versionNo )
    {
        // select all node assignments with OP_CODE_CREATE (3) for this content
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'eznode_assignment' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->like(
                        $this->handler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->like(
                        $this->handler->quoteColumn( 'contentobject_version' ),
                        $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->like(
                        $this->handler->quoteColumn( 'op_code' ),
                        $query->bindValue( self::NODE_ASSIGNMENT_OP_CODE_CREATE, null, \PDO::PARAM_INT )
                    )
                )
            )->orderBy( 'id' );
        $statement = $query->prepare();
        $statement->execute();

        // convert all these assignments to nodes

        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            if ( (bool)$row['is_main'] === true )
            {
                $mainLocationId = true;
            }
            else
            {
                $mainLocationId = $this->getMainNodeId( $contentId );
            }
            $this->create(
                new CreateStruct(
                    array(
                        'contentId' => $row['contentobject_id'],
                        'contentVersion' => $row['contentobject_version'],
                        'mainLocationId' => $mainLocationId,
                        'remoteId' => $row['remote_id'],
                        'sortField' => $row['sort_field'],
                        'sortOrder' => $row['sort_order'],
                    )
                ),
                $this->getBasicNodeData( $row['parent_node'] )
            );

            $this->updateNodeAssignment(
                $row['contentobject_id'],
                $row['parent_node'],
                $row['parent_node'],
                self::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP
            );
        }
    }

    /**
     * Updates all Locations of content identified with $contentId with $versionNo
     *
     * @param mixed $contentId
     * @param mixed $versionNo
     *
     * @return void
     */
    public function updateLocationsContentVersionNo( $contentId, $versionNo )
    {
        $query = $this->handler->createUpdateQuery();
        $query->update(
            $this->handler->quoteTable( "ezcontentobject_tree" )
        )->set(
            $this->handler->quoteColumn( "contentobject_version" ),
            $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( "contentobject_id" ),
                $contentId
            )
        );
        $query->prepare()->execute();
    }

    /**
     * Searches for the main nodeId of $contentId in $versionId
     *
     * @param int $contentId
     * @return int|bool
     */
    private function getMainNodeId( $contentId )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( 'node_id' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'contentobject_id' ),
                        $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'node_id' ),
                        $this->handler->quoteColumn( 'main_node_id' )
                    )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( count( $result ) === 1 )
        {
            return (int)$result[0]['node_id'];
        }
        else
        {
            return false;
        }
    }

    /**
     * Updates an existing location.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
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
     * Deletes ezcontentobject_tree row for given $locationId (node_id)
     *
     * @param mixed $locationId
     */
    public function removeLocation( $locationId )
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom(
            "ezcontentobject_tree"
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( "node_id" ),
                $query->bindValue( $locationId, null, \PDO::PARAM_INT )
            )
        );
        $query->prepare()->execute();
    }

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
    public function getFallbackMainNodeData( $contentId, $locationId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn( "node_id" ),
            $this->handler->quoteColumn( "contentobject_version" ),
            $this->handler->quoteColumn( "parent_node_id" )
        )->from(
            $this->handler->quoteTable( "ezcontentobject_tree" )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn( "contentobject_id" ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->neq(
                    $this->handler->quoteColumn( "node_id" ),
                    $query->bindValue( $locationId, null, \PDO::PARAM_INT )
                )
            )
        )->orderBy( "node_id", Query::SORT_ASC )->limit( 1 );
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetch( \PDO::FETCH_ASSOC );
    }

    /**
     * Sends a subtree to the trash
     *
     * Moves all locations in the subtree to the Trash. The associated content
     * objects are left untouched.
     *
     * @param string $pathString
     * @return void
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
        $objectIds = array();
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
            $objectIds[] = $row['contentobject_id'];
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

        // Now check if there is no more node for each content object.
        // If so, set content object status to archived
        foreach ( $objectIds as $contentId )
        {
            if ( $this->countLocationsByContentId( $contentId ) < 1 )
            {
                $q = $this->handler->createUpdateQuery();
                $q
                    ->update( 'ezcontentobject' )
                    ->set(
                        $this->handler->quoteColumn( 'status' ),
                        $q->bindValue( ContentInfo::STATUS_ARCHIVED, null, \PDO::PARAM_INT )
                    )
                    ->where(
                        $q->expr->eq(
                            $this->handler->quoteColumn( 'id' ),
                            $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                        )
                    );
                $q->prepare()->execute();
            }
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
     * @return \eZ\Publish\SPI\Persistence\Content\Location
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

        if ( !( $row = $statement->fetch( \PDO::FETCH_ASSOC ) ) )
        {
            throw new NotFound( 'trashed location', $locationId );
        }

        $newParentId = $newParentId ?: $row['parent_node_id'];
        $parentData = $this->getBasicNodeData( $newParentId );

        if ( $row['main_node_id'] === $row['node_id'] )
        {
            $row['main_node_id'] = true;
        }

        $newLocation = $this->create(
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

        // Restore content status to published
        $q = $this->handler->createUpdateQuery();
        $q
            ->update( 'ezcontentobject' )
            ->set(
            $this->handler->quoteColumn( 'status' ),
            $q->bindValue( ContentInfo::STATUS_PUBLISHED, null, \PDO::PARAM_INT )
        )
            ->where(
            $q->expr->eq(
                $this->handler->quoteColumn( 'id' ),
                $q->bindValue( $row['contentobject_id'], null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();

        return $newLocation;
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

        throw new NotFound( 'trash', $locationId );
    }

    /**
     * List trashed items
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     * @return array
     */
    public function listTrashed( $offset, $limit, array $sort = null )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( '*' )
            ->from( $this->handler->quoteTable( 'ezcontentobject_trash' ) );

        $sort = $sort ?: array();
        foreach ( $sort as $condition )
        {
            $sortDirection = $condition->direction === Query::SORT_ASC ? \ezcQuerySelect::ASC : \ezcQuerySelect::DESC;
            switch ( true )
            {
                case $condition instanceof SortClause\LocationDepth:
                    $query->orderBy( 'depth', $sortDirection );
                    break;

                case $condition instanceof SortClause\LocationPathString:
                    $query->orderBy( 'path_string', $sortDirection );
                    break;

                case $condition instanceof SortClause\LocationPriority:
                    $query->orderBy( 'priority', $sortDirection );
                    break;

                default:
                    // Only handle location related sort clauses. The others
                    // require data aggregation which is not sensible here.
                    // Since also criteria are yet ignored, because they are
                    // simply not used yet in eZ Publish, we skip that for now.
                    throw new RuntimeException( 'Unhandled sort clause: ' . get_class( $condition ) );
            }
        }

        if ( $limit !== null )
        {
            $query->limit( $limit, $offset );
        }

        $statement = $query->prepare();
        $statement->execute();

        $rows = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Removes every entries in the trash.
     * Will NOT remove associated content objects nor attributes.
     *
     * Basically truncates ezcontentobject_trash table.
     *
     * @return void
     */
    public function cleanupTrash()
    {
        $query = $this->handler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_trash' );
        $query->prepare()->execute();
    }

    /**
     * Removes trashed element identified by $id from trash.
     * Will NOT remove associated content object nor attributes.
     *
     * @param int $id The trashed location Id
     * @return void
     */
    public function removeElementFromTrash( $id )
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( 'ezcontentobject_trash' )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'node_id' ),
                    $id
                )
            );
        $query->prepare()->execute();
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

    /**
     * Returns how many locations given content object identified by $contentId has
     *
     * @param int $contentId
     * @return int
     */
    public function countLocationsByContentId( $contentId )
    {
        $q = $this->handler->createSelectQuery();
        $q
            ->select(
                $q->alias( $q->expr->count( '*' ), 'count' )
            )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->where(
                $q->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $res = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        return (int)$res[0]['count'];
    }

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
    public function changeMainLocation( $contentId, $locationId, $versionNo, $parentLocationId )
    {
        // Update ezcontentobject_tree table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable( "ezcontentobject_tree" )
        )->set(
            $this->handler->quoteColumn( "main_node_id" ),
            $q->bindValue( $locationId, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq(
                $this->handler->quoteColumn( "contentobject_id" ),
                $q->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();

        // Erase is_main in eznode_assignment table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable( "eznode_assignment" )
        )->set(
            $this->handler->quoteColumn( "is_main" ),
            $q->bindValue( 0, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->handler->quoteColumn( "contentobject_id" ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn( "contentobject_version" ),
                    $q->bindValue( $versionNo, null, \PDO::PARAM_INT )
                ),
                $q->expr->neq(
                    $this->handler->quoteColumn( "parent_node" ),
                    $q->bindValue( $parentLocationId, null, \PDO::PARAM_INT )
                )
            )
        );
        $q->prepare()->execute();

        // Set new is_main in eznode_assignment table
        $q = $this->handler->createUpdateQuery();
        $q->update(
            $this->handler->quoteTable( "eznode_assignment" )
        )->set(
            $this->handler->quoteColumn( "is_main" ),
            $q->bindValue( 1, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->handler->quoteColumn( "contentobject_id" ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn( "contentobject_version" ),
                    $q->bindValue( $versionNo, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->handler->quoteColumn( "parent_node" ),
                    $q->bindValue( $parentLocationId, null, \PDO::PARAM_INT )
                )
            )
        );
        $q->prepare()->execute();
    }
}
