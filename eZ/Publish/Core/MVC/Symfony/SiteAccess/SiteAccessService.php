<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use function iterator_to_array;

class SiteAccessService implements SiteAccessServiceInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface */
    private $provider;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteAccess;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        SiteAccessProviderInterface $provider,
        ConfigResolverInterface $configResolver
    ) {
        $this->provider = $provider;
        $this->configResolver = $configResolver;
    }

    public function setSiteAccess(SiteAccess $siteAccess = null): void
    {
        $this->siteAccess = $siteAccess;
    }

    public function exists(string $name): bool
    {
        return $this->provider->isDefined($name);
    }

    public function get(string $name): SiteAccess
    {
        if ($this->provider->isDefined($name)) {
            return $this->provider->getSiteAccess($name);
        }

        throw new NotFoundException('SiteAccess', $name);
    }

    public function getAll(): iterable
    {
        return $this->provider->getSiteAccesses();
    }

    public function getCurrent(): SiteAccess
    {
        return $this->siteAccess;
    }

    public function getSiteAccessesRelation(?SiteAccess $siteAccess = null): array
    {
        $siteAccess = $siteAccess ?? $this->siteAccess;
        $saRelationMap = [];

        $saList = iterator_to_array($this->provider->getSiteAccesses());
        // First build the SiteAccess relation map, indexed by repository and rootLocationId.
        /** @var SiteAccess $siteAccess */
        foreach ($saList as $sa) {
            $siteAccessName = $sa->name;

            $repository = $this->configResolver->getParameter('repository', 'ezsettings', $siteAccessName);
            if (!isset($saRelationMap[$repository])) {
                $saRelationMap[$repository] = [];
            }

            $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', 'ezsettings', $siteAccessName);
            if (!isset($saRelationMap[$repository][$rootLocationId])) {
                $saRelationMap[$repository][$rootLocationId] = [];
            }

            $saRelationMap[$repository][$rootLocationId][] = $siteAccessName;
        }

        $siteAccessName = $siteAccess->name;
        $repository = $this->configResolver->getParameter('repository', 'ezsettings', $siteAccessName);
        $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id', 'ezsettings', $siteAccessName);

        return $saRelationMap[$repository][$rootLocationId];
    }
}
