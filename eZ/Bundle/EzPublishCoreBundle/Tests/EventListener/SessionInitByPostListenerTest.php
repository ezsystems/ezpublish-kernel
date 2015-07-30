<?php

/**
 * File containing the SessionInitByPostListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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
     * @var \eZ\Bundle\EzPublishCoreBundle\EventListener\SessionInitByPostListener
     */
    private $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    protected function setUp()
    {
        parent::setUp();
        $this->session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $this->listener = new SessionInitByPostListener($this->session);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array('onSiteAccessMatch', 249),
            ),
            SessionInitByPostListener::getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchNoSessionService()
    {
        $event = new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST);
        $listener = new SessionInitByPostListener(null);
        $this->assertNull($listener->onSiteAccessMatch($event));
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $event = new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST);
        $this->session
            ->expects($this->never())
            ->method('getName');
        $this->listener->onSiteAccessMatch($event);
    }

    public function testOnSiteAccessMatchRequestNoSessionName()
    {
        $sessionName = 'eZSESSID';
        $event = new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST);

        $this->session
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($sessionName));
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $this->session
            ->expects($this->never())
            ->method('setId');
        $this->session
            ->expects($this->never())
            ->method('start');

        $this->listener->onSiteAccessMatch($event);
    }

    public function testOnSiteAccessMatchNewSessionName()
    {
        $sessionName = 'eZSESSID';
        $sessionId = 'foobar123';
        $request = new Request();
        $request->request->set($sessionName, $sessionId);
        $event = new PostSiteAccessMatchEvent(new SiteAccess(), $request, HttpKernelInterface::MASTER_REQUEST);

        $this->session
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($sessionName));
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $this->session
            ->expects($this->once())
            ->method('setId')
            ->with($sessionId);
        $this->session
            ->expects($this->once())
            ->method('start');

        $this->listener->onSiteAccessMatch($event);
    }
}
