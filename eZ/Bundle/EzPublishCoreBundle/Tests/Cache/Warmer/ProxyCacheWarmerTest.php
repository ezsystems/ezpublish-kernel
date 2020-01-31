<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Cache\Warmer;

use eZ\Bundle\EzPublishCoreBundle\Cache\Warmer\ProxyCacheWarmer;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class ProxyCacheWarmerTest extends TestCase
{
    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $proxyGenerator;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Cache\Warmer\ProxyCacheWarmer */
    private $proxyCacheWarmer;

    protected function setUp(): void
    {
        $this->proxyGenerator = $this->createMock(ProxyGeneratorInterface::class);
        $this->proxyCacheWarmer = new ProxyCacheWarmer($this->proxyGenerator);
    }

    public function testIsOptional(): void
    {
        $this->assertFalse($this->proxyCacheWarmer->isOptional());
    }

    public function testWarmUp(): void
    {
        $this->proxyGenerator
            ->expects($this->once())
            ->method('warmUp')
            ->with(ProxyCacheWarmer::PROXY_CLASSES);

        $this->proxyCacheWarmer->warmUp('/cache/dir');
    }
}
