<?php

/**
 * File containing the RoutingListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RoutingListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RoutingListenerTest extends PHPUnit_Framework_TestCase
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
    }

    public function testGetSubscribedEvents()
    {
        $listener = new RoutingListener($this->configResolver, $this->urlAliasRouter, $this->urlAliasGenerator);
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
                        array('content.tree_root.location_id', null, null, $rootLocationId),
                        array('content.tree_root.excluded_uri_prefixes', null, null, $excludedUriPrefixes),
                    )
                )
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
        $listener = new RoutingListener($this->configResolver, $this->urlAliasRouter, $this->urlAliasGenerator);
        $listener->onSiteAccessMatch($event);
    }
}
