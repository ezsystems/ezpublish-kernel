<?php
/**
 * LocationService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\RequestCache;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;

/**
 * LocationService class
 * @package eZ\Publish\Core\RequestCache
 */
class LocationService implements LocationServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $service;

    /**
     * CachePool
     *
     * @var \eZ\Publish\Core\RequestCache\CachePool
     */
    protected $cachePool;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\LocationService $service
     * @param \eZ\Publish\Core\RequestCache\CachePool $cachePool
     */
    public function __construct( LocationServiceInterface $service, CachePool $cachePool )
    {
        $this->service = $service;
        $this->cachePool = $cachePool;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     *
     * @todo enhancement - this method should return a result structure containing the new location and a list
     *       of locations which are not copied due to permission denials.
     */
    public function copySubtree( Location $subtree, Location $targetParentLocation )
    {
        return $this->service->copySubtree( $subtree, $targetParentLocation );
    }

    /**
     * Loads a location object from its $locationId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param integer $locationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocation( $locationId )
    {
        return $this->service->loadLocation( $locationId );
    }

    /**
     * Loads a location object from its $remoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to read this location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the specified location is not found
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function loadLocationByRemoteId( $remoteId )
    {
        return $this->service->loadLocationByRemoteId( $remoteId );
    }

    /**
     * Loads the locations for the given content object.
     *
     * If a $rootLocation is given, only locations that belong to this location are returned.
     * The location list is also filtered by permissions on reading locations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if there is no published version yet
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $rootLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[] An array of {@link Location}
     */
    public function loadLocations( ContentInfo $contentInfo, Location $rootLocation = null )
    {
        return $this->service->loadLocations( $contentInfo, $rootLocation );
    }

    /**
     * Load children which are readable by the current user of a location object sorted by sortField and sortOrder
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of locations returned. If $limit = -1 all children starting at $offset are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationList
     */
    public function loadLocationChildren( Location $location, $offset = 0, $limit = -1 )
    {
        return $this->service->loadLocationChildren( $location, $offset, $limit );
    }

    /**
     * Returns the number of children which are readable by the current user of a location object
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return int
     */
    public function getLocationChildCount( Location $location )
    {
        return $this->service->getLocationChildCount( $location );
    }

    /**
     * Creates the new $location in the content repository for the given content
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  if the content is already below the specified parent
     *                                        or the parent is a sub location of the location the content
     *                                        or if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created Location
     */
    public function createLocation( ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        $location = $this->service->createLocation( $contentInfo, $locationCreateStruct );
        $this->cachePool->remove( 'content_' . $contentInfo->id );
        return $location;
    }

    /**
     * Updates $location in the content repository
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to update this location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException   if if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct $locationUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the updated Location
     */
    public function updateLocation( Location $location, LocationUpdateStruct $locationUpdateStruct )
    {
        $location = $this->service->updateLocation( $location, $locationUpdateStruct );
        $this->cachePool->purge();
        return $location;
    }

    /**
     * Swaps the contents hold by the $location1 and $location2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation( Location $location1, Location $location2 )
    {
        $this->service->swapLocation( $location1, $location2 );
        $this->cachePool->purge();
    }

    /**
     * Hides the $location and marks invisible all descendants of $location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to hide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function hideLocation( Location $location )
    {
        $location = $this->service->hideLocation( $location );
        $this->cachePool->purge();
        return $location;
    }

    /**
     * Unhides the $location.
     *
     * This method and marks visible all descendants of $locations
     * until a hidden location is found.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to unhide this location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location $location, with updated hidden value
     */
    public function unhideLocation( Location $location )
    {
        return $this->service->unhideLocation( $location );
    }

    /**
     * Moves the subtree to $newParentLocation
     *
     * If a user has the permission to move the location to a target location
     * he can do it regardless of an existing descendant on which the user has no permission.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to move this location to the target
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     */
    public function moveSubtree( Location $location, Location $newParentLocation )
    {
        $this->service->moveSubtree( $location, $newParentLocation );
        $this->cachePool->purge();
    }

    /**
     * Deletes $location and all its descendants.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user is not allowed to delete this location or a descendant
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     */
    public function deleteLocation( Location $location )
    {
        $this->service->deleteLocation( $location );
        $this->cachePool->purge();
    }

    /**
     * Instantiates a new location create class
     *
     * @param mixed $parentLocationId the parent under which the new location should be created
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     */
    public function newLocationCreateStruct( $parentLocationId )
    {
        return $this->service->newLocationCreateStruct( $parentLocationId );
    }

    /**
     * Instantiates a new location update class
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct
     */
    public function newLocationUpdateStruct()
    {
        return $this->service->newLocationUpdateStruct();
    }
}
