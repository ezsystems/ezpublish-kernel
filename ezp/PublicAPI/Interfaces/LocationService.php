<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\LocationUpdate;

use ezp\PublicAPI\Values\Content\LocationCreate;

use ezp\PublicAPI\Values\Content\ContentInfo;

use ezp\PublicAPI\Values\Content\Location;

/**
 * Location service, used for complex subtree operations
 * @example Examples/location.php
 * @package ezp\PublicAPI\Interfaces
 */
interface LocationService
{

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     * Only the items on which the user has read access are copied.
     * @param Location $subtree - the subtree denoted by the location to copy
     * @param Location $targetParentLocation - the target parent location for the copy operation
     * @return Location The newly created location of the copied subtree
     * @todo enhancment - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed copy the subtree to the given parent location
     * @throws ezp\Base\Exception\Forbidden  if the target location is a sub location of the given location
     */
    public function copySubtree( /*Location*/ $subtree,  /*Location*/ $targetParentLocation );

    /**
     * Loads a location object from its $locationId
     * @param integer $locationId
     * @return Location
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to read this location
     * @throws ezp\Base\Exception\NotFound If the specified location is not found
     */
    public function loadLocation( $locationId );

    /**
     * Loads a location object from its $remoteId
     * @param string $remoteId
     * @return \ezp\PublicAPI\Values\Content\Location
     * @throws \ezp\Base\Exception\Unauthorized If the current user user is not allowed to read this location
     * @throws \ezp\Base\Exception\NotFound If the specified location is not found
     */
    public function loadLocationByRemoteId( $remotenId );

    /**
     * Load children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param Location $location
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     * @return array of {@link Location}
     */
    public function loadLocationChildren( /*Location*/ $location, $offset = 0, $limit = -1 );

    /**
     * Creates the new $location in the content repository for the given content
     *
     * @param ContentInfo $contentInfo
     * @param LocationCreate $location
     * @return Location the newly created Location
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to create this location
     * @throws ezp\Base\Exception\Forbidden  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId existis already
     */
    public function createLocation(/*ContentInfo*/ $contentInfo, /*LocationCreate*/ $locationCreate );

    /**
     * Updates $location in the content repository
     * @param Location $location
     * @param LocationUpdate $locationUpdate
     * @return Location the updated Location
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to update this location
     * @throws ezp\Base\Exception\Forbidden   if if set the remoteId existis already
     */
    public function updateLocation( /*Location*/ $location, /*LocationUpdate*/ $locationUpdate );

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @param Location $location1
     * @param Location $location2
     * @return void
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to swap content
     */
    public function swapLoaction( /*Location*/ $location1,  /*Location*/ $location2 );

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @param Location $location
     * @return Location $location, with updated hidden value
     * @throws \ezp\Base\Exception\Unauthorized If the current user user is not allowed to hide this location
     */
    public function hideLocation( /*Location*/ $location );

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @param Location $location
     * @return Location $location, with updated hidden value
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to unhide this location
     */
    public function unhideLocation( /*Location*/ $location );

    /**
     * Moves the subtree to $newParentLocation  If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @param Location $location
     * @param Location $newParentLocation
     * @return void
     * @throws ezp\Base\Exception\Unauthorized If the current user user is not allowed to move this location to the target
     */
    public function moveSubtree( /*Location*/ $location, /*Location*/ $newParentLocation );

    /**
     * Deletes the $locations and all descendants of $location.
     * If $overridePermissions is set to false and a user has no permission to delete a descendant
     * it is not deleted and the location path to this item is left untouched i.e. only the
     * locations on which the user has permission to delete are deleted.
     * Otherwise if $overridePermissions is set to true (default) the method deletes all descendants
     * regardles of the permission settings.
     *
     * @param Location $location
     * @param boolean $overridePermissions
     * @throws ezp\Base\Exception\Unauthorized If the current user is not allowed to delete this location
     *
     */
    public function deleteLocation( /*Location*/ $location, $overridePermissions = true );


    /**
     * instanciates a new location create class
     * @param int $parentLocationId the parent under which the new location should be created
     * @return LocationCreate
     */
    public function newLocationCreate($parentLocationId);

    /**
     * instanciates a new location update class
     * @return LocationUpdate
     */
    public function newLocationUpdate();
}

