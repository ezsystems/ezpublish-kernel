<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\Values\Content\URLAlias;

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

    public function createUrlAlias(
        Location $location,
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        return $this->service->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function createGlobalUrlAlias(
        string $resource,
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        return $this->service->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function listLocationAliases(
        Location $location,
        bool $custom = true,
        ?string $languageCode = null,
        ?bool $showAllTranslations = null,
        ?array $prioritizedLanguages = null
    ): iterable {
        return $this->service->listLocationAliases(
            $location,
            $custom,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function listGlobalAliases(?string $languageCode = null, int $offset = 0, int $limit = -1): iterable
    {
        return $this->service->listGlobalAliases($languageCode, $offset, $limit);
    }

    public function removeAliases(array $aliasList): void
    {
        $this->service->removeAliases($aliasList);
    }

    public function lookup(string $url, ?string $languageCode = null): URLAlias
    {
        return $this->service->lookup($url, $languageCode);
    }

    public function reverseLookup(
        Location $location,
        ?string $languageCode = null,
        ?bool $showAllTranslations = null,
        ?array $prioritizedLanguages = null
    ): URLAlias {
        return $this->service->reverseLookup(
            $location,
            $languageCode,
            $this->languageResolver->getShowAllTranslations($showAllTranslations),
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function load(string $id): URLAlias
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
