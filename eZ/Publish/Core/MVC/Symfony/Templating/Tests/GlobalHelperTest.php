<?php
/**
 * File containing the GlobalHelperTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $this->helper = new GlobalHelper( $this->container );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getSiteaccess
     */
    public function testGetSiteaccess()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'ezpublish.siteaccess' )
            ->will( $this->returnValue( true ) );

        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'ezpublish.siteaccess' )
            ->will(
                $this->returnValue( $this->getMock( 'eZ\\Publish\\Core\\MVC\\Symfony\\SiteAccess' ) )
            );

        $this->helper->getSiteaccess();
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getViewParameters
     */
    public function testGetViewParameters()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'request' )
            ->will( $this->returnValue( true ) );

        $request = Request::create( '/foo' );
        $viewParameters = array(
            'foo'              => 'bar',
            'toto'             => 'tata',
            'somethingelse'    => 'héhé-høhø'
        );
        $request->attributes->set( 'viewParameters', $viewParameters );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'request' )
            ->will( $this->returnValue( $request ) );

        $this->assertSame( $viewParameters, $this->helper->getViewParameters() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getViewParametersString
     */
    public function testGetViewParametersString()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'request' )
            ->will( $this->returnValue( true ) );

        $request = Request::create( '/foo' );
        $viewParametersString = '/(foo)/bar/(toto)/tata/(somethingelse)/héhé-høhø';
        $request->attributes->set( 'viewParametersString', $viewParametersString );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'request' )
            ->will( $this->returnValue( $request ) );

        $this->assertSame( $viewParametersString, $this->helper->getViewParametersString() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getRequestedUriString
     */
    public function testGetRequestedUriString()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'request' )
            ->will( $this->returnValue( true ) );

        $request = Request::create( '/ezdemo_site/foo/bar' );
        $semanticPathinfo = '/foo/bar';
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'request' )
            ->will( $this->returnValue( $request ) );

        $this->assertSame( $semanticPathinfo, $this->helper->getRequestedUriString() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getSystemUriString
     */
    public function testGetSystemUriStringNoUrlAlias()
    {
        $this->container
            ->expects( $this->any() )
            ->method( 'has' )
            ->with( 'request' )
            ->will( $this->returnValue( true ) );

        $request = Request::create( '/ezdemo_site/foo/bar' );
        $semanticPathinfo = '/foo/bar';
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( '_route', 'someRouteName' );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->with( 'request' )
            ->will( $this->returnValue( $request ) );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getSystemUriString
     */
    public function testGetSystemUriString()
    {
        $this->container
            ->expects( $this->any() )
            ->method( 'has' )
            ->with( 'request' )
            ->will( $this->returnValue( true ) );

        $locationId = 123;
        $viewType = 'full';
        $expectedSystemUriString = '/content/location/123/full';
        $request = Request::create( '/ezdemo_site/foo/bar' );
        $request->attributes->set( '_route', UrlAliasRouter::URL_ALIAS_ROUTE_NAME );
        $request->attributes->set( 'locationId', $locationId );
        $request->attributes->set( 'viewType', $viewType );

        $routerMock = $this->getMock( 'Symfony\\Component\\Routing\\RouterInterface' );
        $routerMock
            ->expects( $this->once() )
            ->method( 'generate' )
            ->with( '_ezpublishLocation', array( 'locationId' => $locationId, 'viewType' => $viewType ) )
            ->will( $this->returnValue( $expectedSystemUriString ) );

        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'request', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $request ),
                        array( 'router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $routerMock )
                    )
                )
            );

        $this->assertSame( $expectedSystemUriString, $this->helper->getSystemUriString() );
    }

    /**
     * @covers \eZ\Publish\Core\MVC\Symfony\Templating\GlobalHelper::getConfigResolver
     */
    public function testGetConfigResolver()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'ezpublish.config.resolver' )
            ->will( $this->returnValue( true ) );

        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'ezpublish.config.resolver' )
            ->will(
                $this->returnValue( $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' ) )
            );

        $this->helper->getConfigResolver();
    }
}
