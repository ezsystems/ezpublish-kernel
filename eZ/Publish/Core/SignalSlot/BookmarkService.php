<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Decorator\BookmarkServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\CreateBookmarkSignal;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\DeleteBookmarkSignal;

class BookmarkService extends BookmarkServiceDecorator
{
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
        parent::__construct($service);

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
}
