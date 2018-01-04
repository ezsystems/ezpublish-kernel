<?php

/**
 * LocationService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;

/**
 * LocationService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class LocationService implements LocationServiceInterface
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\LocationService $service
     */
    public function __construct(
        LocationServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function copySubtree(Location $subtree, Location $targetParentLocation)
    {
        return $this->service->copySubtree($subtree, $targetParentLocation);
    }

    public function loadLocation($locationId)
    {
        return $this->service->loadLocation($locationId);
    }

    public function loadLocationByRemoteId($remoteId)
    {
        return $this->service->loadLocationByRemoteId($remoteId);
    }

    public function loadLocations(ContentInfo $contentInfo, Location $rootLocation = null)
    {
        return $this->service->loadLocations($contentInfo, $rootLocation);
    }

    public function loadLocationChildren(Location $location, $offset = 0, $limit = 25)
    {
        return $this->service->loadLocationChildren($location, $offset, $limit);
    }

    public function loadParentLocationsForDraftContent(VersionInfo $versionInfo)
    {
        return $this->service->loadParentLocationsForDraftContent($versionInfo);
    }

    public function getLocationChildCount(Location $location)
    {
        return $this->service->getLocationChildCount($location);
    }

    public function createLocation(ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct)
    {
        return $this->service->createLocation($contentInfo, $locationCreateStruct);
    }

    public function updateLocation(Location $location, LocationUpdateStruct $locationUpdateStruct)
    {
        return $this->service->updateLocation($location, $locationUpdateStruct);
    }

    public function swapLocation(Location $location1, Location $location2)
    {
        return $this->service->swapLocation($location1, $location2);
    }

    public function hideLocation(Location $location)
    {
        return $this->service->hideLocation($location);
    }

    public function unhideLocation(Location $location)
    {
        return $this->service->unhideLocation($location);
    }

    public function moveSubtree(Location $location, Location $newParentLocation)
    {
        return $this->service->moveSubtree($location, $newParentLocation);
    }

    public function deleteLocation(Location $location)
    {
        $this->service->deleteLocation($location);
    }

    public function newLocationCreateStruct($parentLocationId)
    {
        return $this->service->newLocationCreateStruct($parentLocationId);
    }

    public function newLocationUpdateStruct()
    {
        return $this->service->newLocationUpdateStruct();
    }
}
