<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider;

use AppendIterator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccessList;

final class ChainSiteAccessProvider implements SiteAccessProviderInterface
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface[] */
    private $providers;

    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    public function getSiteAccesses(): \Iterator
    {
        $iterator = new AppendIterator();
        foreach($this->providers as $provider) {
            $iterator->append($provider->getSiteAccesses());
        }
        return $iterator;
    }

    public function isDefined(string $name): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->isDefined($name)) {
                return true;
            }
        }

        return false;
    }

    public function getSiteAccess(string $name): SiteAccess
    {
        foreach ($this->providers as $provider) {
            if ($provider->isDefined($name)) {
                return $provider->getSiteAccess($name);
            }
        }

        throw new \RuntimeException("Undefined siteaccess: $name");
    }
}
