<?php

/**
 * File containing the RestValueResponseListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\EventListener;

use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use eZ\Bundle\EzPublishRestBundle\EventListener\CsrfListener;
use PHPUnit_Framework_TestCase;

abstract class EventListenerTest extends PHPUnit_Framework_TestCase
{
    /** @var EventDispatcherInterface */
    protected $eventMock;

    /** @var Request|PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var ParameterBag|PHPUnit_Framework_MockObject_MockObject */
    protected $requestAttributesMock;

    /** @var ParameterBag|PHPUnit_Framework_MockObject_MockObject */
    protected $requestHeadersMock;

    protected $isRestRequest = true;

    protected $requestType = HttpKernelInterface::MASTER_REQUEST;

    protected $requestMethod = false;

    /**
     * @dataProvider provideExpectedSubscribedEventTypes
     */
    public function testGetSubscribedEvents($expectedEventTypes)
    {
        $eventListener = $this->getEventListener();

        $supportedEvents = $eventListener->getSubscribedEvents();
        $supportedEventTypes = array_keys($supportedEvents);
        sort($supportedEventTypes);
        sort($expectedEventTypes);

        self::assertEquals($expectedEventTypes, $supportedEventTypes);

        // Check that referenced methods exist
        foreach ($supportedEvents as $method) {
            self::assertTrue(
                method_exists($eventListener, is_array($method) ? $method[0] : $method)
            );
        }
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|$class
     */
    protected function getEventMock($class)
    {
        if (!isset($this->eventMock)) {
            $this->eventMock = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->eventMock
                ->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($this->getRequestMock()));
        }

        return $this->eventMock;
    }

    /**
     * @return ParameterBag|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestAttributesMock()
    {
        if (!isset($this->requestAttributesMock)) {
            $this->requestAttributesMock = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
            $this->requestAttributesMock
                ->expects($this->once())
                ->method('get')
                ->with('is_rest_request')
                ->will($this->returnValue($this->isRestRequest));
        }

        return $this->requestAttributesMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Request
     */
    protected function getRequestMock()
    {
        if (!isset($this->requestMock)) {
            $this->requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
            $this->requestMock->attributes = $this->getRequestAttributesMock();
            $this->requestMock->headers = $this->getRequestHeadersMock();

            if ($this->requestMethod === false) {
                $this->requestMock
                    ->expects($this->never())
                    ->method('getMethod');
            } else {
                $this->requestMock
                    ->expects($this->atLeastOnce())
                    ->method('getMethod')
                    ->will($this->returnValue($this->requestMethod));
            }
        }

        return $this->requestMock;
    }

    /**
     * @return ParameterBag|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestHeadersMock()
    {
        if (!isset($this->requestHeadersMock)) {
            $this->requestHeadersMock = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        }

        return $this->requestHeadersMock;
    }

    /**
     * @param bool $csrfEnabled
     *
     * @return CsrfListener
     */
    abstract protected function getEventListener();

    /**
     * Returns an array with the events the listener should be subscribed to.
     */
    abstract public function provideExpectedSubscribedEventTypes();
}
