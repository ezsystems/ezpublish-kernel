<?php

/**
 * File containing the CacheFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\CacheFactory;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

class CacheFactoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * @return array
     */
    public function providerGetService()
    {
        return array(
            array('default', 'default'),
            array('ez_site1', 'ez_site1'),
            array('xyZ', 'xyZ'),
        );
    }

    /**
     * @dataProvider providerGetService
     */
    public function testGetService($name, $expected)
    {
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('cache_service_name')
            ->will($this->returnValue($name));

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($expected)
            ->will($this->returnValue($this->createMock(AdapterInterface::class)));

        $factory = new CacheFactory();
        $factory->setContainer($this->container);

        $this->assertInstanceOf(TagAwareAdapter::class, $factory->getCachePool($this->configResolver));
    }
}
