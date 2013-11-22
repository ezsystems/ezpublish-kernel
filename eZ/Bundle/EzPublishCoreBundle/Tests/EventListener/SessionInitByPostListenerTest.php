<?php
/**
 * File containing the SessionInitByPostListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\SessionInitByPostListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionInitByPostListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\EventListener\SessionInitByPostListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $this->listener = new SessionInitByPostListener( $this->container );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 249 )
            ),
            $this->listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchNoSessionService()
    {
        $event = new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST );
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( false ) );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );
        $this->listener->onSiteAccessMatch( $event );
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $event = new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST );
        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );
        $this->listener->onSiteAccessMatch( $event );
    }

    public function testOnSiteAccessMatchRequestNoSessionName()
    {
        $sessionName = 'eZSESSID';
        $event = new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST );

        $session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $session
            ->expects( $this->once() )
            ->method( 'getName' )
            ->will( $this->returnValue( $sessionName ) );
        $session
            ->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( false ) );
        $session
            ->expects( $this->never() )
            ->method( 'setId' );
        $session
            ->expects( $this->never() )
            ->method( 'start' );

        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'session' )
            ->will( $this->returnValue( $session ) );

        $this->listener->onSiteAccessMatch( $event );
    }

    public function testOnSiteAccessMatchNewSessionName()
    {
        $sessionName = 'eZSESSID';
        $sessionId = 'foobar123';
        $request = new Request();
        $request->request->set( $sessionName, $sessionId );
        $event = new PostSiteAccessMatchEvent( new SiteAccess(), $request, HttpKernelInterface::MASTER_REQUEST );

        $session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $session
            ->expects( $this->once() )
            ->method( 'getName' )
            ->will( $this->returnValue( $sessionName ) );
        $session
            ->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( false ) );
        $session
            ->expects( $this->once() )
            ->method( 'setId' )
            ->with( $sessionId );
        $session
            ->expects( $this->once() )
            ->method( 'start' );

        $this->container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'session' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'session' )
            ->will( $this->returnValue( $session ) );

        $this->listener->onSiteAccessMatch( $event );
    }
}
