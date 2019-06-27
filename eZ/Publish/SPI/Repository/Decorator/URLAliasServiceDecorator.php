<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;

abstract class URLAliasServiceDecorator implements URLAliasService
{
    /** @var \eZ\Publish\API\Repository\URLAliasService */
    protected $innerService;

    public function __construct(URLAliasService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createUrlAlias(
        Location $location,
        $path,
        $languageCode,
        $forwarding = false,
        $alwaysAvailable = false
    ) {
        return $this->innerService->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function createGlobalUrlAlias(
        $resource,
        $path,
        $languageCode,
        $forwarding = false,
        $alwaysAvailable = false
    ) {
        return $this->innerService->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function listLocationAliases(
        Location $location,
        $custom = true,
        $languageCode = null
    ) {
        return $this->innerService->listLocationAliases($location, $custom, $languageCode);
    }

    public function listGlobalAliases(
        $languageCode = null,
        $offset = 0,
        $limit = -1
    ) {
        return $this->innerService->listGlobalAliases($languageCode, $offset, $limit);
    }

    public function removeAliases(array $aliasList)
    {
        return $this->innerService->removeAliases($aliasList);
    }

    public function lookup(
        $url,
        $languageCode = null
    ) {
        return $this->innerService->lookup($url, $languageCode);
    }

    public function reverseLookup(
        Location $location,
        $languageCode = null
    ) {
        return $this->innerService->reverseLookup($location, $languageCode);
    }

    public function load($id)
    {
        return $this->innerService->load($id);
    }

    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $this->innerService->refreshSystemUrlAliasesForLocation($location);
    }

    public function deleteCorruptedUrlAliases(): int
    {
        return $this->innerService->deleteCorruptedUrlAliases();
    }
}
