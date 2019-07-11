<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\Bookmark\BeforeCreateBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\BeforeDeleteBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\CreateBookmarkEvent;
use eZ\Publish\Core\Event\Bookmark\DeleteBookmarkEvent;
use eZ\Publish\Core\Event\BookmarkService;

class BookmarkServiceTest extends AbstractServiceTest
{
    public function testCreateBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateBookmarkEvent::class,
            CreateBookmarkEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeCreateBookmarkEvent::class, 0],
            [CreateBookmarkEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateBookmarkEvent::class,
            CreateBookmarkEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeCreateBookmarkEvent::class, function (\eZ\Publish\API\Repository\Events\Bookmark\BeforeCreateBookmarkEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->createBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeCreateBookmarkEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateBookmarkEvent::class, 0],
            [CreateBookmarkEvent::class, 0],
        ]);
    }

    public function testDeleteBookmarkEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteBookmarkEvent::class,
            DeleteBookmarkEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteBookmarkEvent::class, 0],
            [DeleteBookmarkEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteBookmarkStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteBookmarkEvent::class,
            DeleteBookmarkEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(BookmarkServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteBookmarkEvent::class, function (\eZ\Publish\API\Repository\Events\Bookmark\BeforeDeleteBookmarkEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new BookmarkService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteBookmark(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteBookmarkEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteBookmarkEvent::class, 0],
            [DeleteBookmarkEvent::class, 0],
        ]);
    }
}
