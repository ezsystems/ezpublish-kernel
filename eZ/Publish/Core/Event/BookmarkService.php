<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Events\Bookmark\BeforeCreateBookmarkEvent as BeforeCreateBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\BeforeDeleteBookmarkEvent as BeforeDeleteBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\CreateBookmarkEvent as CreateBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\DeleteBookmarkEvent as DeleteBookmarkEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\Bookmark\BeforeCreateBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\BeforeDeleteBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\CreateBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\DeleteBookmarkEvent;
use eZ\Publish\SPI\Repository\Decorator\BookmarkServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BookmarkService extends BookmarkServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        BookmarkServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createBookmark(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeCreateBookmarkEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateBookmarkEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->createBookmark($location);

        $this->eventDispatcher->dispatch(new CreateBookmarkEvent(...$eventData), CreateBookmarkEventInterface::class);
    }

    public function deleteBookmark(Location $location): void
    {
        $eventData = [$location];

        $beforeEvent = new BeforeDeleteBookmarkEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteBookmarkEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteBookmark($location);

        $this->eventDispatcher->dispatch(new DeleteBookmarkEvent(...$eventData), DeleteBookmarkEventInterface::class);
    }
}
