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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use eZ\Bundle\EzPublishRestBundle\EventListener\CsrfListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfListenerTest extends EventListenerTest
{
    const VALID_TOKEN = 'valid';
    const INVALID_TOKEN = 'invalid';
    const INTENTION = 'rest';

    /** @var EventDispatcherInterface */
    protected $eventDispatcherMock;

    /**
     * If set to null before initializing mocks, Request::getSession() is expected not to be called.
     *
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $sessionMock;

    protected $sessionIsStarted = true;

    protected $csrfTokenHeaderValue = self::VALID_TOKEN;

    /**
     * Route returned by Request::get( '_route' )
     * If set to false, get( '_route' ) is expected not to be called.
     *
     * @var string
     */
    protected $route = 'ezpublish_rest_something';

    /**
     * If set to false, Request::getRequestMethod() is expected not to be called.
     */
    protected $requestMethod = 'POST';

    public function provideExpectedSubscribedEventTypes()
    {
        return array(
            array(array(KernelEvents::REQUEST)),
        );
    }

    public function testIsNotRestRequest()
    {
        $this->isRestRequest = false;

        $this->requestMethod = false;
        $this->sessionMock = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $listener = $this->getEventListener();
        $listener->onKernelRequest($this->getEventMock());
    }

    public function testCsrfDisabled()
    {
        $this->requestMethod = false;
        $this->sessionMock = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener(false)->onKernelRequest($this->getEventMock());
    }

    public function testNoSessionStarted()
    {
        $this->sessionIsStarted = false;

        $this->requestMethod = false;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    /**
     * Tests that method CSRF check don't apply to are indeed ignored.
     *
     * @param string $ignoredMethod
     * @dataProvider getIgnoredRequestMethods
     */
    public function testIgnoredRequestMethods($ignoredMethod)
    {
        $this->requestMethod = $ignoredMethod;
        $this->route = false;
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    public function getIgnoredRequestMethods()
    {
        return array(
            array('GET'),
            array('HEAD'),
            array('OPTIONS'),
        );
    }

    /**
     * Tests that session creation request is properly accepted.
     */
    public function testCreateSessionRequest()
    {
        $this->route = 'ezpublish_rest_createSession';
        $this->csrfTokenHeaderValue = null;

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testNoHeader()
    {
        $this->csrfTokenHeaderValue = false;

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testInvalidToken()
    {
        $this->csrfTokenHeaderValue = self::INVALID_TOKEN;

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    public function testValidToken()
    {
        $this->getEventDispatcherMock()
            ->expects($this->once())
            ->method('dispatch');

        $this->getEventListener()->onKernelRequest($this->getEventMock());
    }

    /**
     * @return CsrfProviderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCsrfProviderMock()
    {
        $provider = $this->getMock('\Symfony\Component\Security\Csrf\CsrfTokenManagerInterface');
        $provider->expects($this->any())
            ->method('isTokenValid')
            ->will(
                $this->returnCallback(
                    function (CsrfToken $token) {
                        if ($token == new CsrfToken(self::INTENTION, self::VALID_TOKEN)) {
                            return true;
                        }

                        return false;
                    }
                )
            );

        return $provider;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|GetResponseEvent
     */
    protected function getEventMock($class = null)
    {
        if (!isset($this->eventMock)) {
            parent::getEventMock('Symfony\Component\HttpKernel\Event\GetResponseEvent');

            $this->eventMock
                ->expects($this->any())
                ->method('getRequestType')
                ->will($this->returnValue($this->requestType));
        }

        return $this->eventMock;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSessionMock()
    {
        if (!isset($this->sessionMock)) {
            $this->sessionMock = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
            $this->sessionMock
                ->expects($this->atLeastOnce())
                ->method('isStarted')
                ->will($this->returnValue($this->sessionIsStarted));
        }

        return $this->sessionMock;
    }

    /**
     * @return ParameterBag|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestHeadersMock()
    {
        if (!isset($this->requestHeadersMock)) {
            $this->requestHeadersMock = parent::getRequestHeadersMock();

            if ($this->csrfTokenHeaderValue === null) {
                $this->requestHeadersMock
                    ->expects($this->never())
                    ->method('has');

                $this->requestHeadersMock
                    ->expects($this->never())
                    ->method('get');
            } else {
                $this->requestHeadersMock
                    ->expects($this->atLeastOnce())
                    ->method('has')
                    ->with(CsrfListener::CSRF_TOKEN_HEADER)
                    ->will($this->returnValue(true));

                $this->requestHeadersMock
                    ->expects($this->atLeastOnce())
                    ->method('get')
                    ->with(CsrfListener::CSRF_TOKEN_HEADER)
                    ->will($this->returnValue($this->csrfTokenHeaderValue));
            }
        }

        return $this->requestHeadersMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Request
     */
    protected function getRequestMock()
    {
        if (!isset($this->requestMock)) {
            $this->requestMock = parent::getRequestMock();

            if ($this->sessionMock === false) {
                $this->requestMock
                    ->expects($this->never())
                    ->method('getSession');
            } else {
                $this->requestMock
                    ->expects($this->atLeastOnce())
                    ->method('getSession')
                    ->will($this->returnValue($this->getSessionMock()));
            }

            if ($this->route === false) {
                $this->requestMock
                    ->expects($this->never())
                    ->method('get');
            } else {
                $this->requestMock
                    ->expects($this->atLeastOnce())
                    ->method('get')
                    ->with('_route')
                    ->will($this->returnValue($this->route));
            }
        }

        return $this->requestMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected function getEventDispatcherMock()
    {
        if (!isset($this->eventDispatcherMock)) {
            $this->eventDispatcherMock = $this->getMock(
                'Symfony\Component\EventDispatcher\EventDispatcherInterface'
            );
        }

        return $this->eventDispatcherMock;
    }

    /**
     * @param bool $csrfEnabled
     *
     * @return CsrfListener
     */
    protected function getEventListener($csrfEnabled = true)
    {
        if ($csrfEnabled) {
            return new CsrfListener(
                $this->getEventDispatcherMock(),
                $csrfEnabled,
                self::INTENTION,
                $this->getCsrfProviderMock()
            );
        }

        return new CsrfListener(
            $this->getEventDispatcherMock(),
            $csrfEnabled,
            self::INTENTION
        );
    }
}
