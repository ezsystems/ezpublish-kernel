<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use AppendIterator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;

class SiteAccessService implements SiteAccessServiceInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface[]
     */
    private $providers;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccessGroup[]
     */
    private $siteAccessGroups = [];

    public function __construct(iterable $providers, array $siteAccessGroups)
    {
        $this->providers = $providers;

        foreach ($siteAccessGroups as $name) {
            $this->siteAccessGroups[] = new SiteAccessGroup($name);
        }
    }

    public function exists(string $name): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->isDefined($name)) {
                return true;
            }
        }

        return false;
    }

    public function get(string $name): SiteAccess
    {
        foreach ($this->providers as $provider) {
            if ($provider->isDefined($name)) {
                return $provider->getSiteAccess($name);
            }
        }

        throw new NotFoundException('SiteAccess', $name);
    }

    public function getAll(): iterable
    {
        $iterator = new AppendIterator();
        foreach ($this->providers as $provider) {
            $iterator->append($provider->getSiteAccesses());
        }

        return $iterator;
    }

    public function getAvailableGroups(): iterable
    {
        return $this->siteAccessGroups;
    }
}
