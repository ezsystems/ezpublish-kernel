<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\CreateBookmarkSignal;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\DeleteBookmarkSignal;

class BookmarkService implements BookmarkServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\BookmarkService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * BookmarkService constructor.
     *
     * @param \eZ\Publish\API\Repository\BookmarkService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(BookmarkServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function createBookmark(Location $location): void
    {
        $this->service->createBookmark($location);

        $this->signalDispatcher->emit(new CreateBookmarkSignal([
            'locationId' => $location->id,
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookmark(Location $location): void
    {
        $this->service->deleteBookmark($location);

        $this->signalDispatcher->emit(new DeleteBookmarkSignal([
            'locationId' => $location->id,
        ]));
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
