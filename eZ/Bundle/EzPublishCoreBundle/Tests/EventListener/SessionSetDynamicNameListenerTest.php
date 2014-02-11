<?php
/**
 * File containing the SessionSetDynamicNameListenerTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionStorage;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $this->sessionStorage = $this->getMock( 'Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $listener = new SessionSetDynamicNameListener( $this->configResolver, $this->session, $this->sessionStorage );
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 250 )
            ),
            $listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchNoSession()
    {
        $listener = new SessionSetDynamicNameListener( $this->configResolver, null );
        $listener->onSiteAccessMatch( new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST ) );
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $listener = new SessionSetDynamicNameListener( $this->configResolver, $this->session );
        $listener->onSiteAccessMatch( new PostSiteAccessMatchEvent( new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST ) );
    }

    /**
     * @dataProvider onSiteAccessMatchProvider
     */
    public function testOnSiteAccessMatch( SiteAccess $siteAccess, $configuredSessionName, $expectedSessionName )
    {
        $this->session
            ->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( false ) );
        $this->session
            ->expects( $this->once() )
            ->method( 'setName' )
            ->with( $expectedSessionName );
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'session_name' )
            ->will( $this->returnValue( $configuredSessionName ) );

        $listener = new SessionSetDynamicNameListener( $this->configResolver, $this->session, $this->sessionStorage );
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
