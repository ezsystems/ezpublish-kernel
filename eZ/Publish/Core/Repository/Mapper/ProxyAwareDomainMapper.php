<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Mapper;

use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface;

/**
 * @internal For internal use by Domain Mappers
 *
 * Common abstraction for domain mappers providing properties loaded via proxy.
 */
abstract class ProxyAwareDomainMapper
{
    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface */
    protected $proxyFactory;

    public function __construct(?ProxyDomainMapperInterface $proxyFactory = null)
    {
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Setter for Proxy Factory to work around cyclic dependency issue on Repository.
     *
     * Note: to be resolved by Repository decoupling.
     */
    final public function setProxyFactory(ProxyDomainMapperInterface $proxyFactory): void
    {
        $this->proxyFactory = $proxyFactory;
    }
}
