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
    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
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
        $this->innerService->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function createGlobalUrlAlias(
        $resource,
        $path,
        $languageCode,
        $forwarding = false,
        $alwaysAvailable = false
    ) {
        $this->innerService->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function listLocationAliases(
        Location $location,
        $custom = true,
        $languageCode = null
    ) {
        $this->innerService->listLocationAliases($location, $custom, $languageCode);
    }

    public function listGlobalAliases(
        $languageCode = null,
        $offset = 0,
        $limit = -1
    ) {
        $this->innerService->listGlobalAliases($languageCode, $offset, $limit);
    }

    public function removeAliases(array $aliasList)
    {
        $this->innerService->removeAliases($aliasList);
    }

    public function lookup(
        $url,
        $languageCode = null
    ) {
        $this->innerService->lookup($url, $languageCode);
    }

    public function reverseLookup(
        Location $location,
        $languageCode = null
    ) {
        $this->innerService->reverseLookup($location, $languageCode);
    }

    public function load($id)
    {
        $this->innerService->load($id);
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
