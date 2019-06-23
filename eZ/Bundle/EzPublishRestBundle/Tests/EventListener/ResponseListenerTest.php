<?php

/**
 * File containing the RestValueResponseListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\EventListener;

use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use eZ\Bundle\EzPublishRestBundle\EventListener\ResponseListener;
use stdClass;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListenerTest extends EventListenerTest
{
    /** @var AcceptHeaderVisitorDispatcher|MockObject */
    protected $visitorDispatcherMock;

    protected $eventValue;

    protected $dispatcherMessage;

    protected $controllerResult;

    /** @var Response */
    protected $response;

    protected $eventMock;

    public function setUp()
    {
        $this->eventValue = new stdClass();
        $this->response = new Response('BODY', 406, ['foo' => 'bar']);
    }

    public function provideExpectedSubscribedEventTypes()
    {
        return [
            [[KernelEvents::VIEW, KernelEvents::EXCEPTION]],
        ];
    }

    public function testOnKernelResultViewIsNotRestRequest()
    {
        $this->isRestRequest = false;

        $this->onKernelViewIsNotRestRequest(
            'onKernelResultView',
            $this->getControllerResultEventMock()
        );
    }

    public function testOnKernelExceptionViewIsNotRestRequest()
    {
        $this->isRestRequest = false;

        $this->onKernelViewIsNotRestRequest(
            'onKernelExceptionView',
            $this->getExceptionEventMock()
        );
    }

    protected function onKernelViewIsNotRestRequest($method, GetResponseEvent $event)
    {
        $this->getVisitorDispatcherMock()
            ->expects($this->never())
            ->method('dispatch');

        $this->getEventListener()->$method($event);
    }

    public function testOnKernelExceptionView()
    {
        $this->onKernelView('onKernelExceptionView', $this->getExceptionEventMock());
    }

    public function testOnControllerResultView()
    {
        $this->onKernelView('onKernelResultView', $this->getControllerResultEventMock());
    }

    protected function onKernelView($method, $event)
    {
        $this->getVisitorDispatcherMock()
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->getRequestMock(),
                $this->eventValue
            )->will(
                $this->returnValue(
                    $this->response
                )
            );

        $event->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->getEventListener()->$method($event);
    }

    /**
     * @return AcceptHeaderVisitorDispatcher|MockObject
     */
    public function getVisitorDispatcherMock()
    {
        if (!isset($this->visitorDispatcherMock)) {
            $this->visitorDispatcherMock = $this->createMock(AcceptHeaderVisitorDispatcher::class);
        }

        return $this->visitorDispatcherMock;
    }

    /**
     * @return ResponseListener
     */
    protected function getEventListener()
    {
        return new ResponseListener(
            $this->getVisitorDispatcherMock()
        );
    }

    /**
     * @return MockObject|GetResponseForControllerResultEvent
     */
    protected function getControllerResultEventMock()
    {
        if (!isset($this->eventMock)) {
            $this->eventMock = parent::getEventMock(GetResponseForControllerResultEvent::class);
            $this->eventMock
                ->expects($this->any())
                ->method('getControllerResult')
                ->will($this->returnValue($this->eventValue));
        }

        return $this->eventMock;
    }

    /**
     * @return MockObject|GetResponseForExceptionEvent
     */
    protected function getExceptionEventMock()
    {
        if (!isset($this->eventMock)) {
            $this->eventMock = parent::getEventMock(GetResponseForExceptionEvent::class);

            $this->eventMock
                ->expects($this->any())
                ->method('getException')
                ->will($this->returnValue($this->eventValue));
        }

        return $this->eventMock;
    }
}
