<?php

/**
 * File containing the RoutingListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RoutingListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\RootLocationIdCalculator;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RoutingListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlAliasRouter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlAliasGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rootLocationIdCalculator;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->configResolver = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
        $this->urlAliasRouter = $this
            ->getMockBuilder('eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlAliasGenerator = $this
            ->getMockBuilder('eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->rootLocationIdCalculator = $this
            ->getMockBuilder(RootLocationIdCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $listener = new RoutingListener($this->configResolver, $this->urlAliasRouter, $this->urlAliasGenerator, $this->rootLocationIdCalculator);
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array('onSiteAccessMatch', 200),
            ),
            $listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatch()
    {
        $rootLocationId = 123;
        $excludedUriPrefixes = array('/foo/bar', '/baz');
        $this->configResolver
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnValueMap(
                    array(
                        array('content.tree_root.excluded_uri_prefixes', null, null, $excludedUriPrefixes),
                    )
                )
            );

        $this->rootLocationIdCalculator
            ->expects($this->once())
            ->method('getRootLocationId')
            ->will(
                $this->returnValue($rootLocationId)
            );

        $this->urlAliasRouter
            ->expects($this->once())
            ->method('setRootLocationId')
            ->with($rootLocationId);
        $this->urlAliasGenerator
            ->expects($this->once())
            ->method('setRootLocationId')
            ->with($rootLocationId);
        $this->urlAliasGenerator
            ->expects($this->once())
            ->method('setExcludedUriPrefixes')
            ->with($excludedUriPrefixes);

        $event = new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST);
        $listener = new RoutingListener($this->configResolver, $this->urlAliasRouter, $this->urlAliasGenerator, $this->rootLocationIdCalculator);
        $listener->onSiteAccessMatch($event);
    }
}
