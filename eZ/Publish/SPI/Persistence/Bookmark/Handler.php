<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Bookmark;

interface Handler
{
    /**
     * Create a new bookmark.
     *
     * @param \eZ\Publish\SPI\Persistence\Bookmark\CreateStruct $createStruct
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     */
    public function create(CreateStruct $createStruct): Bookmark;

    /**
     * Delete a bookmark.
     *
     * @param int $bookmarkId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function delete(int $bookmarkId): void;

    /**
     * Get bookmark by id.
     *
     * @param int $bookmarkId
     *
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadById(int $bookmarkId): Bookmark;

    /**
     * Get bookmark by user id and location id.
     *
     * @param int $userId
     * @param int $locationId
     *
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function loadByUserIdAndLocationId(int $userId, int $locationId): Bookmark;

    /**
     * Loads bookmarks owned by user.
     *
     * @param int $userId
     * @param int $offset the start offset for paging
     * @param int $limit the number of bookmarked locations returned
     *
     * @return \eZ\Publish\SPI\Persistence\Bookmark\Bookmark[]
     */
    public function loadUserBookmarks(int $userId, int $offset = 0, int $limit = -1): array;

    /**
     * Count bookmarks owned by user.
     *
     * @param int $userId
     * @return int
     */
    public function countUserBookmarks(int $userId): int;
}
