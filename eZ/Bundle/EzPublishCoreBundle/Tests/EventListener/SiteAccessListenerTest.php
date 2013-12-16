<?php
/**
 * File containing the SiteAccessListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\SiteAccessListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SiteAccessListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generator;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\EventListener\SiteAccessListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $this->router = $this
            ->getMockBuilder( 'eZ\Bundle\EzPublishCoreBundle\Routing\DefaultRouter' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->generator = $this
            ->getMockBuilder( 'eZ\Publish\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->listener = new SiteAccessListener( $this->container, $this->router, $this->generator );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 255 )
            ),
            $this->listener->getSubscribedEvents()
        );
    }

    public function siteAccessMatchProvider()
    {
        return array(
            array( '/foo/bar', '/foo/bar', '', array() ),
            array( '/my_siteaccess/foo/bar', '/foo/bar', '', array() ),
            array( '/foo/bar/(some)/thing', '/foo/bar', '/(some)/thing', array( 'some' => 'thing' ) ),
            array( '/foo/bar/(some)/thing/(other)', '/foo/bar', '/(some)/thing/(other)', array( 'some' => 'thing', 'other' => '' ) ),
            array( '/foo/bar/(some)/thing/orphan', '/foo/bar', '/(some)/thing/orphan', array( 'some' => 'thing/orphan' ) ),
            array( '/foo/bar/(some)/thing/orphan/(something)/else', '/foo/bar', '/(some)/thing/orphan/(something)/else', array( 'some' => 'thing/orphan', 'something' => 'else' ) ),
            array( '/foo/bar/(some)/thing/orphan/(something)/else/(other)', '/foo/bar', '/(some)/thing/orphan/(something)/else/(other)', array( 'some' => 'thing/orphan', 'something' => 'else', 'other' => '' ) ),
            array( '/foo/bar/(some)/thing/orphan/(other)', '/foo/bar', '/(some)/thing/orphan/(other)', array( 'some' => 'thing/orphan', 'other' => '' ) ),
            array( '/my_siteaccess/foo/bar/(some)/thing', '/foo/bar', '/(some)/thing', array( 'some' => 'thing' ) ),
            array( '/foo/bar/(some)/thing/(toto_titi)/tata_tutu', '/foo/bar', '/(some)/thing/(toto_titi)/tata_tutu', array( 'some' => 'thing', 'toto_titi' => 'tata_tutu' ) ),
        );
    }

    /**
     * @dataProvider siteAccessMatchProvider
     */
    public function testOnSiteAccessMatchMasterRequest(
        $uri,
        $expectedSemanticPathinfo,
        $expectedVPString,
        array $expectedVPArray
    )
    {
        $semanticPathinfoPos = strpos( $uri, $expectedSemanticPathinfo );
        if ( $semanticPathinfoPos !== 0 )
        {
            $semanticPathinfo = substr( $uri, $semanticPathinfoPos );
            $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
            $matcher
                ->expects( $this->once() )
                ->method( 'analyseURI' )
                ->with( $uri )
                ->will( $this->returnValue( $semanticPathinfo ) );
        }
        else
        {
            $matcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' );
        }

        $siteAccess = new SiteAccess( 'test', 'test', $matcher );
        $request = Request::create( $uri );
        $event = new PostSiteAccessMatchEvent( $siteAccess, $request, HttpKernelInterface::MASTER_REQUEST );

        $this->container
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( 'ezpublish.siteaccess', $siteAccess );
        $this->generator
            ->expects( $this->once() )
            ->method( 'setSiteAccess' )
            ->with( $siteAccess );
        $this->router
            ->expects( $this->once() )
            ->method( 'setSiteAccess' )
            ->with( $siteAccess );

        $this->listener->onSiteAccessMatch( $event );
        $this->assertSame( $expectedSemanticPathinfo, $request->attributes->get( 'semanticPathinfo' ) );
        $this->assertSame( $expectedVPArray, $request->attributes->get( 'viewParameters' ) );
        $this->assertSame( $expectedVPString, $request->attributes->get( 'viewParametersString' ) );
    }

    /**
     * @dataProvider siteAccessMatchProvider
     */
    public function testOnSiteAccessMatchSubRequest( $uri, $semanticPathinfo, $vpString, $expectedViewParameters )
    {
        $siteAccess = new SiteAccess( 'test', 'test', $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher' ) );
        $request = Request::create( $uri );
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        if ( !empty( $vpString ) )
        {
            $request->attributes->set( 'viewParametersString', $vpString );
        }
        $event = new PostSiteAccessMatchEvent( $siteAccess, $request, HttpKernelInterface::SUB_REQUEST );

        $this->container
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( 'ezpublish.siteaccess', $siteAccess );
        $this->generator
            ->expects( $this->once() )
            ->method( 'setSiteAccess' )
            ->with( $siteAccess );
        $this->router
            ->expects( $this->once() )
            ->method( 'setSiteAccess' )
            ->with( $siteAccess );

        $this->listener->onSiteAccessMatch( $event );
        $this->assertSame( $semanticPathinfo, $request->attributes->get( 'semanticPathinfo' ) );
        $this->assertSame( $expectedViewParameters, $request->attributes->get( 'viewParameters' ) );
        $this->assertSame( $vpString, $request->attributes->get( 'viewParametersString' ) );
    }
}
