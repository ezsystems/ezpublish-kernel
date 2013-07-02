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

        $this->event = new GetResponseEvent(
            $this->getMock( 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' ),
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
        $this->assertFalse( $this->event->hasResponse() );
        $this->assertFalse( $this->event->isPropagationStopped() );
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
}
