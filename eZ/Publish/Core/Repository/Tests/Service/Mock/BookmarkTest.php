<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use Exception;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\BookmarkService;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use PHPUnit\Framework\MockObject\MockObject;

class BookmarkTest extends BaseServiceMockTest
{
    const BOOKMARK_ID = 2;
    const CURRENT_USER_ID = 7;
    const LOCATION_ID = 1;

    /** @var \eZ\Publish\SPI\Persistence\Bookmark\Handler|\PHPUnit\Framework\MockObject\MockObject */
    private $bookmarkHandler;

    protected function setUp()
    {
        parent::setUp();

        $this->bookmarkHandler = $this->getPersistenceMockHandler('Bookmark\\Handler');

        $permissionResolverMock = $this->createMock(PermissionResolver::class);
        $permissionResolverMock
            ->expects($this->atLeastOnce())
            ->method('getCurrentUserReference')
            ->willReturn(new UserReference(self::CURRENT_USER_ID));

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->atLeastOnce())
            ->method('getPermissionResolver')
            ->willReturn($permissionResolverMock);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::createBookmark
     */
    public function testCreateBookmark()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([]);

        $this->assertTransactionIsCommitted(function () {
            $this->bookmarkHandler
                ->expects($this->once())
                ->method('create')
                ->willReturnCallback(function (CreateStruct $createStruct) {
                    $this->assertEquals(self::LOCATION_ID, $createStruct->locationId);
                    $this->assertEquals(self::CURRENT_USER_ID, $createStruct->userId);

                    return new Bookmark();
                });
        });

        $this->createBookmarkService()->createBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::createBookmark
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateBookmarkThrowsInvalidArgumentException()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([self::LOCATION_ID => new Bookmark()]);

        $this->assertTransactionIsNotStarted(function () {
            $this->bookmarkHandler->expects($this->never())->method('create');
        });

        $this->createBookmarkService()->createBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::createBookmark
     * @expectedException \Exception
     */
    public function testCreateBookmarkWithRollback()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([]);

        $this->assertTransactionIsRollback(function () {
            $this->bookmarkHandler
                ->expects($this->once())
                ->method('create')
                ->willThrowException($this->createMock(Exception::class));
        });

        $this->createBookmarkService()->createBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::deleteBookmark
     */
    public function testDeleteBookmarkExisting()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $bookmark = new Bookmark(['id' => self::BOOKMARK_ID]);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([self::LOCATION_ID => $bookmark]);

        $this->assertTransactionIsCommitted(function () use ($bookmark) {
            $this->bookmarkHandler
                ->expects($this->once())
                ->method('delete')
                ->with($bookmark->id);
        });

        $this->createBookmarkService()->deleteBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::deleteBookmark
     * @expectedException \Exception
     */
    public function testDeleteBookmarkWithRollback()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([self::LOCATION_ID => new Bookmark(['id' => self::BOOKMARK_ID])]);

        $this->assertTransactionIsRollback(function () {
            $this->bookmarkHandler
                ->expects($this->once())
                ->method('delete')
                ->willThrowException($this->createMock(Exception::class));
        });

