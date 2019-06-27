<?php

/**
 * URLAliasService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\LanguageResolver;

/**
 * SiteAccess aware implementation of URLAliasService injecting languages where needed.
 */
class URLAliasService implements URLAliasServiceInterface
{
    /** @var \eZ\Publish\API\Repository\URLAliasService */
    protected $service;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\URLAliasService $service
     * @param \eZ\Publish\API\Repository\LanguageResolver $languageResolver
     */
    public function __construct(
        URLAliasServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function createUrlAlias(Location $location, $path, $languageCode, $forwarding = false, $alwaysAvailable = false)
    {
        return $this->service->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function createGlobalUrlAlias($resource, $path, $languageCode, $forwarding = false, $alwaysAvailable = false)
    {
        return $this->service->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function listLocationAliases(
        Location $location,
        $custom = true,
        $languageCode = null,
        bool $showAllTranslations = null,
        array $prioritizedLanguages = null
    ) {
        return $this->service->listLocationAliases(
            $location,
            $custom,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function listGlobalAliases($languageCode = null, $offset = 0, $limit = -1)
    {
        return $this->service->listGlobalAliases($languageCode, $offset, $limit);
    }

    public function removeAliases(array $aliasList)
    {
        return $this->service->removeAliases($aliasList);
    }

    public function lookup($url, $languageCode = null)
    {
        return $this->service->lookup($url, $languageCode);
    }

    public function reverseLookup(
        Location $location,
        $languageCode = null,
        bool $showAllTranslations = null,
        array $prioritizedLanguages = null
    ) {
        return $this->service->reverseLookup(
            $location,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function load($id)
    {
        return $this->service->load($id);
    }

    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $this->service->refreshSystemUrlAliasesForLocation($location);
    }

    public function deleteCorruptedUrlAliases(): int
    {
        return $this->service->deleteCorruptedUrlAliases();
    }
}
