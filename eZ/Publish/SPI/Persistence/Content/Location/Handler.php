<?php
/**
 * File containing the Location Handler interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Location;

use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * The Location Handler interface defines operations on Location elements in the storage engine.
 *
 *
 * Note on Locations drafts, depending on Storage Engine limitations the following needs to be considered on SPI use:
 * - Currently only relevant for unpublished content, however this is internal logic not exposed in API
 * - Storage engine may generate a Location id for drafts with a identifier in the id to be able to know it is a draft
 * - For these storage engines Location drafts are thus only supported by:
 *  - load()
 *  - loadLocationsByDraftContent()
 *  - loadParentLocationsForDraftContent()
 *  - update()
 *  - publishDraftLocation()
 */
interface Handler
{
    /**
     * Loads the data for the location identified by $locationId.
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function load( $locationId );

    /**
     * Loads the subtree ids of the location identified by $locationId.
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return array Location ids are in the index, Content ids in the value.
     */
    public function loadSubtreeIds( $locationId );

    /**
     * Loads the data for the location identified by $remoteId.
     *
     * @param string $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function loadByRemoteId( $remoteId );

    /**
     * Loads all locations for $contentId, optionally limited to a sub tree
     * identified by $rootLocationId
     *
     * @param int $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadLocationsByContent( $contentId );

    /**
     * Loads all location drafts for $contentId
     *
     * @param int $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadLocationsByDraftContent( $contentId );

    /**
     * Loads all parent Locations for unpublished Content by given $contentId.
     *
     * @access private This method is stopgap solution and will be removed once loading draft Locations is implemented.
     * @deprecated Since 5.4
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[]
     */
    public function loadParentLocationsForDraftContent( $contentId );

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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $sourceId or $destinationParentId are invalid
     *
     * @return Location the newly created Location.
     */
    public function copySubtree( $sourceId, $destinationParentId );

    /**
     * Moves location identified by $sourceId into new parent identified by $destinationParentId.
     *
     * Performs a full move of the location identified by $sourceId to a new
     * destination, identified by $destinationParentId. Relations do not need
     * to be updated, since they refer to Content. URLs are not touched.
     *
     * @param mixed $sourceId
     * @param mixed $destinationParentId
     *
     * @return boolean
     */
    public function move( $sourceId, $destinationParentId );

    /**
     * Marks the given nodes and all ancestors as modified
     *
     * Optionally a time stamp with the modification date may be specified,
     * otherwise the current time is used.
     *
     * @param int|string $locationId
     * @param int $timestamp
     *
     * @return void
     */
    public function markSubtreeModified( $locationId, $timestamp = null );

    /**
     * Sets a location to be hidden, and it self + all children to invisible.
     *
     * @param mixed $id Location ID
     */
    public function hide( $id );

    /**
     * Sets a location to be unhidden, and self + children to visible unless a parent is hiding the tree.
     * If not make sure only children down to first hidden node is marked visible.
     *
     * @param mixed $id
     */
    public function unHide( $id );

    /**
     * Swaps the content object being pointed to by a location object.
     *
     * Make the location identified by $locationId1 refer to the Content
     * referred to by $locationId2 and vice versa.
     *
     * @param mixed $locationId1
     * @param mixed $locationId2
     *
     * @return boolean
     */
    public function swap( $locationId1, $locationId2 );

    /**
     * Updates an existing location.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct $location
     * @param int $locationId
     *
     * @return void
     */
    public function update( UpdateStruct $location, $locationId );

    /**
     * Creates a new location rooted at $location->parentId.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct $location
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function create( CreateStruct $location );

    /**
     * Removes all Locations under and including $locationId.
     *
     * Performs a recursive delete on the location identified by $locationId,
     * including all of its child locations. Content which is not referred to
     * by any other location is automatically removed. Content which looses its
     * main Location will get the first of its other Locations assigned as the
     * new main Location.
     *
     * @param mixed $locationId
     *
     * @return boolean
     */
    public function removeSubtree( $locationId );

    /**
     * Set section on all content objects in the subtree.
     * Only main locations will be updated
     *
     * @todo This can be confusing (regarding permissions and main/multi location).
     * So method is for the time being not in PublicAPI so people can instead
     * write scripts using their own logic against the assignSectionToContent() api.
     *
     * @param mixed $locationId
     * @param mixed $sectionId
     *
     * @return void
     */
    public function setSectionForSubtree( $locationId, $sectionId );

    /**
     * Changes the status of Location identified by $locationId to being published.
     *
     * If storage engine does not update drafts on tree operations, typically because drafts are stored separately.
     * Then update the following:
     * - Based on parent location data
     *      - pathString
     *      - pathIdentificationString
     *      - invisible
     *      - depth
     * - New values if needed:
     *      - (location)Id
     *      - remoteId
     *
     * @param int $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If $locationId draft is invalid
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function publishDraftLocation( $locationId );

    /**
     * Changes main location of content identified by given $contentId to location identified by given $locationId
     *
     * @param mixed $contentId
     * @param mixed $locationId
     *
     * @return void
     */
    public function changeMainLocation( $contentId, $locationId );
}
