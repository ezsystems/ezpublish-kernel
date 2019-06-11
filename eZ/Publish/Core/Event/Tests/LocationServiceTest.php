<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\Core\Event\LocationService;
use eZ\Publish\Core\Event\Location\BeforeCopySubtreeEvent;
use eZ\Publish\Core\Event\Location\BeforeCreateLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeDeleteLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeHideLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeMoveSubtreeEvent;
use eZ\Publish\Core\Event\Location\BeforeSwapLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeUnhideLocationEvent;
use eZ\Publish\Core\Event\Location\BeforeUpdateLocationEvent;
use eZ\Publish\Core\Event\Location\LocationEvents;

class LocationServiceTest extends AbstractServiceTest
{
    public function testCopySubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_COPY_SUBTREE,
            LocationEvents::COPY_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($location, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_COPY_SUBTREE, 0],
            [LocationEvents::COPY_SUBTREE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopySubtreeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_COPY_SUBTREE,
            LocationEvents::COPY_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_COPY_SUBTREE, function (BeforeCopySubtreeEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_COPY_SUBTREE, 10],
            [LocationEvents::BEFORE_COPY_SUBTREE, 0],
            [LocationEvents::COPY_SUBTREE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopySubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_COPY_SUBTREE,
            LocationEvents::COPY_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_COPY_SUBTREE, function (BeforeCopySubtreeEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_COPY_SUBTREE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::COPY_SUBTREE, 0],
            [LocationEvents::BEFORE_COPY_SUBTREE, 0],
        ]);
    }

    public function testDeleteLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_DELETE_LOCATION,
            LocationEvents::DELETE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_DELETE_LOCATION, 0],
            [LocationEvents::DELETE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_DELETE_LOCATION,
            LocationEvents::DELETE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_DELETE_LOCATION, function (BeforeDeleteLocationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_DELETE_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::DELETE_LOCATION, 0],
            [LocationEvents::BEFORE_DELETE_LOCATION, 0],
        ]);
    }

    public function testUnhideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UNHIDE_LOCATION,
            LocationEvents::UNHIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($revealedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UNHIDE_LOCATION, 0],
            [LocationEvents::UNHIDE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUnhideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UNHIDE_LOCATION,
            LocationEvents::UNHIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_UNHIDE_LOCATION, function (BeforeUnhideLocationEvent $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UNHIDE_LOCATION, 10],
            [LocationEvents::BEFORE_UNHIDE_LOCATION, 0],
            [LocationEvents::UNHIDE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnhideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UNHIDE_LOCATION,
            LocationEvents::UNHIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_UNHIDE_LOCATION, function (BeforeUnhideLocationEvent $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UNHIDE_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::UNHIDE_LOCATION, 0],
            [LocationEvents::BEFORE_UNHIDE_LOCATION, 0],
        ]);
    }

    public function testHideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_HIDE_LOCATION,
            LocationEvents::HIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($hiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_HIDE_LOCATION, 0],
            [LocationEvents::HIDE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnHideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_HIDE_LOCATION,
            LocationEvents::HIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_HIDE_LOCATION, function (BeforeHideLocationEvent $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_HIDE_LOCATION, 10],
            [LocationEvents::BEFORE_HIDE_LOCATION, 0],
            [LocationEvents::HIDE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_HIDE_LOCATION,
            LocationEvents::HIDE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_HIDE_LOCATION, function (BeforeHideLocationEvent $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_HIDE_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::HIDE_LOCATION, 0],
            [LocationEvents::BEFORE_HIDE_LOCATION, 0],
        ]);
    }

    public function testSwapLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_SWAP_LOCATION,
            LocationEvents::SWAP_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->swapLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_SWAP_LOCATION, 0],
            [LocationEvents::SWAP_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSwapLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_SWAP_LOCATION,
            LocationEvents::SWAP_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_SWAP_LOCATION, function (BeforeSwapLocationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->swapLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_SWAP_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::SWAP_LOCATION, 0],
            [LocationEvents::BEFORE_SWAP_LOCATION, 0],
        ]);
    }

    public function testMoveSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_MOVE_SUBTREE,
            LocationEvents::MOVE_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->moveSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_MOVE_SUBTREE, 0],
            [LocationEvents::MOVE_SUBTREE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_MOVE_SUBTREE,
            LocationEvents::MOVE_SUBTREE
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_MOVE_SUBTREE, function (BeforeMoveSubtreeEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->moveSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_MOVE_SUBTREE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::MOVE_SUBTREE, 0],
            [LocationEvents::BEFORE_MOVE_SUBTREE, 0],
        ]);
    }

    public function testUpdateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UPDATE_LOCATION,
            LocationEvents::UPDATE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UPDATE_LOCATION, 0],
            [LocationEvents::UPDATE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UPDATE_LOCATION,
            LocationEvents::UPDATE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_UPDATE_LOCATION, function (BeforeUpdateLocationEvent $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UPDATE_LOCATION, 10],
            [LocationEvents::BEFORE_UPDATE_LOCATION, 0],
            [LocationEvents::UPDATE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_UPDATE_LOCATION,
            LocationEvents::UPDATE_LOCATION
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_UPDATE_LOCATION, function (BeforeUpdateLocationEvent $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_UPDATE_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::UPDATE_LOCATION, 0],
            [LocationEvents::BEFORE_UPDATE_LOCATION, 0],
        ]);
    }

    public function testCreateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_CREATE_LOCATION,
            LocationEvents::CREATE_LOCATION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($location, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_CREATE_LOCATION, 0],
            [LocationEvents::CREATE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_CREATE_LOCATION,
            LocationEvents::CREATE_LOCATION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_CREATE_LOCATION, function (BeforeCreateLocationEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_CREATE_LOCATION, 10],
            [LocationEvents::BEFORE_CREATE_LOCATION, 0],
            [LocationEvents::CREATE_LOCATION, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            LocationEvents::BEFORE_CREATE_LOCATION,
            LocationEvents::CREATE_LOCATION
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(LocationEvents::BEFORE_CREATE_LOCATION, function (BeforeCreateLocationEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [LocationEvents::BEFORE_CREATE_LOCATION, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [LocationEvents::CREATE_LOCATION, 0],
            [LocationEvents::BEFORE_CREATE_LOCATION, 0],
        ]);
    }
}
