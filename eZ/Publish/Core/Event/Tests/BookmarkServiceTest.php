<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Events\Bookmark\BeforeCreateBookmarkEvent as BeforeCreateBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\BeforeDeleteBookmarkEvent as BeforeDeleteBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\CreateBookmarkEvent as CreateBookmarkEventInterface;
use eZ\Publish\API\Repository\Events\Bookmark\DeleteBookmarkEvent as DeleteBookmarkEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Events\BookmarkService;

class BookmarkServiceTest extends AbstractServiceTest
{
    public function testCreateBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateBookmarkEventInterface::class,
            CreateBookmarkEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeCreateBookmarkEventInterface::class, 0],
            [CreateBookmarkEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateBookmarkEventInterface::class,
            CreateBookmarkEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeCreateBookmarkEventInterface::class, function (BeforeCreateBookmarkEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeCreateBookmarkEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateBookmarkEventInterface::class, 0],
            [CreateBookmarkEventInterface::class, 0],
        ]);
    }

    public function testDeleteBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteBookmarkEventInterface::class,
            DeleteBookmarkEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteBookmarkEventInterface::class, 0],
            [DeleteBookmarkEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteBookmarkEventInterface::class,
            DeleteBookmarkEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteBookmarkEventInterface::class, function (BeforeDeleteBookmarkEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteBookmarkEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteBookmarkEventInterface::class, 0],
            [DeleteBookmarkEventInterface::class, 0],
        ]);
    }
}
