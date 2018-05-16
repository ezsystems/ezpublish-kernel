<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Bookmark;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as HandlerInterface;

/**
 * Storage Engine handler for bookmarks.
 */
class Handler implements HandlerInterface
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway */
    private $gateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper */
    private $mapper;

    /**
     * Handler constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Mapper $mapper
     */
    public function __construct(Gateway $gateway, Mapper $mapper)
    {
        $this->gateway = $gateway;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $createStruct): Bookmark
    {
        $bookmark = $this->mapper->createBookmarkFromCreateStruct(
            $createStruct
        );
        $bookmark->id = $this->gateway->insertBookmark($bookmark);

        return $bookmark;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $bookmarkId): void
    {
        $this->gateway->deleteBookmark($bookmarkId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadByUserIdAndLocationId(int $userId, int $locationId): Bookmark
    {
        $bookmark = $this->mapper->extractBookmarksFromRows(
            $this->gateway->loadBookmarkDataByUserIdAndLocationId($userId, $locationId)
        );

        if (count($bookmark) < 1) {
            throw new NotFoundException('Bookmark', [
                'userId' => $userId,
                'locationId' => $locationId,
            ]);
        }

        return reset($bookmark);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserBookmarks(int $userId, int $offset = 0, int $limit = -1): array
    {
        return $this->mapper->extractBookmarksFromRows(
            $this->gateway->loadUserBookmarks($userId, $offset, $limit)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function countUserBookmarks(int $userId): int
    {
        return $this->gateway->countUserBookmarks($userId);
    }
}
