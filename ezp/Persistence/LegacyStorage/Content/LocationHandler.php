<?php
/**
 * File containing the LocationHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\Content;
use ezp\Persistence\Content\Location;

/**
 * The LocationHandler interface defines operations on Location elements in the storage engine.
 */
class LocationHandler implements \ezp\Persistence\Content\Interfaces\LocationHandler
{
    /**
     * Content handler implementation
     *
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * Gaateway for handling location data
     *
     * @var LocationGateway
     */
    protected $locationGateway;

    /**
     * Construct from userGateway
     *
     * @param LocationGateway $locationGateway
     * @return void
     */
    public function __construct( ContentHandler $contentHandler, LocationGateway $locationGateway )
    {
        $this->contentHandler  = $contentHandler;
        $this->locationGateway = $locationGateway;
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
     * @return ezp\Persistence\Content\Location
     */
    public function load( $locationId )
    {
        $data     = $this->locationGateway->getBasicNodeData( $locationId );
        $location = new Location();

        $location->id                       = $data['node_id'];
        $location->priority                 = $data['priority'];
        $location->hidden                   = $data['is_hidden'];
        $location->invisible                = $data['is_invisible'];
        $location->remoteId                 = $data['remote_id'];
        $location->contentId                = $data['contentobject_id'];
        $location->parentId                 = $data['parent_node_id'];
        $location->pathIdentificationString = $data['path_identification_string'];
        $location->pathString               = $data['path_string'];
        $location->modifiedSubLocation      = $data['modified_subnode'];
        $location->mainLocationId           = $data['main_node_id'];
        $location->depth                    = $data['depth'];
        $location->sortField                = $data['sort_field'];
        $location->sortOrder                = $data['sort_order'];

        return $location;
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
        $sourceNodeData      = $this->locationGateway->getBasicNodeData( $sourceId );
        $destinationNodeData = $this->locationGateway->getBasicNodeData( $destinationParentId );

        $this->locationGateway->moveSubtreeNodes(
            $sourceNodeData['path_string'],
            $destinationNodeData['path_string']
        );

        $this->locationGateway->updateSubtreeModificationTime(
            $destinationNodeData['path_string'] . $sourceNodeData['node_id'] . '/'
        );

        $this->locationGateway->updateNodeAssignement(
            $sourceNodeData['contentobject_id'], $destinationParentId
        );
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
        $this->locationGateway->updateSubtreeModificationTime( $sourceNodeData['path_string'] );
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
        $this->locationGateway->updateSubtreeModificationTime( $sourceNodeData['path_string'] );
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

        $locationData1 = $this->locationGateway->getBasicNodeData( $locationId1 );
        $this->locationGateway->updateSubtreeModificationTime( $this->getParentPathString( $locationData1['path_string'] ) );

        $locationData2 = $this->locationGateway->getBasicNodeData( $locationId2 );
        $this->locationGateway->updateSubtreeModificationTime( $this->getParentPathString( $locationData2['path_string'] ) );
    }

    /**
     * Updates an existing location priority.
     *
     * @param int $locationId
     * @param int $priority
     * @return boolean
     */
    public function updatePriority( $locationId, $priority )
    {
        $sourceNodeData = $this->locationGateway->getBasicNodeData( $locationId );

        $this->locationGateway->updatePriority( $locationId, $priority );
        $this->locationGateway->updateSubtreeModificationTime( $this->getParentPathString( $sourceNodeData['path_string'] ) );
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
        $parentNodeData = $this->locationGateway->getBasicNodeData( $parentId );
        $content        = $this->contentHandler->load( $contentId );

        $this->locationGateway->createLocation( $content, $parentNodeData );
        $this->locationGateway->updateSubtreeModificationTime( $parentNodeData['path_string'] );
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
?>
