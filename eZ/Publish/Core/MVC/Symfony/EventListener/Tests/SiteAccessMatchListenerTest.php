<?php

/**
 * File containing the SiteAccessMatchListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener\Tests;

use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteAccessMatchListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $saRouter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userHashMatcher;

    /**
     * @var SiteAccessMatchListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->saRouter = $this->getMockBuilder('\eZ\Publish\Core\MVC\Symfony\SiteAccess\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->userHashMatcher = $this->getMock('\Symfony\Component\HttpFoundation\RequestMatcherInterface');
        $this->listener = new SiteAccessMatchListener($this->saRouter, $this->eventDispatcher, $this->userHashMatcher);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(KernelEvents::REQUEST => array('onKernelRequest', 45)),
            SiteAccessMatchListener::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestUserHashNoOriginalRequest()
    {
        $request = new Request();
        $event = new GetResponseEvent(
            $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(true));

        $this->saRouter
            ->expects($this->never())
            ->method('match');
        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->listener->onKernelRequest($event);
        $this->assertFalse($request->attributes->has('siteaccess'));
    }

    public function testOnKernelRequestSerializedSA()
    {
        $siteAccess = new SiteAccess();
        $request = new Request();
        $request->attributes->set('serialized_siteaccess', serialize($siteAccess));
        $event = new GetResponseEvent(
            $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(false));

        $this->saRouter
            ->expects($this->never())
            ->method('match');

        $postSAMatchEvent = new PostSiteAccessMatchEvent($siteAccess, $request, $event->getRequestType());
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::SITEACCESS, $this->equalTo($postSAMatchEvent));

        $this->listener->onKernelRequest($event);
        $this->assertEquals($siteAccess, $request->attributes->get('siteaccess'));
        $this->assertFalse($request->attributes->has('serialized_siteaccess'));
    }

    public function testOnKernelRequestSiteAccessPresent()
    {
        $siteAccess = new SiteAccess();
        $request = new Request();
        $request->attributes->set('siteaccess', $siteAccess);
        $event = new GetResponseEvent(
            $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(false));

        $this->saRouter
            ->expects($this->never())
            ->method('match');

        $postSAMatchEvent = new PostSiteAccessMatchEvent($siteAccess, $request, $event->getRequestType());
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::SITEACCESS, $this->equalTo($postSAMatchEvent));

        $this->listener->onKernelRequest($event);
        $this->assertSame($siteAccess, $request->attributes->get('siteaccess'));
    }

    public function testOnKernelRequest()
    {
        $siteAccess = new SiteAccess();
        $scheme = 'https';
        $host = 'phoenix-rises.fm';
        $port = 1234;
        $path = '/foo/bar';
        $request = Request::create(sprintf('%s://%s:%d%s', $scheme, $host, $port, $path));
        $event = new GetResponseEvent(
            $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(false));

        $simplifiedRequest = new SimplifiedRequest(
            array(
                'scheme' => $request->getScheme(),
                'host' => $request->getHost(),
                'port' => $request->getPort(),
                'pathinfo' => $request->getPathInfo(),
                'queryParams' => $request->query->all(),
                'languages' => $request->getLanguages(),
                'headers' => $request->headers->all(),
            )
        );
        $this->saRouter
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($simplifiedRequest))
            ->will($this->returnValue($siteAccess));

        $postSAMatchEvent = new PostSiteAccessMatchEvent($siteAccess, $request, $event->getRequestType());
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::SITEACCESS, $this->equalTo($postSAMatchEvent));

        $this->listener->onKernelRequest($event);
        $this->assertSame($siteAccess, $request->attributes->get('siteaccess'));
    }

    public function testOnKernelRequestUserHashWithOriginalRequest()
    {
        $siteAccess = new SiteAccess();
        $scheme = 'https';
        $host = 'phoenix-rises.fm';
        $port = 1234;
        $path = '/foo/bar';
        $originalRequest = Request::create(sprintf('%s://%s:%d%s', $scheme, $host, $port, $path));
        $request = Request::create('http://localhost/_fos_user_hash');
        $request->attributes->set('_ez_original_request', $originalRequest);
        $event = new GetResponseEvent(
            $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface'),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(true));

        $simplifiedRequest = new SimplifiedRequest(
            array(
                'scheme' => $originalRequest->getScheme(),
                'host' => $originalRequest->getHost(),
                'port' => $originalRequest->getPort(),
                'pathinfo' => $originalRequest->getPathInfo(),
                'queryParams' => $originalRequest->query->all(),
                'languages' => $originalRequest->getLanguages(),
                'headers' => $originalRequest->headers->all(),
            )
        );
        $this->saRouter
            ->expects($this->once())
            ->method('match')
            ->with($this->equalTo($simplifiedRequest))
            ->will($this->returnValue($siteAccess));

        $postSAMatchEvent = new PostSiteAccessMatchEvent($siteAccess, $request, $event->getRequestType());
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(MVCEvents::SITEACCESS, $this->equalTo($postSAMatchEvent));

        $this->listener->onKernelRequest($event);
        $this->assertSame($siteAccess, $request->attributes->get('siteaccess'));
    }
}
