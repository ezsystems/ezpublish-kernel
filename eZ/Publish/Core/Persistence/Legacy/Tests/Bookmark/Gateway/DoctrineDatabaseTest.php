<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Bookmark\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use PDO;

class DoctrineDatabaseTest extends TestCase
{
    const EXISTING_BOOKMARK_ID = 1;
    const EXISTING_BOOKMARK_DATA = [
        'id' => 1,
        'name' => 'Lorem ipsum dolor',
        'node_id' => 5,
        'user_id' => 14,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(__DIR__ . '/../_fixtures/bookmarks.php');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::insertBookmark
     */
    public function testInsertBookmark()
    {
        $id = $this->getGateway()->insertBookmark(new Bookmark([
            'userId' => 14,
            'locationId' => 54,
            'name' => 'Lorem ipsum dolor...',
        ]));

        $data = $this->loadBookmark($id);

        $this->assertEquals([
            'id' => $id,
            'name' => 'Lorem ipsum dolor...',
            'node_id' => '54',
            'user_id' => '14',
        ], $data);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::deleteBookmark
     */
    public function testDeleteBookmark()
    {
        $this->getGateway()->deleteBookmark(self::EXISTING_BOOKMARK_ID);

        $this->assertEmpty($this->loadBookmark(self::EXISTING_BOOKMARK_ID));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::loadBookmarkDataById
     */
    public function testLoadBookmarkDataById()
    {
        $this->assertEquals(
            [self::EXISTING_BOOKMARK_DATA],
            $this->getGateway()->loadBookmarkDataById(self::EXISTING_BOOKMARK_ID)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::loadBookmarkDataByUserIdAndLocationId
     */
    public function testLoadBookmarkDataByUserIdAndLocationId()
    {
        $data = $this->getGateway()->loadBookmarkDataByUserIdAndLocationId(
            (int) self::EXISTING_BOOKMARK_DATA['user_id'],
            [(int) self::EXISTING_BOOKMARK_DATA['node_id']]
        );

        $this->assertEquals([self::EXISTING_BOOKMARK_DATA], $data);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::loadUserBookmarks
     * @dataProvider dataProviderForLoadUserBookmarks
     */
    public function testLoadUserBookmarks(int $userId, int $offset, int $limit, array $expected)
    {
        $this->assertEquals($expected, $this->getGateway()->loadUserBookmarks($userId, $offset, $limit));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::countUserBookmarks
     * @dataProvider dataProviderForLoadUserBookmarks
     */
    public function testCountUserBookmarks(int $userId, int $offset, int $limit, array $expected)
    {
        $this->assertEquals(count($expected), $this->getGateway()->countUserBookmarks($userId));
    }

    public function dataProviderForLoadUserBookmarks(): array
    {
        $fixtures = (require __DIR__ . '/../_fixtures/bookmarks.php')[DoctrineDatabase::TABLE_BOOKMARKS];

        $expectedRows = function ($userId) use ($fixtures) {
            $rows = array_filter($fixtures, function (array $row) use ($userId) {
                return $row['user_id'] == $userId;
            });

            usort($rows, function ($a, $b) {
                return $a['id'] < $b['id'];
            });

            return $rows;
        };

        $userId = self::EXISTING_BOOKMARK_DATA['user_id'];

        return [
            [
                $userId, 0, 10, $expectedRows($userId),
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway\DoctrineDatabase::locationSwapped
     */
    public function testLocationSwapped()
    {
        $bookmark1Id = 3;
        $bookmark2Id = 4;

        $bookmark1BeforeSwap = $this->loadBookmark($bookmark1Id);
        $bookmark2BeforeSwap = $this->loadBookmark($bookmark2Id);

        $this->getGateway()->locationSwapped(
            (int) $bookmark1BeforeSwap['node_id'],
            (int) $bookmark2BeforeSwap['node_id']
        );

        $bookmark1AfterSwap = $this->loadBookmark($bookmark1Id);
        $bookmark2AfterSwap = $this->loadBookmark($bookmark2Id);

        $this->assertEquals($bookmark1BeforeSwap['node_id'], $bookmark2AfterSwap['node_id']);
        $this->assertEquals($bookmark2BeforeSwap['node_id'], $bookmark1AfterSwap['node_id']);
    }

    /**
     * Return a ready to test DoctrineStorage gateway.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getGateway(): Gateway
    {
        return new DoctrineDatabase($this->getDatabaseConnection());
    }

    private function loadBookmark(int $id): array
    {
        $data = $this->connection
            ->executeQuery('SELECT * FROM ezcontentbrowsebookmark WHERE id = :id', ['id' => $id])
            ->fetch(PDO::FETCH_ASSOC);

        return is_array($data) ? $data : [];
    }
}
