<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony;

final class SiteAccessList implements \IteratorAggregate
{
    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess[] */
    private $siteAccesses;

    public function __construct(array $siteAccesses)
    {
        $this->siteAccesses = $siteAccesses;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->siteAccesses);
    }
}