        $this->createBookmarkService()->deleteBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::deleteBookmark
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDeleteBookmarkNonExisting()
    {
        $location = $this->createLocation(self::LOCATION_ID);

        $this->assertLocationIsLoaded($location);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([]);

        $this->assertTransactionIsNotStarted(function () {
            $this->bookmarkHandler->expects($this->never())->method('delete');
        });

        $this->createBookmarkService()->deleteBookmark($location);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::loadBookmarks
     */
    public function testLoadBookmarks()
    {
        $offset = 0;
        $limit = 25;

        $expectedTotalCount = 10;
        $expectedItems = array_map(function ($locationId) {
            return $this->createLocation($locationId);
        }, range(1, $expectedTotalCount));

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('countUserBookmarks')
            ->with(self::CURRENT_USER_ID)
            ->willReturn($expectedTotalCount);

        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadUserBookmarks')
            ->with(self::CURRENT_USER_ID, $offset, $limit)
            ->willReturn(array_map(function ($locationId) {
                return new Bookmark(['locationId' => $locationId]);
            }, range(1, $expectedTotalCount)));

        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock
            ->expects($this->exactly($expectedTotalCount))
            ->method('loadLocation')
            ->willReturnCallback(function ($locationId) {
                return $this->createLocation($locationId);
            });

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->any())
            ->method('getLocationService')
            ->willReturn($locationServiceMock);

        $bookmarks = $this->createBookmarkService()->loadBookmarks($offset, $limit);

        $this->assertEquals($expectedTotalCount, $bookmarks->totalCount);
        $this->assertEquals($expectedItems, $bookmarks->items);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::loadBookmarks
     */
    public function testLoadBookmarksEmptyList()
    {
        $this->bookmarkHandler
            ->expects($this->once())
            ->method('countUserBookmarks')
            ->with(self::CURRENT_USER_ID)
            ->willReturn(0);

        $this->bookmarkHandler
            ->expects($this->never())
            ->method('loadUserBookmarks');

        $bookmarks = $this->createBookmarkService()->loadBookmarks(0, 10);

        $this->assertEquals(0, $bookmarks->totalCount);
        $this->assertEmpty($bookmarks->items);
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::isBookmarked
     */
    public function testLocationShouldNotBeBookmarked()
    {
        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([]);

        $this->assertFalse($this->createBookmarkService()->isBookmarked($this->createLocation(self::LOCATION_ID)));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\BookmarkService::isBookmarked
     */
    public function testLocationShouldBeBookmarked()
    {
        $this->bookmarkHandler
            ->expects($this->once())
            ->method('loadByUserIdAndLocationId')
            ->with(self::CURRENT_USER_ID, [self::LOCATION_ID])
            ->willReturn([self::LOCATION_ID => new Bookmark()]);

        $this->assertTrue($this->createBookmarkService()->isBookmarked($this->createLocation(self::LOCATION_ID)));
    }

    private function assertLocationIsLoaded(Location $location): MockObject
    {
        $locationServiceMock = $this->createMock(LocationService::class);
        $locationServiceMock
            ->expects($this->once())
            ->method('loadLocation')
            ->willReturn($location);

        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->once())
            ->method('getLocationService')
            ->willReturn($locationServiceMock);

        return $locationServiceMock;
    }

    private function assertTransactionIsNotStarted(callable $operation): void
    {
        $repository = $this->getRepositoryMock();
        $repository->expects($this->never())->method('beginTransaction');
        $operation();
        $repository->expects($this->never())->method('commit');
        $repository->expects($this->never())->method('rollback');
    }

    private function assertTransactionIsCommitted(callable $operation): void
    {
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())->method('beginTransaction');
        $operation();
        $repository->expects($this->once())->method('commit');
        $repository->expects($this->never())->method('rollback');
    }

    private function assertTransactionIsRollback(callable $operation): void
    {
        $repository = $this->getRepositoryMock();
        $repository->expects($this->once())->method('beginTransaction');
        $operation();
        $repository->expects($this->never())->method('commit');
        $repository->expects($this->once())->method('rollback');
    }

    private function createLocation(int $id = self::CURRENT_USER_ID, string $name = 'Lorem ipsum...'): Location
    {
        return new Location([
            'id' => $id,
            'contentInfo' => new ContentInfo([
                'name' => $name,
            ]),
        ]);
    }

    /**
     * @return \eZ\Publish\API\Repository\BookmarkService|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createBookmarkService(array $methods = null)
    {
        return $this
            ->getMockBuilder(BookmarkService::class)
            ->setConstructorArgs([$this->getRepositoryMock(), $this->bookmarkHandler])
            ->setMethods($methods)
            ->getMock();
    }
}
