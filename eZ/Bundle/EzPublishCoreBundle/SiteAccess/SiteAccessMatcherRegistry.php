<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);
namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class SiteAccessMatcherRegistry
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher[] */
    protected $matchers;

    /**
     * @param \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher[] $matchers
     */
    public function __construct(array $matchers = [])
    {
        $this->matchers = $matchers;
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * @param \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Matcher[] $matchers
     */
    public function setMatchers(array $matchers): void
    {
        $this->matchers = $matchers;
    }

    public function setMatcher(string $identifier, Matcher $matcher): void
    {
        $this->matchers[$identifier] = $matcher;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getMatcher(string $identifier): Matcher
    {
        if (!$this->hasMatcher($identifier)) {
            throw new NotFoundException('SiteAccess Matcher', $identifier);
        }

        return $this->matchers[$identifier];
    }

    public function hasMatcher(string $identifier): bool
    {
        return isset($this->matchers[$identifier]);
    }
}
