<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\LocationUpdateStruct;
use ezp\PublicAPI\Values\Content\LocationCreateStruct;
use ezp\PublicAPI\Values\Content\Content;
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
     * Only the items on which the user has read access are copied.
     *
     * @param Location $subtree - the subtree denoted by the location to copy
     * @param Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return Location The newly created location of the copied subtree
     *
<<<<<<< HEAD
     * @todo enhancement - this method should return a result structure containing the new location and a list
=======
     * @todo enhancment - this method should return a result structure containing the new location and a list
>>>>>>> Whitespaces + doc fixes
     *       of locations which are not copied due to permission denials.
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException  if the target location is a sub location of the given location
     */
    public function copySubtree( /*Location*/ $subtree,  /*Location*/ $targetParentLocation );

    /**
     * Loads a location object from its $locationId
     *
     * @param integer $locationId
     *
     * @return Location
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read this location
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If the specified location is not found
     */
    public function loadLocation( $locationId );

    /**
     * Loads a location object from its $remoteId
     *
     * @param string $remoteId
     *
     * @return Location
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read this location
     * @throws ezp\PublicAPI\Interfaces\NotFoundException If the specified location is not found
     */
    public function loadLocationByRemoteId( $remoteId );

    /**
     * loads the main loaction of a content object
     *
     * @param Content $content
     *
     * @return Location (in 5.x the return value also can be null if the content has no location)
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to read this location
     * @throws ezp\PublicAPI\Interfaces\BadStateException if there is no published version yet
     */
    public Function loadMainLocation(/*Content*/ $content);

    /**
<<<<<<< HEAD
     * Loads the locations for the given content object. If a $rootLocation is given only
     * locations of the content which are descendants of the root location are returnd
=======
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location re returned.
>>>>>>> Whitespaces + doc fixes
     * The location list is also filtered by permissions on reading locations.
     *
     * @param Content $content
     * @param Location $rootLocation
     *
     * @return array an array of {@link Location}
     *
     * @throws ezp\PublicAPI\Interfaces\BadStateException if there is no published version yet
     */
    public function loadLocations(/*Content*/ $content, /*Location*/ $rootLocation = null);

    /**
     * Load children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return array of {@link Location}
     */
    public function loadLocationChildren( /*Location*/ $location, $offset = 0, $limit = -1 );

    /**
     * Creates the new $location in the content repository for the given content
     *
     * @param Content $content
     *
     * @param LocationCreateStruct $location
     *
     * @return Location the newly created Location
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to create this location
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId existis already
     */
    public function createLocation(/*Content*/ $content, /*LocationCreate*/ $locationCreateStruct );

    /**
     * Updates $location in the content repository
     *
     * @param Location $location
     * @param LocationUpdateStruct $locationUpdateStruct
     *
     * @return Location the updated Location
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to update this location
     * @throws ezp\PublicAPI\Interfaces\IllegalArgumentException   if if set the remoteId existis already
     */
    public function updateLocation( /*Location*/ $location, /*LocationUpdate*/ $locationUpdateStruct );

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @param Location $location1
     * @param Location $location2
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to swap content
     */
    public function swapLocation( /*Location*/ $location1,  /*Location*/ $location2 );

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @param Location $location
     *
     * @return Location $location, with updated hidden value
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to hide this location
     */
    public function hideLocation( /*Location*/ $location );

    /**
     * Unhides the $location and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @param Location $location
     *
     * @return Location $location, with updated hidden value
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to unhide this location
     */
    public function unhideLocation( /*Location*/ $location );

    /**
     * Moves the subtree to $newParentLocation  If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @param Location $location
     * @param Location $newParentLocation
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user user is not allowed to move this location to the target
     */
    public function moveSubtree( /*Location*/ $location, /*Location*/ $newParentLocation );

    /**
     * Deletes $location and all its descendants.
     * If $overridePermissions is set to false and a user has no permission to delete a descendant
     * it is not deleted and the location path to this item is left untouched i.e. only the
     * locations on which the user has permission to delete are deleted.
     * Otherwise if $overridePermissions is set to true (default) the method deletes all descendants
     * regardles of the permission settings.
     *
     * @param Location $location
     * @param boolean $overridePermissions
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException If the current user is not allowed to delete this location
     */
    public function deleteLocation( /*Location*/ $location, $overridePermissions = true );


    /**
<<<<<<< HEAD
     * instantiates a new location create class
=======
     * instanciates a new location create class
>>>>>>> Whitespaces + doc fixes
     *
     * @param int $parentLocationId the parent under which the new location should be created
     *
     * @return LocationCreateStruct
     */
    public function newLocationCreateStruct($parentLocationId);

    /**
     * instanciates a new location update class
     *
     * @return LocationUpdateStruct
     */
    public function newLocationUpdateStruct();
}

