<?php

/**
 * File containing the RequestListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\EventListener;

use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use eZ\Bundle\EzPublishRestBundle\EventListener\RequestListener;
use Symfony\Component\HttpKernel\KernelEvents;
use PHPUnit\Framework\MockObject\MockObject;

class RequestListenerTest extends EventListenerTest
{
    const REST_ROUTE = '/api/ezp/v2/rest-route';
    const NON_REST_ROUTE = '/non-rest-route';

    public function provideExpectedSubscribedEventTypes()
    {
        return [
            [
                [KernelEvents::REQUEST],
            ],
        ];
    }

    public static function restRequestUrisProvider()
    {
        return [
            ['/api/ezp/v2/true'],
            ['/api/bundle-name/v2/true'],
            ['/api/MyBundle12/v2/true'],
            ['/api/ThisIs_Bundle123/v2/true'],
            ['/api/my-bundle/v1/true'],
            ['/api/my-bundle/v2/true'],
            ['/api/my-bundle/v2.7/true'],
            ['/api/my-bundle/v122.73/true'],
        ];
    }

    public static function nonRestRequestsUrisProvider()
    {
        return [
            ['/ap/ezp/v2/false'],
            ['/api/bundle name/v2/false'],
            ['/api/My/Bundle/v2/false'],
            ['/api//v2/false'],
            ['/api/my-bundle/v/false'],
            ['/api/my-bundle/v2-2/false'],
            ['/api/my-bundle/v2 7/false'],
            ['/api/my-bundle/v/7/false'],
        ];
    }

    public function testOnKernelRequestNotMasterRequest()
    {
        $request = $this->performFakeRequest(self::REST_ROUTE, HttpKernelInterface::SUB_REQUEST);

        self::assertTrue($request->attributes->get('is_rest_request'));
    }

    public function testOnKernelRequestNotRestRequest()
    {
        $request = $this->performFakeRequest(self::NON_REST_ROUTE);

        self::assertFalse($request->attributes->get('is_rest_request'));
    }

    public function testOnKernelRequestRestRequest()
    {
        $request = $this->performFakeRequest(self::REST_ROUTE);

        self::assertTrue($request->attributes->get('is_rest_request'));
    }

    /**
     * @dataProvider restRequestUrisProvider
     */
    public function testRestRequestVariations($uri)
    {
        $request = $this->performFakeRequest($uri);

        self::assertTrue($request->attributes->get('is_rest_request'));
    }

    /**
     * @dataProvider nonRestRequestsUrisProvider
     */
    public function testNonRestRequestVariations($uri)
    {
        $request = $this->performFakeRequest($uri);

        self::assertFalse($request->attributes->get('is_rest_request'));
    }

    /**
     * @return RequestListener
     */
    protected function getEventListener()
    {
        return new RequestListener(
            $this->getVisitorDispatcherMock()
        );
    }

    /**
     * @return AcceptHeaderVisitorDispatcher|MockObject
     */
    public function getVisitorDispatcherMock()
    {
        return $this->createMock(AcceptHeaderVisitorDispatcher::class);
    }

    /**
     * @return Request
     */
    protected function performFakeRequest($uri, $type = HttpKernelInterface::MASTER_REQUEST)
    {
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create($uri),
            $type
        );

        $this->getEventListener()->onKernelRequest($event);

        return $event->getRequest();
    }
}
