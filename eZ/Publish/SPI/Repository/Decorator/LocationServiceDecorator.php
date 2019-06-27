<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

abstract class LocationServiceDecorator implements LocationService
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $innerService;

    public function __construct(LocationService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function copySubtree(
        Location $subtree,
        Location $targetParentLocation
    ) {
        return $this->innerService->copySubtree($subtree, $targetParentLocation);
    }

    public function loadLocation(
        $locationId,
        array $prioritizedLanguages = null,
        bool $useAlwaysAvailable = null
    ) {
        return $this->innerService->loadLocation($locationId, $prioritizedLanguages, $useAlwaysAvailable);
    }

    public function loadLocationList(
        array $locationIds,
        array $prioritizedLanguages = null,
        bool $useAlwaysAvailable = null
    ): iterable {
        return $this->innerService->loadLocationList($locationIds, $prioritizedLanguages, $useAlwaysAvailable);
    }

    public function loadLocationByRemoteId(
        $remoteId,
        array $prioritizedLanguages = null,
        bool $useAlwaysAvailable = null
    ) {
        return $this->innerService->loadLocationByRemoteId($remoteId, $prioritizedLanguages, $useAlwaysAvailable);
    }

    public function loadLocations(
        ContentInfo $contentInfo,
        Location $rootLocation = null,
        array $prioritizedLanguages = null
    ) {
        return $this->innerService->loadLocations($contentInfo, $rootLocation, $prioritizedLanguages);
    }

    public function loadLocationChildren(
        Location $location,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = null
    ) {
        return $this->innerService->loadLocationChildren($location, $offset, $limit, $prioritizedLanguages);
    }

    public function loadParentLocationsForDraftContent(
        VersionInfo $versionInfo,
        array $prioritizedLanguages = null
    ) {
        return $this->innerService->loadParentLocationsForDraftContent($versionInfo, $prioritizedLanguages);
    }

    public function getLocationChildCount(Location $location)
    {
        return $this->innerService->getLocationChildCount($location);
    }

    public function createLocation(
        ContentInfo $contentInfo,
        LocationCreateStruct $locationCreateStruct
    ) {
        return $this->innerService->createLocation($contentInfo, $locationCreateStruct);
    }

    public function updateLocation(
        Location $location,
        LocationUpdateStruct $locationUpdateStruct
    ) {
        return $this->innerService->updateLocation($location, $locationUpdateStruct);
    }

    public function swapLocation(
        Location $location1,
        Location $location2
    ) {
        return $this->innerService->swapLocation($location1, $location2);
    }

    public function hideLocation(Location $location)
    {
        return $this->innerService->hideLocation($location);
    }

    public function unhideLocation(Location $location)
    {
        return $this->innerService->unhideLocation($location);
    }

    public function moveSubtree(
        Location $location,
        Location $newParentLocation
    ) {
        return $this->innerService->moveSubtree($location, $newParentLocation);
    }

    public function deleteLocation(Location $location)
    {
        return $this->innerService->deleteLocation($location);
    }

    public function newLocationCreateStruct($parentLocationId)
    {
        return $this->innerService->newLocationCreateStruct($parentLocationId);
    }

    public function newLocationUpdateStruct()
    {
        return $this->innerService->newLocationUpdateStruct();
    }

    public function getAllLocationsCount(): int
    {
        return $this->innerService->getAllLocationsCount();
    }

    public function loadAllLocations(
        int $offset = 0,
        int $limit = 25
    ): array {
        return $this->innerService->loadAllLocations($offset, $limit);
    }
}
