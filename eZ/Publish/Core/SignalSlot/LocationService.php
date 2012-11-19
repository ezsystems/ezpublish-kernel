<?php
/**
 * LocationService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\CreateLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\UpdateLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\UnhideLocationSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;
use eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal;

/**
 * LocationService class
 * @package eZ\Publish\Core\SignalSlot
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
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\LocationService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( LocationServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Copies the subtree starting from $subtree as a new subtree of $targetLocation
     *
     * Only the items on which the user has read access are copied.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed copy the subtree to the given parent location
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user does not have read access to the whole source subtree
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the target location is a sub location of the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $subtree - the subtree denoted by the location to copy
     * @param \eZ\Publish\API\Repository\Values\Content\Location $targetParentLocation - the target parent location for the copy operation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location The newly created location of the copied subtree
     */
    public function copySubtree( Location $subtree, Location $targetParentLocation )
    {
        $returnValue = $this->service->copySubtree( $subtree, $targetParentLocation );
        $this->signalDispatcher->emit(
            new CopySubtreeSignal(
                array(
                    'subtreeId' => $subtree->id,
                    'targetParentLocationId' => $targetParentLocation->id,
                )
            )
        );
        return $returnValue;
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
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the content is already below the specified parent
     *                                        or the parent is a sub location of the location of the content
     *                                        or if set the remoteId exists already
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created Location
     *
     */
    public function createLocation( ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct )
    {
        $returnValue = $this->service->createLocation( $contentInfo, $locationCreateStruct );
        $this->signalDispatcher->emit(
            new CreateLocationSignal(
                array(
                    'contentId' => $contentInfo->id,
                    'locationId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
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
        $returnValue = $this->service->updateLocation( $location, $locationUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdateLocationSignal(
                array(
                    'contentId' => $location->contentId,
                    'locationId' => $location->id,
                )
            )
        );
        return $returnValue;
    }

    /**
     * Swaps the contents held by $location1 and $location2
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to swap content
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location1
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location2
     */
    public function swapLocation( Location $location1, Location $location2 )
    {
        $returnValue = $this->service->swapLocation( $location1, $location2 );
        $this->signalDispatcher->emit(
            new SwapLocationSignal(
                array(
                    'location1Id' => $location1->id,
                    'content1Id' => $location1->contentId,
                    'location2Id' => $location2->id,
                    'content2Id' => $location2->contentId,
                )
            )
        );
        return $returnValue;
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
        $returnValue = $this->service->hideLocation( $location );
        $this->signalDispatcher->emit(
            new HideLocationSignal(
                array(
                    'locationId' => $location->id,
                )
            )
        );
        return $returnValue;
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
        $returnValue = $this->service->unhideLocation( $location );
        $this->signalDispatcher->emit(
            new UnhideLocationSignal(
                array(
                    'locationId' => $location->id,
                )
            )
        );
        return $returnValue;
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
        $returnValue = $this->service->moveSubtree( $location, $newParentLocation );
        $this->signalDispatcher->emit(
            new MoveSubtreeSignal(
                array(
                    'locationId' => $location->id,
                    'newParentLocationId' => $newParentLocation->id,
                )
            )
        );
        return $returnValue;
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
        $returnValue = $this->service->deleteLocation( $location );
        $this->signalDispatcher->emit(
            new DeleteLocationSignal(
                array(
                    'contentId' => $location->contentId,
                    'locationId' => $location->id,
                )
            )
        );
        return $returnValue;
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
