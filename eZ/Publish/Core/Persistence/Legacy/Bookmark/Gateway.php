<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Bookmark;

use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;

/**
 * Base class for bookmark gateways.
 */
abstract class Gateway
{
    /**
     * Insert a bookmark.
     *
     * @param \eZ\Publish\SPI\Persistence\Bookmark\Bookmark $bookmark
     *
     * @return int ID
     */
    abstract public function insertBookmark(Bookmark $bookmark): int;

    /**
     * Delete bookmark with the given $id.
     *
     * @param int $id ID of bookmark
     */
    abstract public function deleteBookmark(int $id): void;

    /**
     * Load data for an bookmark with the given $userId and $locationId.
     *
     * @param int $userId ID of user
     * @param array $locationIds ID of location
     *
     * @return array
     */
    abstract public function loadBookmarkDataByUserIdAndLocationId(int $userId, array $locationIds): array;

    /**
     * Load data for all bookmarks owned by given $userId.
     *
     * @param int $userId ID of user
     * @param int $offset Offset to start listing from, 0 by default
     * @param int $limit Limit for the listing. -1 by default (no limit)
     *
     * @return array
     */
    abstract public function loadUserBookmarks(int $userId, int $offset = 0, int $limit = -1): array;

    /**
     * Count bookmarks owned by given $userId.
     *
     * @param int $userId ID of user
     *
     * @return int
     */
    abstract public function countUserBookmarks(int $userId): int;

    /**
     * Updates related bookmarks when location was swapped.
     *
     * @param int $location1Id ID of first location
     * @param int $location2Id ID of second location
     */
    abstract public function locationSwapped(int $location1Id, int $location2Id): void;
}
