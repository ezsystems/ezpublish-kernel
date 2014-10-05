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

    private static $ioUriPrefix = '/var/test/storage';

    public function setUp(  )
    {
        $this->ioServiceMock = $this->getMock( 'eZ\Publish\Core\IO\IOServiceInterface' );
        $this->eventListener = new StreamFileListener( $this->ioServiceMock, self::$ioUriPrefix );
    }

    public function testDoesNotRespondToNonIoUri()
    {
        $request = new Request();
        $request->attributes->set( 'semanticPathinfo', '/Not-an-image' );

        $event = new GetResponseEvent(
            $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->eventListener->onKernelRequest( $event );

        self::assertNull( $event->getResponse() );
    }

    public function testRespondsToIoUri()
    {
        $request = new Request();
        $uri = '/var/test/storage/images/image.png';
        $request->attributes->set( 'semanticPathinfo', $uri );

        $event = new GetResponseEvent(
            $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $binaryFile = new BinaryFile( array( 'mtime' => new DateTime ) );

        $this->ioServiceMock
            ->expects( $this->once() )
            ->method( 'loadBinaryFileByUri' )
            ->with( $uri )
            ->will( $this->returnValue( $binaryFile ) );

        $this->eventListener->onKernelRequest( $event );

        self::assertEquals(
            new BinaryStreamResponse( $binaryFile, $this->ioServiceMock ),
            $event->getResponse()
        );
    }
}
