<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

abstract class LocationServiceDecorator implements LocationService
{
    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $service
     */
    public function __construct(LocationService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function copySubtree(Location $subtree, Location $targetParentLocation)
    {
        return $this->service->copySubtree($subtree, $targetParentLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocation($locationId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        return $this->service->loadLocation($locationId, $prioritizedLanguages, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationList(array $locationIds, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null): iterable
    {
        return $this->service->loadLocationList($locationIds, $prioritizedLanguages, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationByRemoteId($remoteId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        return $this->service->loadLocationByRemoteId($remoteId, $prioritizedLanguages, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocations(ContentInfo $contentInfo, Location $rootLocation = null, array $prioritizedLanguages = null)
    {
        return $this->service->loadLocations($contentInfo, $rootLocation, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLocationChildren(Location $location, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        return $this->service->loadLocationChildren($location, $offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadParentLocationsForDraftContent(VersionInfo $versionInfo, array $prioritizedLanguages = null)
    {
        return $this->service->loadParentLocationsForDraftContent($versionInfo, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocationChildCount(Location $location)
    {
        return $this->service->getLocationChildCount($location);
    }

    /**
     * {@inheritdoc}
     */
    public function createLocation(ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct)
    {
        return $this->service->createLocation($contentInfo, $locationCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLocation(Location $location, LocationUpdateStruct $locationUpdateStruct)
    {
        return $this->service->updateLocation($location, $locationUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function swapLocation(Location $location1, Location $location2)
    {
        return $this->service->swapLocation($location1, $location2);
    }

    /**
     * {@inheritdoc}
     */
    public function hideLocation(Location $location)
    {
        return $this->service->hideLocation($location);
    }

    /**
     * {@inheritdoc}
     */
    public function unhideLocation(Location $location)
    {
        return $this->service->unhideLocation($location);
    }

    /**
     * {@inheritdoc}
     */
    public function moveSubtree(Location $location, Location $newParentLocation)
    {
        return $this->service->moveSubtree($location, $newParentLocation);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteLocation(Location $location)
    {
        return $this->service->deleteLocation($location);
    }

    /**
     * {@inheritdoc}
     */
    public function newLocationCreateStruct($parentLocationId, ContentType $contentType = null)
    {
        return $this->service->newLocationCreateStruct($parentLocationId, $contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function newLocationUpdateStruct()
    {
        return $this->service->newLocationUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllLocationsCount(): int
    {
        return $this->service->getAllLocationsCount();
    }

    /**
     * {@inheritdoc}
     */
    public function loadAllLocations(int $offset = 0, int $limit = 25): array
    {
        return $this->service->loadAllLocations($offset, $limit);
    }
}
