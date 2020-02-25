<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\ProxyFactory;

use eZ\Publish\API\Repository\Repository;

/**
 * @internal
 */
final class ProxyDomainMapperFactory implements ProxyDomainMapperFactoryInterface
{
    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyGeneratorInterface */
    private $proxyGenerator;

    public function __construct(ProxyGeneratorInterface $proxyGenerator)
    {
        $this->proxyGenerator = $proxyGenerator;
    }

    public function create(Repository $repository): ProxyDomainMapperInterface
    {
        return new ProxyDomainMapper($repository, $this->proxyGenerator);
    }
}
