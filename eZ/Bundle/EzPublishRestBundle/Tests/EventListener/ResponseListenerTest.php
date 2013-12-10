<?php
/**
 * File containing the RestValueResponseListener class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\EventListener;

use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use eZ\Bundle\EzPublishRestBundle\EventListener\ResponseListener;
use stdClass;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListenerTest extends EventListenerTest
{
    /** @var AcceptHeaderVisitorDispatcher|PHPUnit_Framework_MockObject_MockObject */
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

        $this->dispatcherMessage = new stdClass();

        $this->dispatcherMessage->body = 'BODY';
        $this->dispatcherMessage->headers = 'HEADERS';
        $this->dispatcherMessage->statusCode = 406;

        $this->response = new Response(
            $this->dispatcherMessage->body,
            $this->dispatcherMessage->statusCode,
            $this->dispatcherMessage->headers = array( 'foo' => 'bar' )
        );
    }

    public function provideExpectedSubscribedEventTypes()
    {
        return array(
            array( array( KernelEvents::VIEW, KernelEvents::EXCEPTION ) )
        );
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

    protected function onKernelViewIsNotRestRequest( $method, GetResponseEvent $event )
    {
        $this->getVisitorDispatcherMock()
            ->expects( $this->never() )
            ->method( 'dispatch' );

        $this->getEventListener()->$method( $event );
    }

    public function testOnKernelExceptionView()
    {
        $this->onKernelView( 'onKernelExceptionView', $this->getExceptionEventMock() );
    }

    public function testOnControllerResultView()
    {
        $this->onKernelView( 'onKernelResultView', $this->getControllerResultEventMock() );
    }

    protected function onKernelView( $method, $event )
    {
        $this->getVisitorDispatcherMock()
            ->expects( $this->once() )
            ->method( 'dispatch' )
            ->with(
                $this->getRequestMock(),
                $this->eventValue
            )->will(
                $this->returnValue(
                    $this->dispatcherMessage
                )
            );

        $event->expects( $this->once() )
            ->method( 'setResponse' )
            ->with( $this->response );

        $this->getEventListener()->$method( $event );
    }

    /**
     * @return AcceptHeaderVisitorDispatcher|PHPUnit_Framework_MockObject_MockObject
     */
    public function getVisitorDispatcherMock()
    {
        if ( !isset( $this->visitorDispatcherMock ) )
        {
            $this->visitorDispatcherMock = $this->getMock(
                'eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher'
            );
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
     * @return PHPUnit_Framework_MockObject_MockObject|GetResponseForControllerResultEvent
     */
    protected function getControllerResultEventMock()
    {
        if ( !isset( $this->eventMock ) )
        {
            $this->eventMock = parent::getEventMock( 'Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent' );
            $this->eventMock
                ->expects( $this->any() )
                ->method( 'getControllerResult' )
                ->will( $this->returnValue( $this->eventValue ) );
        }
        return $this->eventMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|GetResponseForExceptionEvent
     */
    protected function getExceptionEventMock()
    {
        if ( !isset( $this->eventMock ) )
        {
            $this->eventMock = parent::getEventMock( 'Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent' );

            $this->eventMock
                ->expects( $this->any() )
                ->method( 'getException' )
                ->will( $this->returnValue( $this->eventValue ) );
        }
        return $this->eventMock;
    }
}
