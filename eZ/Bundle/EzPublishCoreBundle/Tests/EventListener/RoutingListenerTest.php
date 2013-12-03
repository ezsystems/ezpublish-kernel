<?php
/**
 * File containing the RoutingListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RoutingListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RoutingListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $listener = new RoutingListener( $this->container );
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 200 )
            ),
            $listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatch()
    {
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $urlAliasRouter = $this
            ->getMockBuilder( 'eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter' )
            ->disableOriginalConstructor()
            ->getMock();
        $urlAliasGenerator = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $configResolver ),
                        array( 'ezpublish.urlalias_router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $urlAliasRouter ),
                        array( 'ezpublish.urlalias_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $urlAliasGenerator ),
                    )
                )
            );

        $rootLocationId = 123;
        $excludedUriPrefixes = array( '/foo/bar', '/baz' );
        $configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'content.tree_root.location_id', null, null, $rootLocationId ),
                        array( 'content.tree_root.excluded_uri_prefixes', null, null, $excludedUriPrefixes ),
                    )
                )
            );

        $urlAliasRouter
            ->expects( $this->once() )
            ->method( 'setRootLocationId' )
            ->with( $rootLocationId );
        $urlAliasGenerator
            ->expects( $this->once() )
            ->method( 'setRootLocationId' )
            ->with( $rootLocationId );
        $urlAliasGenerator
            ->expects( $this->once() )
            ->method( 'setExcludedUriPrefixes' )
            ->with( $excludedUriPrefixes );

        $event = new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST );
        $listener = new RoutingListener( $this->container );
        $listener->onSiteAccessMatch( $event );
    }
}
