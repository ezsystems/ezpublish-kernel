<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Traversable;

/**
 * @internal
 */
interface SiteAccessProviderInterface
{
    public function isDefined(string $name): bool;

    public function getSiteAccess(string $name): SiteAccess;

    public function getSiteAccesses(): Traversable;
}
