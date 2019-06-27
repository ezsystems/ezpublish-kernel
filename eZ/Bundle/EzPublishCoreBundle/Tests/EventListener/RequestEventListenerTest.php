<?php

/**
 * File containing the RequestEventListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class RequestEventListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface */
    private $logger;

    /** @var RequestEventListener */
    private $requestEventListener;

    /** @var Request */
    private $request;

    /** @var GetResponseEvent */
    private $event;

    /** @var HttpKernelInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $httpKernel;

    protected function setUp()
    {
        parent::setUp();

        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->requestEventListener = new RequestEventListener($this->configResolver, $this->router, 'foobar', $this->logger);

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->setMethods(['getSession', 'hasSession'])
            ->getMock();

        $this->httpKernel = $this->createMock(HttpKernelInterface::class);
        $this->event = new GetResponseEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            [
                KernelEvents::REQUEST => [
                    ['onKernelRequestForward', 10],
                    ['onKernelRequestRedirect', 0],
                ],
            ],
            $this->requestEventListener->getSubscribedEvents()
        );
    }

    public function testOnKernelRequestForwardSubRequest()
    {
        $this->httpKernel
            ->expects($this->never())
            ->method('handle');

        $event = new GetResponseEvent($this->httpKernel, new Request(), HttpKernelInterface::SUB_REQUEST);
        $this->requestEventListener->onKernelRequestForward($event);
    }

    public function testOnKernelRequestForward()
    {
        $queryParameters = ['some' => 'thing'];
        $cookieParameters = ['cookie' => 'value'];
        $request = Request::create('/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters);
        $semanticPathinfo = '/foo/something';
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('needsForward', true);
        $request->attributes->set('someAttribute', 'someValue');

        $expectedForwardRequest = Request::create($semanticPathinfo, 'GET', $queryParameters, $cookieParameters);
        $expectedForwardRequest->attributes->set('semanticPathinfo', $semanticPathinfo);
        $expectedForwardRequest->attributes->set('someAttribute', 'someValue');

        $response = new Response('Success!');
        $this->httpKernel
            ->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($expectedForwardRequest))
            ->will($this->returnValue($response));

        $event = new GetResponseEvent($this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->requestEventListener->onKernelRequestForward($event);
        $this->assertSame($response, $event->getResponse());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testOnKernelRequestRedirectSubRequest()
    {
        $event = new GetResponseEvent($this->httpKernel, new Request(), HttpKernelInterface::SUB_REQUEST);
        $this->requestEventListener->onKernelRequestRedirect($event);
        $this->assertFalse($event->hasResponse());
    }

    public function testOnKernelRequestRedirect()
    {
        $queryParameters = ['some' => 'thing'];
        $cookieParameters = ['cookie' => 'value'];
        $request = Request::create('/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters);
        $semanticPathinfo = '/foo/something';
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('needsRedirect', true);
        $request->attributes->set('siteaccess', new SiteAccess());

        $event = new GetResponseEvent($this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->requestEventListener->onKernelRequestRedirect($event);
        $this->assertTrue($event->hasResponse());
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame("$semanticPathinfo?some=thing", $response->getTargetUrl());
        $this->assertSame(301, $response->getStatusCode());
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testOnKernelRequestRedirectWithLocationId()
    {
        $queryParameters = ['some' => 'thing'];
        $cookieParameters = ['cookie' => 'value'];
        $request = Request::create('/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters);
        $semanticPathinfo = '/foo/something';
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('needsRedirect', true);
        $request->attributes->set('locationId', 123);
        $request->attributes->set('siteaccess', new SiteAccess());

        $event = new GetResponseEvent($this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->requestEventListener->onKernelRequestRedirect($event);
        $this->assertTrue($event->hasResponse());
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame("$semanticPathinfo?some=thing", $response->getTargetUrl());
        $this->assertSame(301, $response->getStatusCode());
        $this->assertEquals(123, $response->headers->get('X-Location-Id'));
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testOnKernelRequestRedirectPrependSiteaccess()
    {
        $queryParameters = ['some' => 'thing'];
        $cookieParameters = ['cookie' => 'value'];
        $siteaccessMatcher = $this->createMock(SiteAccess\URILexer::class);
        $siteaccess = new SiteAccess('test', 'foo', $siteaccessMatcher);
        $semanticPathinfo = '/foo/something';

        $request = Request::create('/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters);
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);
        $request->attributes->set('needsRedirect', true);
        $request->attributes->set('siteaccess', $siteaccess);
        $request->attributes->set('prependSiteaccessOnRedirect', true);

        $expectedURI = "/test$semanticPathinfo";
        $siteaccessMatcher
            ->expects($this->once())
            ->method('analyseLink')
            ->with($semanticPathinfo)
            ->will($this->returnValue($expectedURI));

        $event = new GetResponseEvent($this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST);
        $this->requestEventListener->onKernelRequestRedirect($event);
        $this->assertTrue($event->hasResponse());
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame("$expectedURI?some=thing", $response->getTargetUrl());
        $this->assertSame(301, $response->getStatusCode());
        $this->assertTrue($event->isPropagationStopped());
    }
}
