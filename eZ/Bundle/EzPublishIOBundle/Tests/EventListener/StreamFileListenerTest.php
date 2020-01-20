<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\EventListener;

use DateTime;
use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Bundle\EzPublishIOBundle\EventListener\StreamFileListener;
use eZ\Publish\Core\IO\IOConfigProvider;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StreamFileListenerTest extends TestCase
{
    /** @var StreamFileListener */
    private $eventListener;

    /** @var \eZ\Publish\Core\IO\IOServiceInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $ioServiceMock;

    /** @var \eZ\Publish\Core\IO\IOConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $ioConfigResolverMock;

    protected function setUp(): void
    {
        $this->ioServiceMock = $this->createMock(IOServiceInterface::class);

        $this->ioConfigResolverMock = $this->createMock(IOConfigProvider::class);

        $this->eventListener = new StreamFileListener($this->ioServiceMock, $this->ioConfigResolverMock);
    }

    public function testDoesNotRespondToNonIoUri()
    {
        $request = $this->createRequest('/Not-an-image');
        $event = $this->createEvent($request);

        $this->configureIoUrlPrefix('var/test/storage');
        $this->ioServiceMock
            ->expects($this->never())
            ->method('loadBinaryFileByUri');

        $this->eventListener->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testDoesNotRespondToNoIoRequest()
    {
        $request = $this->createRequest('/Not-an-image', 'bar.fr');
        $event = $this->createEvent($request);

        $this->configureIoUrlPrefix('http://foo.com/var/test/storage');
        $this->ioServiceMock
            ->expects($this->never())
            ->method('loadBinaryFileByUri');

        $this->eventListener->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testRespondsToIoUri()
    {
        $uri = '/var/test/storage/images/image.png';
        $this->configureIoUrlPrefix(ltrim($uri, '/'));
        $request = $this->createRequest($uri);

        $event = $this->createEvent($request);

        $binaryFile = new BinaryFile(['mtime' => new DateTime()]);

        $this->ioServiceMock
            ->expects($this->once())
            ->method('loadBinaryFileByUri')
            ->with($uri)
            ->will($this->returnValue($binaryFile));

        $this->eventListener->onKernelRequest($event);

        self::assertTrue($event->hasResponse());
        $expectedResponse = new BinaryStreamResponse($binaryFile, $this->ioServiceMock);
        $response = $event->getResponse();
        // since symfony/symfony v3.2.7 Response sets Date header if not explicitly set
        // @see https://github.com/symfony/symfony/commit/e3d90db74773406fb8fdf07f36cb8ced4d187f62
        $expectedResponse->setDate($response->getDate());
        self::assertEquals(
            $expectedResponse,
            $response
        );
    }

    public function testRespondsToIoRequest()
    {
        $uri = '/var/test/storage/images/image.png';
        $host = 'phoenix-rises.fm';
        $urlPrefix = "http://$host/var/test/storage";
        $this->configureIoUrlPrefix($urlPrefix);
        $request = $this->createRequest($uri, $host);

        $event = $this->createEvent($request);

        $binaryFile = new BinaryFile(['mtime' => new DateTime()]);

        $this->ioServiceMock
            ->expects($this->once())
            ->method('loadBinaryFileByUri')
            ->with(sprintf('http://%s%s', $host, $uri))
            ->will($this->returnValue($binaryFile));

        $this->eventListener->onKernelRequest($event);

        self::assertTrue($event->hasResponse());
        $expectedResponse = new BinaryStreamResponse($binaryFile, $this->ioServiceMock);
        $response = $event->getResponse();
        // since symfony/symfony v3.2.7 Response sets Date header if not explicitly set
        // @see https://github.com/symfony/symfony/commit/e3d90db74773406fb8fdf07f36cb8ced4d187f62
        $expectedResponse->setDate($response->getDate());
        self::assertEquals(
            $expectedResponse,
            $response
        );
    }

    private function configureIoUrlPrefix($urlPrefix)
    {
        $this->ioConfigResolverMock
            ->method('getUrlPrefix')
            ->willReturn($urlPrefix);
    }

    /**
     * @return Request
     */
    protected function createRequest($semanticPath, $host = 'localhost')
    {
        $request = Request::create(sprintf('http://%s%s', $host, $semanticPath));
        $request->attributes->set('semanticPathinfo', $semanticPath);

        return $request;
    }

    /**
     * @param $request
     *
     * @return RequestEvent
     */
    protected function createEvent($request)
    {
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        return $event;
    }
}
