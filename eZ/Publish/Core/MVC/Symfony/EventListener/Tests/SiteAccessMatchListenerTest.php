<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\EventListener\Tests;

use eZ\Publish\Core\MVC\Symfony\Component\Serializer\SerializerTrait;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteAccessMatchListenerTest extends TestCase
{
    use SerializerTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $saRouter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $userHashMatcher;

    /** @var SiteAccessMatchListener */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->saRouter = $this->createMock(Router::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->userHashMatcher = $this->createMock(RequestMatcherInterface::class);
        $this->listener = new SiteAccessMatchListener($this->saRouter, $this->eventDispatcher, $this->userHashMatcher);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 45]],
            SiteAccessMatchListener::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestUserHashNoOriginalRequest()
    {
        $request = new Request();
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
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
        $matcher = new SiteAccess\Matcher\URIElement(1);
        $siteAccess = new SiteAccess(
            'test',
            'matching_type',
            $matcher
        );
        $request = new Request();
        $request->attributes->set('serialized_siteaccess', json_encode($siteAccess));
        $request->attributes->set(
            'serialized_siteaccess_matcher',
            $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            )
        );
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
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

    public function testOnKernelRequestSerializedSAWithCompoundMatcher()
    {
        $compoundMatcher = new SiteAccess\Matcher\Compound\LogicalAnd([]);
        $subMatchers = [
            SiteAccess\Matcher\Map\URI::class => new SiteAccess\Matcher\Map\URI([]),
            SiteAccess\Matcher\Map\Host::class => new SiteAccess\Matcher\Map\Host([]),
        ];
        $compoundMatcher->setSubMatchers($subMatchers);
        $siteAccess = new SiteAccess(
            'test',
            'matching_type',
            $compoundMatcher
        );
        $request = new Request();
        $request->attributes->set('serialized_siteaccess', json_encode($siteAccess));
        $request->attributes->set(
            'serialized_siteaccess_matcher',
            $this->getSerializer()->serialize(
                $siteAccess->matcher,
                'json'
            )
        );
        $serializedSubMatchers = [];
        foreach ($subMatchers as $subMatcher) {
            $serializedSubMatchers[get_class($subMatcher)] = $this->getSerializer()->serialize(
                $subMatcher,
                'json'
            );
        }
        $request->attributes->set(
            'serialized_siteaccess_sub_matchers',
            $serializedSubMatchers
        );
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
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
            $this->createMock(HttpKernelInterface::class),
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
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(false));

        $simplifiedRequest = new SimplifiedRequest(
            [
                'scheme' => $request->getScheme(),
                'host' => $request->getHost(),
                'port' => $request->getPort(),
                'pathinfo' => $request->getPathInfo(),
                'queryParams' => $request->query->all(),
                'languages' => $request->getLanguages(),
                'headers' => $request->headers->all(),
            ]
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
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(true));

        $simplifiedRequest = new SimplifiedRequest(
            [
                'scheme' => $originalRequest->getScheme(),
                'host' => $originalRequest->getHost(),
                'port' => $originalRequest->getPort(),
                'pathinfo' => $originalRequest->getPathInfo(),
                'queryParams' => $originalRequest->query->all(),
                'languages' => $originalRequest->getLanguages(),
                'headers' => $originalRequest->headers->all(),
            ]
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
