<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
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
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        return $this->innerService->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function createGlobalUrlAlias(
        string $resource,
        string $path,
        string $languageCode,
        bool $forwarding = false,
        bool $alwaysAvailable = false
    ): URLAlias {
        return $this->innerService->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    public function listLocationAliases(
        Location $location,
        bool $custom = true,
        ?string $languageCode = null
    ): iterable {
        return $this->innerService->listLocationAliases($location, $custom, $languageCode);
    }

    public function listGlobalAliases(
        ?string $languageCode = null,
        int $offset = 0,
        int $limit = -1
    ): iterable {
        return $this->innerService->listGlobalAliases($languageCode, $offset, $limit);
    }

    public function removeAliases(array $aliasList): void
    {
        $this->innerService->removeAliases($aliasList);
    }

    public function lookup(
        string $url,
        ?string $languageCode = null
    ): URLAlias {
        return $this->innerService->lookup($url, $languageCode);
    }

    public function reverseLookup(
        Location $location,
        ?string $languageCode = null
    ): URLAlias {
        return $this->innerService->reverseLookup($location, $languageCode);
    }

    public function load(string $id): URLAlias
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
