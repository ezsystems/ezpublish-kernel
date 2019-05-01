<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;
use eZ\Publish\Core\MVC\Symfony\SiteAccessList;

final class StaticSiteAccessProvider implements SiteAccessProviderInterface
{
    /** @var string[] */
    private $siteAccessList;

    /**
     * @var string[]
     */
    private $groupsBySiteAccess;

    public function __construct(array $siteAccessList, array $groupsBySiteAccess)
    {
        $this->siteAccessList = $siteAccessList;
        $this->groupsBySiteAccess = $groupsBySiteAccess;
    }

    public function getSiteAccesses(): SiteAccessList
    {
        return new SiteAccessList($this->siteAccessList);
    }

    public function isDefined(string $name): bool
    {
        return in_array($name, $this->siteAccessList);
    }

    public function getSiteAccess(string $name): SiteAccess
    {
        $siteaccess = new SiteAccess($name);
        $siteaccess->groups = array_map(function($group) {
            return new SiteAccessGroup($group);
        }, $this->groupsBySiteAccess[$name]);

        return $siteaccess;
    }
}
