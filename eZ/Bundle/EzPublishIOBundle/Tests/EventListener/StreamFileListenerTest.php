<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\EventListener;

use eZ\Bundle\EzPublishIOBundle\EventListener\StreamFileListener;
use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\Core\IO\Values\BinaryFile;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use DateTime;

class StreamFileListenerTest extends PHPUnit_Framework_TestCase
{
    /** @var StreamFileListener */
    private $eventListener;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $ioServiceMock;

    private $ioUriPrefix = 'var/test/storage';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $configResolverMock;

    public function setUp(  )
    {
        $this->ioServiceMock = $this->getMock( 'eZ\Publish\Core\IO\IOServiceInterface' );

        $this->configResolverMock = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->configResolverMock
            ->expects( $this->any() )
            ->method( 'getParameter' )
            ->with( 'io.url_prefix' )
            ->will( $this->returnValue( $this->ioUriPrefix ) );

        $this->eventListener = new StreamFileListener( $this->ioServiceMock, $this->configResolverMock );
    }

    public function testDoesNotRespondToNonIoUri()
    {
        $request = $this->createRequest( '/Not-an-image' );
        $event = $this->createEvent( $request );

        $this->eventListener->onKernelRequest( $event );

        self::assertNull( $event->getResponse() );
    }

    public function testRespondsToIoUri()
    {
        $uri = '/var/test/storage/images/image.png';
        $request = $this->createRequest( $uri );

        $event = $this->createEvent( $request );

        $binaryFile = new BinaryFile( array( 'mtime' => new DateTime ) );

        $this->ioServiceMock
            ->expects( $this->once() )
            ->method( 'loadBinaryFileByUri' )
            ->with( $uri )
            ->will( $this->returnValue( $binaryFile ) );

        $this->eventListener->onKernelRequest( $event );

        self::assertTrue( $event->hasResponse() );
        self::assertEquals(
            new BinaryStreamResponse( $binaryFile, $this->ioServiceMock ),
            $event->getResponse()
        );
    }

    /**
     * @return Request
     */
    protected function createRequest( $semanticPath )
    {
        $request = new Request();
        $request->attributes->set( 'semanticPathinfo', $semanticPath );
        return $request;
    }

    /**
     * @param $request
     *
     * @return GetResponseEvent
     */
    protected function createEvent( $request )
    {
        $event = new GetResponseEvent(
            $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        return $event;
    }
}
