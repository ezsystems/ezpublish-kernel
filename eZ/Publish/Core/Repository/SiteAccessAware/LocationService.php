<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Decorator\LocationServiceDecorator;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * LocationService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class LocationService extends LocationServiceDecorator
{
    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\LocationService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        LocationServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        parent::__construct($service);

        $this->languageResolver = $languageResolver;
    }

    public function loadLocation($locationId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        return $this->service->loadLocation(
            $locationId,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocationList(array $locationIds, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null): iterable
    {
        return $this->service->loadLocationList(
            $locationIds,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocationByRemoteId($remoteId, array $prioritizedLanguages = null, bool $useAlwaysAvailable = null)
    {
        return $this->service->loadLocationByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadLocations(ContentInfo $contentInfo, Location $rootLocation = null, array $prioritizedLanguages = null)
    {
        return $this->service->loadLocations(
            $contentInfo,
            $rootLocation,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function loadLocationChildren(Location $location, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        return $this->service->loadLocationChildren(
            $location,
            $offset,
            $limit,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function loadParentLocationsForDraftContent(VersionInfo $versionInfo, array $prioritizedLanguages = null)
    {
        return $this->service->loadParentLocationsForDraftContent(
            $versionInfo,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }
}
