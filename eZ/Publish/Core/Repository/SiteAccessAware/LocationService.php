<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\Values\Filter\Filter;

/**
 * LocationService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class LocationService implements LocationServiceInterface
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $service;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\LocationService $service
     * @param \eZ\Publish\API\Repository\LanguageResolver $languageResolver
     */
    public function __construct(
        LocationServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function copySubtree(Location $subtree, Location $targetParentLocation): Location
    {
        return $this->service->copySubtree($subtree, $targetParentLocation);
    }

    public function loadLocation(int $locationId, ?array $prioritizedLanguages = null, ?bool $useAlwaysAvailable = null): Location
    {
        return $this->service->loadLocation(
            $locationId,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocationList(array $locationIds, ?array $prioritizedLanguages = null, ?bool $useAlwaysAvailable = null): iterable
    {
        return $this->service->loadLocationList(
            $locationIds,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocationByRemoteId(string $remoteId, ?array $prioritizedLanguages = null, ?bool $useAlwaysAvailable = null): Location
    {
        return $this->service->loadLocationByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocations(ContentInfo $contentInfo, ?Location $rootLocation = null, ?array $prioritizedLanguages = null): iterable
    {
        return $this->service->loadLocations(
            $contentInfo,
            $rootLocation,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function loadLocationChildren(Location $location, int $offset = 0, int $limit = 25, ?array $prioritizedLanguages = null): LocationList
    {
        return $this->service->loadLocationChildren(
            $location,
            $offset,
            $limit,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function loadParentLocationsForDraftContent(VersionInfo $versionInfo, ?array $prioritizedLanguages = null): iterable
    {
        return $this->service->loadParentLocationsForDraftContent(
            $versionInfo,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function getLocationChildCount(Location $location): int
    {
        return $this->service->getLocationChildCount($location);
    }

    public function createLocation(ContentInfo $contentInfo, LocationCreateStruct $locationCreateStruct): Location
    {
        return $this->service->createLocation($contentInfo, $locationCreateStruct);
    }

    public function updateLocation(Location $location, LocationUpdateStruct $locationUpdateStruct): Location
    {
        return $this->service->updateLocation($location, $locationUpdateStruct);
    }

    public function swapLocation(Location $location1, Location $location2): void
    {
        $this->service->swapLocation($location1, $location2);
    }

    public function hideLocation(Location $location): Location
    {
        return $this->service->hideLocation($location);
    }

    public function unhideLocation(Location $location): Location
    {
        return $this->service->unhideLocation($location);
    }

    public function moveSubtree(Location $location, Location $newParentLocation): void
    {
        $this->service->moveSubtree($location, $newParentLocation);
    }

    public function deleteLocation(Location $location): void
    {
        $this->service->deleteLocation($location);
    }

    public function newLocationCreateStruct(int $parentLocationId): LocationCreateStruct
    {
        return $this->service->newLocationCreateStruct($parentLocationId);
    }

    public function newLocationUpdateStruct(): LocationUpdateStruct
    {
        return $this->service->newLocationUpdateStruct();
    }

    /**
     * Get the total number of all existing Locations. Can be combined with loadAllLocations.
     *
     * @see loadAllLocations
     *
     * @return int Total number of Locations
     */
    public function getAllLocationsCount(): int
    {
        return $this->service->getAllLocationsCount();
    }

    /**
     * Bulk-load all existing Locations, constrained by $limit and $offset to paginate results.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     */
    public function loadAllLocations(int $offset = 0, int $limit = 25): array
    {
        return $this->service->loadAllLocations($offset, $limit);
    }

    public function find(Filter $filter, ?array $languages = null): LocationList
    {
        return $this->service->find(
            $filter,
            $this->languageResolver->getPrioritizedLanguages($languages)
        );
    }
}
