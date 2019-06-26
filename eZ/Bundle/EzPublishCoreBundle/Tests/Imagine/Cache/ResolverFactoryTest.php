<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Cache;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\Resolver\RelativeResolver;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\ResolverFactory;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;

class ResolverFactoryTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $resolver;

    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\ResolverFactory */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)->getMock();
        $this->resolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
        $this->factory = new ResolverFactory(
            $this->configResolver,
            $this->resolver,
            ProxyResolver::class,
            RelativeResolver::class
        );
    }

    public function testCreateProxyCacheResolver()
    {
        $this->configResolver
            ->expects($this->at(0))
            ->method('hasParameter')
            ->with('image_host')
            ->willReturn(true);

        $host = 'http://ez.no';

        $this->configResolver
            ->expects($this->at(1))
            ->method('getParameter')
            ->with('image_host')
            ->willReturn($host);

        $expected = new ProxyResolver($this->resolver, [$host]);

        $this->assertEquals($expected, $this->factory->createCacheResolver());
    }

    public function testCreateRelativeCacheResolver()
    {
        $this->configResolver
            ->expects($this->at(0))
            ->method('hasParameter')
            ->with('image_host')
            ->willReturn(true);

        $host = '/';

        $this->configResolver
            ->expects($this->at(1))
            ->method('getParameter')
            ->with('image_host')
            ->willReturn($host);

        $expected = new RelativeResolver($this->resolver);

        $this->assertEquals($expected, $this->factory->createCacheResolver());
    }
}
