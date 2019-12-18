<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use eZ\Bundle\EzPublishCoreBundle\EzPublishCoreBundle;
use eZ\Publish\Core\Repository\ProxyFactory\LazyLoadingValueHolderFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EzPublishCoreBundleTest extends TestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\EzPublishCoreBundle */
    private $bundle;

    protected function setUp(): void
    {
        $this->bundle = new EzPublishCoreBundle();
    }

    public function testBoot(): void
    {
        $lazyLoadingValueHolderFactory = $this->createMock(LazyLoadingValueHolderFactory::class);
        $lazyLoadingValueHolderFactory->expects($this->once())->method('registerAutoloader');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with(LazyLoadingValueHolderFactory::class)->willReturn($lazyLoadingValueHolderFactory);

        $this->bundle->setContainer($container);
        $this->bundle->boot();
    }
}
