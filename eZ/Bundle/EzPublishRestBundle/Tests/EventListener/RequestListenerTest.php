<?php
/**
 * File containing the RequestListenerTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\EventListener;

use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use eZ\Bundle\EzPublishRestBundle\EventListener\RequestListener;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListenerTest extends EventListenerTest
{
    const REST_PREFIX = '/rest/prefix';

    public function provideExpectedSubscribedEventTypes()
    {
        return array(
            array( array( KernelEvents::REQUEST ) )
        );
    }

    public function testOnKernelRequestNotMasterRequest()
    {
        $event = $this->getEvent( self::REST_PREFIX . '/', HttpKernelInterface::SUB_REQUEST );
        $this->getEventListener()->onKernelRequest( $event );

        self::assertFalse(
            $event->getRequest()->attributes->get( 'is_rest_request' )
        );
    }

    public function testOnKernelRequestNotRestRequest()
    {
        $event = $this->getEvent( '/' );
        $this->getEventListener()->onKernelRequest( $event );

        self::assertFalse(
            $event->getRequest()->attributes->get( 'is_rest_request' )
        );
    }

    public function testOnKernelRequestRestRequest()
    {
        $event = $this->getEvent( self::REST_PREFIX . '/' );
        $this->getEventListener()->onKernelRequest( $event );

        self::assertTrue(
            $event->getRequest()->attributes->get( 'is_rest_request' )
        );
    }

    /**
     * @return RequestListener
     */
    protected function getEventListener()
    {
        return new RequestListener(
            self::REST_PREFIX,
            $this->getVisitorDispatcherMock()
        );
    }

    /**
     * @return AcceptHeaderVisitorDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getVisitorDispatcherMock()
    {
        return $this->getMock(
            'eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher'
        );
    }

    /**
     * @return GetResponseEvent
     */
    public function getEvent( $uri, $type = HttpKernelInterface::MASTER_REQUEST )
    {
        return new GetResponseEvent(
            $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
            Request::create( $uri ),
            $type
        );
    }
}
