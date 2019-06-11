<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\BookmarkService;
use eZ\Publish\Core\Event\Bookmark\BeforeCreateBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\BeforeDeleteBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\BookmarkEvents;

class BookmarkServiceTest extends AbstractServiceTest
{
    public function testCreateBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BookmarkEvents::BEFORE_CREATE_BOOKMARK,
            BookmarkEvents::CREATE_BOOKMARK
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BookmarkEvents::BEFORE_CREATE_BOOKMARK, 0],
            [BookmarkEvents::CREATE_BOOKMARK, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BookmarkEvents::BEFORE_CREATE_BOOKMARK,
            BookmarkEvents::CREATE_BOOKMARK
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BookmarkEvents::BEFORE_CREATE_BOOKMARK, function (BeforeCreateBookmarkEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BookmarkEvents::BEFORE_CREATE_BOOKMARK, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BookmarkEvents::CREATE_BOOKMARK, 0],
            [BookmarkEvents::BEFORE_CREATE_BOOKMARK, 0],
        ]);
    }

    public function testDeleteBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BookmarkEvents::BEFORE_DELETE_BOOKMARK,
            BookmarkEvents::DELETE_BOOKMARK
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BookmarkEvents::BEFORE_DELETE_BOOKMARK, 0],
            [BookmarkEvents::DELETE_BOOKMARK, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BookmarkEvents::BEFORE_DELETE_BOOKMARK,
            BookmarkEvents::DELETE_BOOKMARK
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BookmarkEvents::BEFORE_DELETE_BOOKMARK, function (BeforeDeleteBookmarkEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BookmarkEvents::BEFORE_DELETE_BOOKMARK, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BookmarkEvents::DELETE_BOOKMARK, 0],
            [BookmarkEvents::BEFORE_DELETE_BOOKMARK, 0],
        ]);
    }
}
