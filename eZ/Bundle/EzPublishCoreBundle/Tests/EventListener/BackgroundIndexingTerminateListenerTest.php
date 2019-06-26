<?php

/**
 * File containing the BackgroundIndexingTerminateListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\BackgroundIndexingTerminateListener;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\HttpKernel\KernelEvents;

class BackgroundIndexingTerminateListenerTest extends TestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\EventListener\BackgroundIndexingTerminateListener */
    protected $listener;

    /** @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit\Framework\MockObject\MockObject */
    protected $persistenceMock;

    /** @var \eZ\Publish\SPI\Search\Handler|\PHPUnit\Framework\MockObject\MockObject */
    protected $searchMock;

    protected function setUp()
    {
        parent::setUp();
        $this->persistenceMock = $this->createMock(PersistenceHandler::class);
        $this->searchMock = $this->createMock(SearchHandler::class);
        $this->listener = new BackgroundIndexingTerminateListener(
            $this->persistenceMock,
            $this->searchMock
        );
    }

    protected function tearDown()
    {
        unset($this->persistenceMock, $this->searchMock, $this->listener);
        parent::tearDown();
    }

    public function testGetSubscribedEvents()
    {
        self::assertSame(
            [
                KernelEvents::TERMINATE => 'reindex',
                KernelEvents::EXCEPTION => 'reindex',
                ConsoleEvents::TERMINATE => 'reindex',
            ],
            BackgroundIndexingTerminateListener::getSubscribedEvents()
        );
    }

    public function indexingProvider()
    {
        $info = new ContentInfo(['id' => 33]);
        $location = new Location(['id' => 44, 'contentId' => 33]);

        return [
            [[$location]],
            [[$location], $this->createMock(LoggerInterface::class)],
            [[$info]],
            [[$info], $this->createMock(LoggerInterface::class)],
            [null],
            [null, $this->createMock(LoggerInterface::class)],
            [[$location, $info]],
            [[$info, $location], $this->createMock(LoggerInterface::class)],
        ];
    }

    /**
     * @dataProvider indexingProvider
     *
     * @param array|null $value
     * @param \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject|null $logger
     */
    public function testIndexing(array $values = null, $logger = null)
    {
        $contentHandlerMock = $this->createMock(Content\Handler::class);
        $this->persistenceMock
            ->expects($this->once())
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        if ($values) {
            $contentHandlerMock
                ->expects($this->once())
                ->method('loadContentInfo')
                ->with(33)
                ->willReturn(new ContentInfo(['id' => 33, 'currentVersionNo' => 2, 'isPublished' => true]));

            $contentHandlerMock
                ->expects($this->once())
                ->method('load')
                ->with(33, 2)
                ->willReturn(new Content());

            $this->searchMock
                ->expects($this->once())
                ->method('indexContent')
                ->with($this->isInstanceOf(Content::class));

            $this->searchMock->expects($this->never())->method('indexLocation');
            $this->searchMock->expects($this->never())->method('deleteContent');
            $this->searchMock->expects($this->never())->method('deleteLocation');
        } else {
            $contentHandlerMock->expects($this->never())->method($this->anything());
            $this->searchMock->expects($this->never())->method($this->anything());
        }

        foreach ((array) $values as $value) {
            if ($value instanceof Location) {
                $this->listener->registerLocation($value);
            } elseif ($value instanceof ContentInfo) {
                $this->listener->registerContent($value);
            }
        }

        if ($logger) {
            $this->listener->setLogger($logger);

            if ($values) {
                $logger->expects($this->once())
                    ->method('warning')
                    ->with($this->isType('string'));
            } else {
                $logger->expects($this->never())
                    ->method('warning');
            }
        }

        $this->listener->reindex();
    }

    public function indexDeleteProvider()
    {
        $location = new Location(['id' => 44, 'contentId' => 33]);
        $info = new ContentInfo(['id' => 33, 'currentVersionNo' => 2, 'isPublished' => true]);

        $infoReturn = $this->returnValue($info);
        $infoReturnUnPublished = $this->returnValue(new ContentInfo(['id' => 33, 'currentVersionNo' => 2]));
        $returnThrow = $this->throwException(new NotFoundException('content', '33'));

        return [
            [$location, $infoReturn, $returnThrow],
            [$location, $returnThrow],
            [$location, $infoReturnUnPublished],

            [$info, $infoReturn, $returnThrow],
            [$info, $returnThrow],
            [$info, $infoReturnUnPublished],
        ];
    }

    /**
     * @dataProvider indexDeleteProvider
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo|\eZ\Publish\SPI\Persistence\Content\Location $value
     * @param \PHPUnit\Framework\MockObject\Stub $infoReturn
     * @param \PHPUnit\Framework\MockObject\Stub|null $contentReturn
     */
    public function testIndexDelete($value, $infoReturn, $contentReturn = null)
    {
        $contentHandlerMock = $this->createMock(Content\Handler::class);
        $this->persistenceMock
            ->expects($this->once())
            ->method('contentHandler')
            ->willReturn($contentHandlerMock);

        $contentHandlerMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with(33)
            ->will($infoReturn);

        if ($contentReturn) {
            $contentHandlerMock
                ->expects($this->once())
                ->method('load')
                ->with(33, 2)
                ->will($contentReturn);
        } else {
            $contentHandlerMock
                ->expects($this->never())
                ->method('load');
        }

        $this->searchMock->expects($this->never())->method('indexContent');
        $this->searchMock->expects($this->never())->method('indexLocation');

        if ($value instanceof Location) {
            $contentId = $value->contentId;
            $locationId = $value->id;
            $this->listener->registerLocation($value);
        } else {
            $contentId = $value->id;
            $locationId = $value->mainLocationId;
            $this->listener->registerContent($value);
        }

        $this->searchMock
            ->expects($this->once())
            ->method('deleteContent')
            ->with($contentId);

        if ($locationId) {
            $this->searchMock
                ->expects($this->once())
                ->method('deleteLocation')
                ->with($locationId);
        } else {
            $this->searchMock->expects($this->never())->method('deleteLocation');
        }

        $this->listener->reindex();
    }
}
