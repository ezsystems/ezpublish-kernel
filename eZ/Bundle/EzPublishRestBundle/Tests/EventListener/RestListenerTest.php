<?php
/**
 * File containing the RestValueResponseListener class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Test\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;
use eZ\Publish\Core\REST\Server\Request as RESTRequest;
use eZ\Bundle\EzPublishRestBundle\EventListener\RestListener;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use \PHPUnit_Framework_TestCase;

class RestListenerTest extends PHPUnit_Framework_TestCase
{
    const REST_PREFIX = '/rest/prefix';
    const VALID_TOKEN = 'valid';
    const INVALID_TOKEN = 'invalid';
    const INTENTION = 'rest';

    /**
     * @dataProvider onKernelExceptionViewProvider
     */
    public function testOnKernelExceptionView(
        $name,
        ContainerInterface $container,
        RESTRequest $request,
        CsrfProviderInterface $csrfProvider,
        GetResponseForExceptionEvent $event,
        $visit
    )
    {
        $listener = $this->getMock(
            'eZ\\Bundle\\EzPublishRestBundle\\EventListener\\RestListener',
            array( 'visitResult' ), array( $container, $request, $csrfProvider )
        );
        $listener->expects( $this->exactly( $visit ) )
            ->method( 'visitResult' )
            ->will( $this->returnValue( new Response() ) );
        $listener->onKernelExceptionView( $event );
    }

    public function onKernelExceptionViewProvider()
    {
        $request = $this->getRequestMock();
        $csrfProvider = $this->getCsrfProviderMock();
        $tests = array();
        $tests[] = array(
            'sub request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseForExceptionEventMock(
                HttpKernelInterface::SUB_REQUEST
            ),
            0
        );
        $tests[] = array(
            'master request but not a REST request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseForExceptionEventMock(
                HttpKernelInterface::MASTER_REQUEST
            ),
            0
        );

        $event = $this->getGetResponseForExceptionEventMock(
            HttpKernelInterface::MASTER_REQUEST,
            'http://example.com' . self::REST_PREFIX . '/'
        );
        // checking that the exception is visited and that
        // we stop the propagation of the event
        $event->expects( $this->once() )
            ->method( 'getException' );
        $event->expects( $this->once() )
            ->method( 'setResponse' );
        $event->expects( $this->once() )
            ->method( 'stopPropagation' );

        $tests[] = array(
            'master request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $event,
            1
        );

        return $tests;
    }

    protected function getGetResponseForExceptionEventMock(
        $requestType = HttpKernelInterface::MASTER_REQUEST,
        $uri = 'http://example.com/not/a/rest/request'
    )
    {
        $event = $this->getMockBuilder( 'Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'getRequest', 'getRequestType' ) )
            ->getMock();

        $event->expects( $this->once() )
            ->method( 'getRequestType' )
            ->will( $this->returnValue( $requestType ) );

        $requestType === HttpKernelInterface::MASTER_REQUEST ? $calls = 1 : $calls = 0;
        $request = Request::create( $uri );
        $event->expects( $this->exactly( $calls ) )
            ->method( 'getRequest' )
            ->will( $this->returnValue( $request ) );

        return $event;
    }


    /**
     * @dataProvider onKernelResultViewProvider
     */
    public function testOnKernelResultView(
        $name,
        ContainerInterface $container,
        RESTRequest $request,
        CsrfProviderInterface $csrfProvider,
        GetResponseForControllerResultEvent $event,
        $visit
    )
    {
        $listener = $this->getMock(
            'eZ\\Bundle\\EzPublishRestBundle\\EventListener\\RestListener',
            array( 'visitResult' ), array( $container, $request, $csrfProvider )
        );
        $listener->expects( $this->exactly( $visit ) )
            ->method( 'visitResult' )
            ->will( $this->returnValue( new Response() ) );
        $listener->onKernelResultView( $event );
    }

    public function onKernelResultViewProvider()
    {
        $request = $this->getRequestMock();
        $csrfProvider = $this->getCsrfProviderMock();
        $tests = array();
        $tests[] = array(
            'sub request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseForControllerResultEventMock(
                HttpKernelInterface::SUB_REQUEST
            ),
            0
        );
        $tests[] = array(
            'master request but not a REST request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseForControllerResultEventMock(
                HttpKernelInterface::MASTER_REQUEST
            ),
            0
        );

        $event = $this->getGetResponseForControllerResultEventMock(
            HttpKernelInterface::MASTER_REQUEST,
            'http://example.com' . self::REST_PREFIX . '/'
        );
        // checking that the results of the controller
        // is visited and that we stop the propagation of the event
        $event->expects( $this->once() )
            ->method( 'getControllerResult' );
        $event->expects( $this->once() )
            ->method( 'setResponse' );
        $event->expects( $this->once() )
            ->method( 'stopPropagation' );

        $tests[] = array(
            'master request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $event,
            1
        );

        return $tests;
    }

    protected function getGetResponseForControllerResultEventMock(
        $requestType = HttpKernelInterface::MASTER_REQUEST,
        $uri = 'http://example.com/not/a/rest/request'
    )
    {
        $event = $this->getMockBuilder( 'Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'getRequest', 'getRequestType' ) )
            ->getMock();

        $event->expects( $this->once() )
            ->method( 'getRequestType' )
            ->will( $this->returnValue( $requestType ) );

        $requestType === HttpKernelInterface::MASTER_REQUEST ? $calls = 1 : $calls = 0;
        $request = Request::create( $uri );
        $event->expects( $this->exactly( $calls ) )
            ->method( 'getRequest' )
            ->will( $this->returnValue( $request ) );

        return $event;
    }

    /**
     * @dataProvider onKernelRequestProvider
     */
    public function testOnKernelRequest(
        $name,
        ContainerInterface $container,
        RESTRequest $request,
        CsrfProviderInterface $csrfProvider,
        GetResponseEvent $event,
        $shouldThrowException
    )
    {
        $listener = new RestListener( $container, $request, $csrfProvider );
        $exception = false;
        try
        {
            $listener->onKernelRequest( $event );
        }
        catch ( UnauthorizedException $e )
        {
            $exception = true;
        }
        $this->assertTrue( $exception === $shouldThrowException );
    }

    public function onKernelRequestProvider()
    {
        $request = $this->getRequestMock();
        $csrfProvider = $this->getCsrfProviderMock();
        $tests = array();
        $tests[] = array(
            'csrf disabled',
            $this->getContainerMock( false ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(),
            false
        );
        $tests[] = array(
            'session not started',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(),
            false
        );
        $tests[] = array(
            'sub-request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::SUB_REQUEST
            ),
            false
        );
        $tests[] = array(
            'not a REST request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST
            ),
            false
        );
        $tests[] = array(
            'not a REST request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://phpunit.de/manual/3.7/', 'POST'
            ),
            false
        );
        $tests[] = array(
            'REST GET request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'GET'
            ),
            false
        );
        $tests[] = array(
            'REST HEAD request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'HEAD'
            ),
            false
        );
        $tests[] = array(
            'REST createSession POST request',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'POST',
                'ezpublish_rest_createSession'
            ),
            false
        );
        $tests[] = array(
            'REST POST request without CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'POST',
                'a_rest_route'
            ),
            true
        );
        $tests[] = array(
            'REST PUT request without CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'PUT',
                'a_rest_route'
            ),
            true
        );
        $tests[] = array(
            'REST DELETE request without CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'DELETE',
                'a_rest_route'
            ),
            true
        );
        $tests[] = array(
            'REST CUSTOMVERB request without CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'CUSTOMVERB',
                'a_rest_route'
            ),
            true
        );
        $tests[] = array(
            'REST POST request with an invalid CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'POST',
                'a_rest_route',
                array( RestListener::CSRF_TOKEN_HEADER => self::INVALID_TOKEN )
            ),
            true
        );
        $tests[] = array(
            'REST POST request with a valid CSRF header',
            $this->getContainerMock( true ),
            $request,
            $csrfProvider,
            $this->getGetResponseEventMock(
                true, HttpKernelInterface::MASTER_REQUEST,
                'http://example.com' . self::REST_PREFIX . '/', 'POST',
                'a_rest_route',
                array( RestListener::CSRF_TOKEN_HEADER => self::VALID_TOKEN )
            ),
            false
        );
        return $tests;
    }

    protected function getContainerMock( $enabled )
    {
        $container = $this->getMock(
            'Symfony\\Component\\DependencyInjection\\ContainerInterface'
        );
        $container->expects( $this->atLeastOnce() )
            ->method( 'getParameter' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'form.type_extension.csrf.enabled', $enabled ),
                        array( 'ezpublish_rest.path_prefix', self::REST_PREFIX ),
                        array( 'ezpublish_rest.csrf_token_intention', self::INTENTION )
                    )
                )
            );
        $container->expects( $this->once() )
            ->method( 'get' )
            ->with(
                'event_dispatcher',
                $this->anything()
            )
            ->will(
                $this->returnValue(
                    $this->getMock(
                        'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface'
                    )
                )
            );
        return $container;
    }

    protected function getCsrfProviderMock()
    {
        $provider = $this->getMock(
            'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\CsrfProviderInterface'
        );
        $provider->expects( $this->any() )
            ->method( 'isCsrfTokenValid' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( self::INTENTION, self::VALID_TOKEN, true ),
                        array( self::INTENTION, self::INVALID_TOKEN, false )
                    )
                )
            );
        return $provider;
    }

    protected function getGetResponseEventMock(
        $sessionStarted = false,
        $requestType = HttpKernelInterface::MASTER_REQUEST,
        $uri = 'http://example.com/not/a/rest/request',
        $method = 'GET',
        $route = 'a_rest_route',
        $customHeaders = array()
    )
    {
        $event = $this->getMockBuilder( 'Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'getRequest', 'getRequestType' ) )
            ->getMock();

        $session = $this->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Session\\SessionInterface' )
            ->getMock();
        $session->expects( $this->once() )
            ->method( 'isStarted' )
            ->will( $this->returnValue( $sessionStarted ) );

        $request = Request::create( $uri . '?_route=' . $route, $method );
        $request->setSession( $session );

        foreach ( $customHeaders as $key => $val )
        {
            $request->headers->set( $key, $val );
        }

        $event->expects( $this->any() )
            ->method( 'getRequest' )
            ->will( $this->returnValue( $request ) );
        $event->expects( $this->once() )
            ->method( 'getRequestType' )
            ->will( $this->returnValue( $requestType ) );

        return $event;
    }

    protected function getRequestMock()
    {
        $request = $this->getMockBuilder( 'eZ\\Publish\\Core\\REST\\Server\\Request' )
            ->disableOriginalConstructor()
            ->getMock();
        return $request;
    }

}
