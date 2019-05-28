<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\BookmarkService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\SPI\Repository\Decorator\BookmarkServiceDecorator;

class BookmarkServiceDecoratorTest extends TestCase
{
    protected function createDecorator(BookmarkService $service): BookmarkService
    {
        return new class($service) extends BookmarkServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(BookmarkService::class);
    }

    public function testCreateBookmarkDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Location::class)];

        $serviceMock->expects($this->exactly(1))->method('createBookmark')->with(...$parameters);

        $decoratedService->createBookmark(...$parameters);
    }

    public function testDeleteBookmarkDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Location::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteBookmark')->with(...$parameters);

        $decoratedService->deleteBookmark(...$parameters);
    }

    public function testLoadBookmarksDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            679,
            606,
        ];

        $serviceMock->expects($this->exactly(1))->method('loadBookmarks')->with(...$parameters);

        $decoratedService->loadBookmarks(...$parameters);
    }

    public function testIsBookmarkedDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Location::class)];

        $serviceMock->expects($this->exactly(1))->method('isBookmarked')->with(...$parameters);

        $decoratedService->isBookmarked(...$parameters);
    }
}
