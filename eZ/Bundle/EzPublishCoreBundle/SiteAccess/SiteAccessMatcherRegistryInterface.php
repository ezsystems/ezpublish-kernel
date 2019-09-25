<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

/**
 * @internal
 */
interface SiteAccessMatcherRegistryInterface
{
    public function setMatcher(string $identifier, Matcher $matcher): void;

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getMatcher(string $identifier): Matcher;

    public function hasMatcher(string $identifier): bool;
}
