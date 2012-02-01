<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\LocationUpdateStruct;
use ezp\PublicAPI\Values\Content\LocationCreateStruct;
use ezp\PublicAPI\Values\Content\ContentInfo;
use ezp\PublicAPI\Values\Content\Location;

/**
 * Location service, used for complex subtree operations
 *
 * @example Examples/location.php
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface LocationService
{
    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     * 
     * Only the items on which the user has read access are copied.
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException  if the target location is a sub location of the given location
     *
     * @param \ezp\PublicAPI\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \ezp\PublicAPI\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \ezp\PublicAPI\Values\Content\Location The newly created location of the copied subtree
     *
     * @todo enhancement - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     */
    public function copySubtree( Location $subtree,  Location $targetParentLocation );

    /**
     * Loads a location object from its $locationId
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException If the specified location is not found
     *
     * @param integer $locationId
     *
     * @return \ezp\PublicAPI\Values\Content\Location
     */
    public function loadLocation( $locationId );

    /**
     * Loads a location object from its $remoteId
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \ezp\PublicAPI\Exceptions\NotFoundException If the specified location is not found
     *
     * @param string $remoteId
     *
     * @return \ezp\PublicAPI\Values\Content\Location
     */
    public function loadLocationByRemoteId( $remoteId );

    /**
     * loads the main loaction of a content object
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \ezp\PublicAPI\Exceptions\BadStateException if there is no published version yet
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     *
     * @return \ezp\PublicAPI\Values\Content\Location (in 5.x the return value also can be null if the content has no location)
     */
    public function loadMainLocation( ContentInfo $contentInfo );

    /**
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location are returned.
     * The location list is also filtered by permissions on reading locations.
     *
     * @throws \ezp\PublicAPI\Exceptions\BadStateException if there is no published version yet
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param \ezp\PublicAPI\Values\Content\Location $rootLocation
     *
     * @return array an array of {@link Location}
     */
    public function loadLocations( Content $contentInfo, Location $rootLocation = null );

    /**
     * Load children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return array of {@link Location}
     */
    public function loadLocationChildren( Location $location, $offset = 0, $limit = -1 );

    /**
     * Creates the new $location in the content repository for the given content
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId existis already
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     *
     * @param \ezp\PublicAPI\Values\Content\LocationCreateStruct $location
     *
     * @return \ezp\PublicAPI\Values\Content\Location the newly created Location
     *
     */
    public function createLocation( ContentInfo $content, LocationCreateStruct $locationCreateStruct );

    /**
     * Updates $location in the content repository
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \ezp\PublicAPI\Exceptions\IllegalArgumentException   if if set the remoteId existis already
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location
     * @param \ezp\PublicAPI\Values\Content\LocationUpdateStruct $locationUpdateStruct
     *
     * @return \ezp\PublicAPI\Values\Content\Location the updated Location
     */
    public function updateLocation( Location $location, LocationUpdateStruct $locationUpdateStruct );

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location1
     * @param \ezp\PublicAPI\Values\Content\Location $location2
     */
    public function swapLocation( Location $location1,  Location $location2 );

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to hide this location
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location
     *
     * @return \ezp\PublicAPI\Values\Content\Location $location, with updated hidden value
     */
    public function hideLocation( Location $location );

    /**
     * Unhides the $location.
     * 
     * This method and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to unhide this location
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location
     *
     * @return \ezp\PublicAPI\Values\Content\Location $location, with updated hidden value
     */
    public function unhideLocation( Location $location );

    /**
     * Moves the subtree to $newParentLocation  
     * 
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \ezp\PublicAPI\Values\Content\Location $location
     * @param \ezp\PublicAPI\Values\Content\Location $newParentLocation
     */
    public function moveSubtree( Location $location, Location $newParentLocation );

    /**
     * Deletes $location and all its descendants.
     *
     * @throws \ezp\PublicAPI\Exceptions\UnauthorizedException If the current user is not allowed to delete this location or a descendant
     * 
     * @param \ezp\PublicAPI\Values\Content\Location $location
     */
    public function deleteLocation( Location $location );


    /**
     * Instantiates a new location create class
     *
     * @param int $parentLocationId the parent under which the new location should be created
     *
     * @return \ezp\PublicAPI\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct( $parentLocationId );

    /**
     * Instantiates a new location update class
     *
     * @return \ezp\PublicAPI\Values\Content\LocationUpdateStruct
     */
    public function newLocationUpdateStruct();
}

