<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\BookmarkService;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;

abstract class BookmarkServiceDecorator implements BookmarkService
{
    /**
     * @var \eZ\Publish\API\Repository\BookmarkService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\BookmarkService $service
     */
    public function __construct(BookmarkService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createBookmark(Location $location): void
    {
        $this->service->createBookmark($location);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookmark(Location $location): void
    {
        $this->service->deleteBookmark($location);
    }

    /**
     * {@inheritdoc}
     */
    public function loadBookmarks(int $offset = 0, int $limit = 25): BookmarkList
    {
        return $this->service->loadBookmarks($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function isBookmarked(Location $location): bool
    {
        return $this->service->isBookmarked($location);
    }
}
