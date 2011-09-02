<?php
/**
 * File containing the Location Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Location;
use ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\Location\UpdateStruct,
    ezp\Persistence\Content\Location\Handler as BaseLocationHandler,
    ezp\Persistence\Storage\Legacy\Content\Handler as ContentHandler,
    ezp\Persistence\Storage\Legacy\Content\Location\Gateway as LocationGateway,
    ezp\Persistence\Storage\Legacy\Content\Location\Mapper as LocationMapper;

/**
 * The Location Handler interface defines operations on Location elements in the storage engine.
 */
class Handler implements BaseLocationHandler
{
    /**
     * Gaateway for handling location data
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location mapper
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Mapper $mapper
     */
    protected $mapper;

    /**
     * Construct from userGateway
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Location\Gateway $locationGateway
     * @return void
     */
    public function __construct( LocationGateway $locationGateway, LocationMapper $mapper )
    {
        $this->locationGateway = $locationGateway;
        $this->mapper = $mapper;
    }

    /**
     * Return parent path string for a path string
     *
     * @param string $pathString
     * @return string
     */
    protected function getParentPathString( $pathString )
    {
        return implode( '/', array_slice( explode( '/', $pathString ), 0, -2 ) ) . '/';
    }

    /**
     * Loads the data for the location identified by $locationId.
     *
     * @param int $locationId
     * @return \ezp\Persistence\Content\Location
     */
    public function load( $locationId )
    {
        $data = $this->locationGateway->getBasicNodeData( $locationId );
        return $this->mapper->createLocationFromRow( $data );
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
        throw new \RuntimeException( '@TODO: Implement' );
    }

    /**
     * Moves location identified by $sourceId into new parent identified by $destinationParentId.
     *
     * Performs a full move of the location identified by $sourceId to a new
     * destination, identified by $destinationParentId. Relations do not need
     * to be updated, since they refer to Content. URLs are not touched.
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     * @return boolean
     */
    public function move( $sourceId, $destinationParentId )
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $sourceId );
        $destinationNodeData = $this->locationGateway->getBasicNodeData( $destinationParentId );

        $this->locationGateway->moveSubtreeNodes(
            $sourceNodeData['path_string'],
            $destinationNodeData['path_string']
        );

        $this->locationGateway->updateNodeAssignement(
            $sourceNodeData['contentobject_id'], $destinationParentId
        );
    }

    /**
     * Marks the given nodes and all ancestors as modified
     *
     * Optionally a time stamp with the modification date may be specified,
     * otherwise the current time is used.
     *
     * @param int|string $locationId
     * @param int $timeStamp
     * @return void
     */
    public function markSubtreeModified( $locationId, $timeStamp = null )
    {
        $nodeData = $this->locationGateway->getBasicNodeData( $locationId );
        $timeStamp = $timeStamp ?: time();
        $this->locationGateway->updateSubtreeModificationTime( $nodeData['path_string'], $timeStamp );
    }

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param mixed $id Location ID
     */
    public function hide( $id )
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $id );

        $this->locationGateway->hideSubtree( $sourceNodeData['path_string'] );
    }

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hidding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param mixed $id
     */
    public function unHide( $id )
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $id );

        $this->locationGateway->unhideSubtree( $sourceNodeData['path_string'] );
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
        $this->locationGateway->swap( $locationId1, $locationId2 );
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
        $this->locationGateway->update( $location, $locationId );
    }

    /**
     * Creates a new location rooted at $location->parentId.
     *
     * @param \ezp\Persistence\Content\Location\CreateStruct $contentId
     * @return \ezp\Persistence\Content\Location
     */
    public function create( CreateStruct $locationStruct )
    {
        $parentNodeData = $this->locationGateway->getBasicNodeData( $locationStruct->parentId );
        return $this->locationGateway->create( $locationStruct, $parentNodeData );
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
        throw new \RuntimeException( '@TODO: Implement' );
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
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $locationId );

        $this->locationGateway->trashSubtree( $sourceNodeData['path_string'] );
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
        $this->locationGateway->untrashLocation( $locationId, $newParentId );
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
        $nodeData = $this->locationGateway->getBasicNodeData( $locationId );

        $this->locationGateway->setSectionForSubtree( $nodeData['path_string'], $sectionId );
    }

    /**
     * Removes a location from its $locationId
     *
     * @param mixed $locationId
     */
    public function delete( $locationId )
    {
        throw new \RuntimeException( '@TODO: Implement' );
    }
}
?>
