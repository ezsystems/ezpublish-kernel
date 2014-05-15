<?php
/**
 * File containing the SessionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\LegacyMapper;

use eZ\Bundle\EzPublishLegacyBundle\LegacyMapper\Session as SessionMapper;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    protected function setUp()
    {
        parent::setUp();
        $this->sessionStorage = $this->getMock( 'Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface' );
        $this->session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $this->request = $this
            ->getMockBuilder( 'Symfony\Component\HttpFoundation\Request' )
            ->setMethods( array( 'hasPreviousSession' ) )
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( 'onBuildKernelHandler', 128 )
            ),
            SessionMapper::getSubscribedEvents()
        );
    }

    public function testOnBuildKernelHandlerNoSession()
    {
        $sessionMapper = new SessionMapper( $this->sessionStorage, 'foo' );
        $event = new PreBuildKernelEvent( new ParameterBag(), $this->request );
        $sessionMapper->onBuildKernelHandler( $event );

        $this->assertSame(
            array(
                'session' => array(
                    'configured' => false,
                    'started' => false,
                    'name' => false,
                    'namespace' => false,
                    'has_previous' => false,
                    'storage' => false,
                ),
                'injected-settings' => array(
                    'site.ini/Session/CookieTimeout' => false,
                    'site.ini/Session/CookiePath' => false,
                    'site.ini/Session/CookieDomain' => false,
                    'site.ini/Session/CookieSecure' => false,
                    'site.ini/Session/CookieHttponly' => false,
                )
            ),
            $event->getParameters()->all()
        );
    }

    /**
     * @dataProvider buildKernelProvider
     */
    public function testOnBuildKernelHandler( $sessionName, $isStarted, $storageKey, $hasPreviousSession )
    {
        $this->session
            ->expects( $this->once() )
            ->method( 'getName' )
            ->will( $this->returnValue( $sessionName ) );
        $this->session
            ->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( $isStarted ) );
        $this->request
            ->expects( $this->once() )
            ->method( 'hasPreviousSession' )
            ->will( $this->returnValue( $hasPreviousSession ) );

        $sessionMapper = new SessionMapper( $this->sessionStorage, $storageKey, $this->session );
        $sessionMapper->setRequest( $this->request );
        $event = new PreBuildKernelEvent( new ParameterBag(), $this->request );

        $sessionMapper->onBuildKernelHandler( $event );
        $this->assertSame(
            array(
                'session' => array(
                    'configured' => true,
                    'started' => $isStarted,
                    'name' => $sessionName,
                    'namespace' => $storageKey,
                    'has_previous' => $hasPreviousSession,
                    'storage' => $this->sessionStorage
                ),
                'injected-settings' => array(
                    'site.ini/Session/CookieTimeout' => false,
                    'site.ini/Session/CookiePath' => false,
                    'site.ini/Session/CookieDomain' => false,
                    'site.ini/Session/CookieSecure' => false,
                    'site.ini/Session/CookieHttponly' => false,
                )
            ),
            $event->getParameters()->all()
        );
    }

    public function buildKernelProvider()
    {
        return array(
            array( 'some_session_name', false, '_symfony', true ),
            array( 'my_session', true, '_symfony', false ),
            array( 'my_session', true, 'foobar', true ),
            array( 'eZSESSID', true, '_ezpublish', true ),
        );
    }
}
