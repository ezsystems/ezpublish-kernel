<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;

/**
 * Bookmark Service.
 *
 * Service to handle bookmarking of Content item Locations. It works in the context of a current User (obtained from
 * the PermissionResolver).
 */
interface BookmarkService
{
    /**
     * Add location to bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When location is already bookmarked
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the current user user is not allowed to create bookmark
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function createBookmark(Location $location): void;

    /**
     * Delete given location from bookmarks.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException When location is not bookmarked
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current user user is not allowed to delete bookmark
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function deleteBookmark(Location $location): void;

    /**
     * List bookmarked locations.
     *
     * @param int $offset the start offset for paging
     * @param int $limit the number of bookmarked locations returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @return \eZ\Publish\API\Repository\Values\Bookmark\BookmarkList
     */
    public function loadBookmarks(int $offset = 0, int $limit = 25): BookmarkList;

    /**
     * Return true if location is bookmarked.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function isBookmarked(Location $location): bool;
}
