<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use Traversable;

/**
 * @internal For internal use only, do not rely on this class.
 *           Be aware this should be regarded as experimental feature.
 *           As in, method signatures and behavior might change in the future.
 */
final class StaticSiteAccessProvider implements SiteAccessProviderInterface
{
    /** @var string[] */
    private $siteAccessList;

    /**
     * @param string[] $siteAccessList
     */
    public function __construct(
        array $siteAccessList
    ) {
        $this->siteAccessList = $siteAccessList;
    }

    public function getSiteAccesses(): Traversable
    {
        foreach ($this->siteAccessList as $name) {
            yield new SiteAccess($name, SiteAccess::DEFAULT_MATCHING_TYPE, null, self::class);
        }

        yield from [];
    }

    public function isDefined(string $name): bool
    {
        return \in_array($name, $this->siteAccessList, true);
    }

    public function getSiteAccess(string $name): SiteAccess
    {
        if ($this->isDefined($name)) {
            return new SiteAccess($name, SiteAccess::DEFAULT_MATCHING_TYPE, null, self::class);
        }

        throw new NotFoundException('Site Access', $name);
    }
}
