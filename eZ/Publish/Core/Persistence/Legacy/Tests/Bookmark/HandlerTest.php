<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Bookmark;

use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    const BOOKMARK_ID = 7;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway|\PHPUnit\Framework\MockObject\MockObject */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper|\PHPUnit\Framework\MockObject\MockObject */
    private $mapper;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler */
    private $handler;

    protected function setUp()
    {
        $this->gateway = $this->createMock(Gateway::class);
        $this->mapper = $this->createMock(Mapper::class);
        $this->handler = new Handler($this->gateway, $this->mapper);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::create
     */
    public function testCreate()
    {
        $createStruct = new CreateStruct([
            'name' => 'Contact',
            'locationId' => 54,
            'userId' => 87,
        ]);

        $bookmark = new Bookmark([
            'name' => 'Contact',
            'locationId' => 54,
            'userId' => 87,
        ]);

        $this->mapper
            ->expects($this->once())
            ->method('createBookmarkFromCreateStruct')
            ->with($createStruct)
            ->willReturn($bookmark);

        $this->gateway
            ->expects($this->once())
            ->method('insertBookmark')
            ->with($bookmark)
            ->willReturn(self::BOOKMARK_ID);

        $this->handler->create($createStruct);

        $this->assertEquals($bookmark->id, self::BOOKMARK_ID);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::delete
     */
    public function testDelete()
    {
        $this->gateway
            ->expects($this->once())
            ->method('deleteBookmark')
            ->with(self::BOOKMARK_ID);

        $this->handler->delete(self::BOOKMARK_ID);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::loadByUserIdAndLocationId
     */
    public function testLoadByUserIdAndLocationIdExistingBookmark()
    {
        $userId = 87;
        $locationId = 54;

        $rows = [
            [
                'name' => 'Contact',
                'node_id' => $locationId,
                'user_id' => $userId,
            ],
        ];

        $object = new Bookmark([
            'name' => 'Contact',
            'locationId' => $locationId,
            'userId' => $userId,
        ]);

        $this->gateway
            ->expects($this->once())
            ->method('loadBookmarkDataByUserIdAndLocationId')
            ->with($userId, [$locationId])
            ->willReturn($rows);

        $this->mapper
            ->expects($this->once())
            ->method('extractBookmarksFromRows')
            ->with($rows)
            ->willReturn([$object]);

        $this->assertEquals([$locationId => $object], $this->handler->loadByUserIdAndLocationId($userId, [$locationId]));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::loadByUserIdAndLocationId
     */
    public function testLoadByUserIdAndLocationIdNonExistingBookmark()
    {
        $userId = 87;
        $locationId = 54;

        $this->gateway
            ->expects($this->once())
            ->method('loadBookmarkDataByUserIdAndLocationId')
            ->with($userId, [$locationId])
            ->willReturn([]);

        $this->mapper
            ->expects($this->once())
            ->method('extractBookmarksFromRows')
            ->with([])
            ->willReturn([]);

        $this->assertEmpty($this->handler->loadByUserIdAndLocationId($userId, [$locationId]));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::loadUserBookmarks
     */
    public function testLoadUserBookmarks()
    {
        $userId = 87;
        $offset = 50;
        $limit = 25;

        $rows = [
            [
                'id' => '12',
                'name' => 'Home',
                'node_id' => '2',
                'user_id' => $userId,
            ],
            [
                'id' => '75',
                'name' => 'Contact',
                'node_id' => '54',
                'user_id' => $userId,
            ],
        ];

        $objects = [
            new Bookmark([
                'id' => 12,
                'name' => 'Home',
                'locationId' => 2,
                'userId' => 78,
            ]),
            new Bookmark([
                'id' => 75,
                'name' => 'Contact',
                'locationId' => 54,
                'userId' => 87,
            ]),
        ];

        $this->gateway
            ->expects($this->once())
            ->method('loadUserBookmarks')
            ->with($userId, $offset, $limit)
            ->willReturn($rows);

        $this->mapper
            ->expects($this->once())
            ->method('extractBookmarksFromRows')
            ->with($rows)
            ->willReturn($objects);

        $this->assertEquals($objects, $this->handler->loadUserBookmarks($userId, $offset, $limit));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Handler::locationSwapped
     */
    public function testLocationSwapped()
    {
        $location1Id = 1;
        $location2Id = 2;

        $this->gateway
            ->expects($this->once())
            ->method('locationSwapped')
            ->with($location1Id, $location2Id);

        $this->handler->locationSwapped($location1Id, $location2Id);
    }
}
