<?php
/**
 * File containing the RequestEventListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use eZ\Bundle\EzPublishCoreBundle\Kernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var RequestEventListener
     */
    private $requestEventListener;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var GetResponseEvent
     */
    private $event;

    /**
     * @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpKernel;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );

        $this->requestEventListener = new RequestEventListener( $this->container, $this->logger );

        $this->request = $this
            ->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Request' )
            ->setMethods( array( 'getSession', 'hasSession' ) )
            ->getMock();

        $this->httpKernel = $this->getMock( 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' );
        $this->event = new GetResponseEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener::onKernelRequestUserHash
     */
    public function testOnKernelRequestUserHashNotAuthenticate()
    {
        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertFalse( $this->event->hasResponse() );
        $this->assertFalse( $this->event->isPropagationStopped() );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener::onKernelRequestUserHash
     */
    public function testOnKernelRequestUserHashAuthenticateNoSession()
    {
        $this->request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );

        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertTrue( $this->event->hasResponse() );
        $this->assertTrue( $this->event->isPropagationStopped() );
        $this->assertSame( 400, $this->event->getResponse()->getStatusCode() );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener::onKernelRequestUserHash
     */
    public function testOnKernelRequestUserHash()
    {
        $hashGenerator = $this->getMock( 'eZ\\Publish\\SPI\\HashGenerator' );
        $hash = '123abc';
        $hashGenerator
            ->expects( $this->once() )
            ->method( 'generate' )
            ->will( $this->returnValue( $hash ) );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.user.hash_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $hashGenerator ),
                        array( 'logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger ),
                    )
                )
            );

        $this->request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $this->request
            ->expects( $this->once() )
            ->method( 'hasSession' )
            ->will( $this->returnValue( true ) );

        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertTrue( $this->event->isPropagationStopped() );
        $this->assertTrue( $this->event->hasResponse() );
        $response = $this->event->getResponse();
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertTrue( $response->headers->has( 'X-User-Hash' ) );
        $this->assertSame( $hash, $response->headers->get( 'X-User-Hash' ) );
    }

    public function testOnKernelRequestForwardSubRequest()
    {
        $this->httpKernel
            ->expects( $this->never() )
            ->method( 'handle' );

        $event = new GetResponseEvent( $this->httpKernel, new Request, HttpKernelInterface::SUB_REQUEST );
        $this->requestEventListener->onKernelRequestForward( $event );
    }

    public function testOnKernelRequestForward()
    {
        $queryParameters = array( 'some' => 'thing' );
        $cookieParameters = array( 'cookie' => 'value' );
        $request = Request::create( '/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters );
        $semanticPathinfo = '/foo/something';
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( 'needsForward', true );
        $request->attributes->set( 'someAttribute', 'someValue' );

        $expectedForwardRequest = Request::create( $semanticPathinfo, 'GET', $queryParameters, $cookieParameters );
        $expectedForwardRequest->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $expectedForwardRequest->attributes->set( 'someAttribute', 'someValue' );

        $response = new Response( 'Success!' );
        $this->httpKernel
            ->expects( $this->once() )
            ->method( 'handle' )
            ->with( $this->equalTo( $expectedForwardRequest ) )
            ->will( $this->returnValue( $response ) );

        $event = new GetResponseEvent( $this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST );
        $this->requestEventListener->onKernelRequestForward( $event );
        $this->assertSame( $response, $event->getResponse() );
        $this->assertTrue( $event->isPropagationStopped() );
    }
}
