<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location;

class URLAliasServiceDecorator implements URLAliasService
{
    /**
     * @var \eZ\Publish\API\Repository\URLAliasService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\URLAliasService $service
     */
    public function __construct(URLAliasService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrlAlias(Location $location, $path, $languageCode, $forwarding = false, $alwaysAvailable = false)
    {
        return $this->service->createUrlAlias($location, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function createGlobalUrlAlias($resource, $path, $languageCode, $forwarding = false, $alwaysAvailable = false)
    {
        return $this->service->createGlobalUrlAlias($resource, $path, $languageCode, $forwarding, $alwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocationAliases(Location $location, $custom = true, $languageCode = null)
    {
        return $this->service->listLocationAliases($location, $custom, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function listGlobalAliases($languageCode = null, $offset = 0, $limit = -1)
    {
        return $this->service->listGlobalAliases($languageCode, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAliases(array $aliasList)
    {
        return $this->service->removeAliases($aliasList);
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($url, $languageCode = null)
    {
        return $this->service->lookup($url, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseLookup(Location $location, $languageCode = null, bool $showAllTranslations = null, array $prioritizedLanguageList = null)
    {
        return $this->service->reverseLookup($location, $languageCode, $showAllTranslations, $prioritizedLanguageList);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        return $this->service->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshSystemUrlAliasesForLocation(Location $location): void
    {
        $this->service->refreshSystemUrlAliasesForLocation($location);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCorruptedUrlAliases(): int
    {
        return $this->service->deleteCorruptedUrlAliases();
    }
}
