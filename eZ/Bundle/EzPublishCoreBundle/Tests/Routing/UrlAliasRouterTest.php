<?php
/**
 * File containing the UrlAliasRouterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Routing;

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator;
use eZ\Publish\Core\MVC\Symfony\Routing\Tests\UrlAliasRouterTest as BaseUrlAliasRouterTest;
use eZ\Publish\Core\MVC\Symfony\View\Manager as ViewManager;

class UrlAliasRouterTest extends BaseUrlAliasRouterTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    protected function setUp()
    {
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'url_alias_router', null, null, true ),
                        array( 'content.tree_root.location_id', null, null, null ),
                        array( 'content.tree_root.excluded_uri_prefixes', null, null, array() ),
                    )
                )
            );
        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configResolver )
                    )
                )
            );
        return parent::setUp();
    }

    protected function getRouter( \Closure $lazyRepository, UrlAliasGenerator $urlAliasGenerator, RequestContext $requestContext )
    {
        $router = new UrlAliasRouter( $lazyRepository, $urlAliasGenerator, $requestContext );
        $router->setContainer( $this->container );
        return $router;
    }

    /**
     * Resets container and configResolver mocks
     */
    protected function resetContainerAndConfigResolver()
    {
        $this->configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->configResolver )
                    )
                )
            );
        $this->router->setContainer( $this->container );
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     *
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::setContainer
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::getConfigResolver
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::matchRequest
     */
    public function testMatchRequestDeactivatedUrlAlias()
    {
        $this->resetContainerAndConfigResolver();
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'url_alias_router', null, null, false ),
                    )
                )
            );
        $this->router->matchRequest( $this->getRequestByPathInfo( '/foo' ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::setContainer
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::getConfigResolver
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::matchRequest
     */
    public function testMatchRequestWithRootLocation()
    {
        $rootLocationId = 123;
        $this->resetContainerAndConfigResolver();
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'url_alias_router', null, null, true ),
                        array( 'content.tree_root.location_id', null, null, $rootLocationId ),
                        array( 'content.tree_root.excluded_uri_prefixes', null, null, array() ),
                    )
                )
            );

        $prefix = '/root/prefix';
        $this->urlALiasGenerator
            ->expects( $this->once() )
            ->method( 'getPathPrefixByRootLocationId' )
            ->with( $rootLocationId )
            ->will( $this->returnValue( $prefix ) );

        $locationId = 789;
        $path = '/foo/bar';
        $urlAlias = new URLAlias(
            array(
                'destination' => $locationId,
                'path' => $prefix . $path,
                'type' => URLAlias::LOCATION,
                'isHistory' => false
            )
        );
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'lookup' )
            ->with( $prefix . $path )
            ->will( $this->returnValue( $urlAlias ) );

        $expected = array(
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::LOCATION_VIEW_CONTROLLER,
            'locationId' => $locationId,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true
        );
        $request = $this->getRequestByPathInfo( $path );
        $this->assertEquals( $expected, $this->router->matchRequest( $request ) );
        $this->assertSame( $locationId, $request->attributes->get( 'locationId' ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::setContainer
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::getConfigResolver
     * @covers eZ\Bundle\EzPublishCoreBundle\Routing\UrlAliasRouter::matchRequest
     */
    public function testMatchRequestWithRootLocationAndExclusion()
    {
        $this->resetContainerAndConfigResolver();
        $this->configResolver
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'url_alias_router', null, null, true ),
                        array( 'content.tree_root.location_id', null, null, 123 ),
                        array( 'content.tree_root.excluded_uri_prefixes', null, null, array( '/shared/content' ) ),
                    )
                )
            );

        $pathInfo = '/shared/content/foo-bar';
        $destinationId = 789;
        $this->urlALiasGenerator
            ->expects( $this->once() )
            ->method( 'isUriPrefixExcluded' )
            ->with( $pathInfo )
            ->will( $this->returnValue( true ) );

        $urlAlias = new URLAlias(
            array(
                'path' => $pathInfo,
                'type' => UrlAlias::LOCATION,
                'destination' => $destinationId,
                'isHistory' => false
            )
        );
        $request = $this->getRequestByPathInfo( $pathInfo );
        $this->urlAliasService
            ->expects( $this->once() )
            ->method( 'lookup' )
            ->with( $pathInfo )
            ->will( $this->returnValue( $urlAlias ) );

        $expected = array(
            '_route' => UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
            '_controller' => UrlAliasRouter::LOCATION_VIEW_CONTROLLER,
            'locationId' => $destinationId,
            'viewType' => ViewManager::VIEW_TYPE_FULL,
            'layout' => true
        );
        $this->assertEquals( $expected, $this->router->matchRequest( $request ) );
        $this->assertSame( $destinationId, $request->attributes->get( 'locationId' ) );
    }
}
