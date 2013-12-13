<?php
/**
 * File containing the SessionSetDynamicNameListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\SessionSetDynamicNameListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionSetDynamicNameListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $listener = new SessionSetDynamicNameListener( $this->container );
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 250 )
            ),
            $listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchNoSession()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( false ) );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );

        $listener = new SessionSetDynamicNameListener( $this->container );
        $listener->onSiteAccessMatch( new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST ) );
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );

        $listener = new SessionSetDynamicNameListener( $this->container );
        $listener->onSiteAccessMatch( new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST ) );
    }

    /**
     * @dataProvider onSiteAccessMatchProvider
     */
    public function testOnSiteAccessMatch( SiteAccess $siteAccess, $configuredSessionName, $expectedSessionName )
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( true ) );

        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $configResolver ),
                        array( 'session', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $session ),
                    )
                )
            );

        $session
            ->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( false ) );
        $session
            ->expects( $this->once() )
            ->method( 'setName' )
            ->with( $expectedSessionName );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'session_name' )
            ->will( $this->returnValue( $configuredSessionName ) );

        $listener = new SessionSetDynamicNameListener( $this->container );
        $listener->onSiteAccessMatch( new PostSiteAccessMatchEvent( $siteAccess, new Request(), HttpKernelInterface::MASTER_REQUEST ) );
    }

    public function onSiteAccessMatchProvider()
    {
        return array(
            array( new SiteAccess( 'foo' ), 'eZSESSID', 'eZSESSID' ),
            array( new SiteAccess( 'foo' ), 'eZSESSID{siteaccess_hash}', 'eZSESSID' . md5( 'foo' ) ),
            array( new SiteAccess( 'foo' ), 'this_is_a_session_name', 'eZSESSID_this_is_a_session_name' ),
            array( new SiteAccess( 'foo' ), 'something{siteaccess_hash}', 'eZSESSID_something' . md5( 'foo' ) ),
            array( new SiteAccess( 'bar_baz' ), '{siteaccess_hash}something', 'eZSESSID_' . md5( 'bar_baz' ) . 'something' ),
        );
    }
}
