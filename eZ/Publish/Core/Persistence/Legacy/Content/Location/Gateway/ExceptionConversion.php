<?php
/**
 * File containing the Location Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway,
    eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;

/**
 * Base class for location gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
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
        try
        {
            return $this->innerGateway->getBasicNodeData( $nodeId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns an array with basic node data for the node with $remoteId
     *
     * @optimze
     * @param mixed $remoteId
     * @return array
     */
    public function getBasicNodeDataByRemoteId( $remoteId )
    {
        try
        {
            return $this->innerGateway->getBasicNodeDataByRemoteId( $remoteId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadLocationDataByContent( $contentId, $rootLocationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Find all content in the given subtree
     *
     * @param mixed $sourceId
     * @return array
     */
    public function getSubtreeContent( $sourceId )
    {
        try
        {
            return $this->innerGateway->getSubtreeContent( $sourceId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns data for the first level children of the location identified by given $locationId
     *
     * @param mixed $locationId
     * @return array
     */
    public function getChildren( $locationId )
    {
        try
        {
            return $this->innerGateway->getChildren( $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->moveSubtreeNodes( $fromPathString, $toPathString );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            return $this->innerGateway->updateSubtreeModificationTime( $pathString, $timestamp );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->updateNodeAssignment( $contentObjectId, $oldParent, $newParent, $opcode );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->createLocationsFromNodeAssignments( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            return $this->innerGateway->updateLocationsContentVersionNo( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param string $pathString
     */
    public function hideSubtree( $pathString )
    {
        try
        {
            return $this->innerGateway->hideSubtree( $pathString );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hidding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param string $pathString
     */
    public function unHideSubtree( $pathString )
    {
        try
        {
            return $this->innerGateway->unHideSubtree( $pathString );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->swap( $locationId1, $locationId2 );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Creates a new location in given $parentNode
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $createStruct
     * @param array $parentNode
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function create( CreateStruct $createStruct, array $parentNode )
    {
        try
        {
            return $this->innerGateway->create( $createStruct, $parentNode );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->createNodeAssignment( $createStruct, $parentNodeId, $type );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes node assignment for given $contentId and $versionNo
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return void
     */
    public function deleteNodeAssignment( $contentId, $versionNo = null )
    {
        try
        {
            return $this->innerGateway->deleteNodeAssignment( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            return $this->innerGateway->update( $location, $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes ezcontentobject_tree row for given $locationId (node_id)
     *
     * @param mixed $locationId
     */
    public function removeLocation( $locationId )
    {
        try
        {
            return $this->innerGateway->removeLocation( $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->getFallbackMainNodeData( $contentId, $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sends a single location identified by given $locationId to the trash.
     *
     * The associated content object is left untouched.
     *
     * @param mixed $locationId
     *
     * @return boolean
     */
    public function trashLocation( $locationId )
    {
        try
        {
            return $this->innerGateway->trashLocation( $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
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
        try
        {
            return $this->innerGateway->untrashLocation( $locationId, $newParentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Load trash data specified by location ID
     *
     * @param mixed $locationId
     * @return array
     */
    public function loadTrashByLocation( $locationId )
    {
        try
        {
            return $this->innerGateway->loadTrashByLocation( $locationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->cleanupTrash();
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Lists trashed items.
     * Returns entries from ezcontentobject_trash.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort
     * @return array
     */
    public function listTrashed( $offset, $limit, array $sort = null )
    {
        try
        {
            return $this->innerGateway->listTrashed( $offset, $limit, $sort );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->removeElementFromTrash( $id );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->setSectionForSubtree( $pathString, $sectionId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns how many locations given content object identified by $contentId has
     *
     * @param int $contentId
     * @return int
     */
    public function countLocationsByContentId( $contentId )
    {
        try
        {
            return $this->innerGateway->countLocationsByContentId( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->changeMainLocation( $contentId, $locationId, $versionNo, $parentLocationId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
