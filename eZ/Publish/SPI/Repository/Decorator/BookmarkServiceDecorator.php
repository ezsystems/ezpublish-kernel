<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\BookmarkService;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;

abstract class BookmarkServiceDecorator implements BookmarkService
{
    /** @var \eZ\Publish\API\Repository\BookmarkService */
    protected $innerService;

    public function __construct(BookmarkService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createBookmark(Location $location): void
    {
        $this->innerService->createBookmark($location);
    }

    public function deleteBookmark(Location $location): void
    {
        $this->innerService->deleteBookmark($location);
    }

    public function loadBookmarks(
        int $offset = 0,
        int $limit = 25
    ): BookmarkList {
        return $this->innerService->loadBookmarks($offset, $limit);
    }

    public function isBookmarked(Location $location): bool
    {
        return $this->innerService->isBookmarked($location);
    }
}
