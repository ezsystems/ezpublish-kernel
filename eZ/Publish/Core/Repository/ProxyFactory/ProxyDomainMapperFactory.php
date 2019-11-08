<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\ProxyFactory;

use eZ\Publish\API\Repository\Repository;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * @internal
 */
final class ProxyDomainMapperFactory
{
    /** @var \ProxyManager\Factory\LazyLoadingValueHolderFactory */
    private $lazyLoadingValueHolderFactory;

    public function __construct(LazyLoadingValueHolderFactory $lazyLoadingValueHolderFactory)
    {
        $this->lazyLoadingValueHolderFactory = $lazyLoadingValueHolderFactory;
    }

    public function create(Repository $repository): ProxyDomainMapperInterface
    {
        return new ProxyDomainMapper($repository, $this->lazyLoadingValueHolderFactory);
    }
}
