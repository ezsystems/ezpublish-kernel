<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\Location\BeforeCopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeCreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeDeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeHideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeMoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeSwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeUnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\BeforeUpdateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\Core\Event\LocationService;

class LocationServiceTest extends AbstractServiceTest
{
    public function testCopySubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopySubtreeEvent::class,
            CopySubtreeEvent::class
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
            [BeforeCopySubtreeEvent::class, 0],
            [CopySubtreeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopySubtreeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopySubtreeEvent::class,
            CopySubtreeEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCopySubtreeEvent::class, function (BeforeCopySubtreeEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopySubtreeEvent::class, 10],
            [BeforeCopySubtreeEvent::class, 0],
            [CopySubtreeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopySubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopySubtreeEvent::class,
            CopySubtreeEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCopySubtreeEvent::class, function (BeforeCopySubtreeEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopySubtreeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopySubtreeEvent::class, 0],
            [CopySubtreeEvent::class, 0],
        ]);
    }

    public function testDeleteLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLocationEvent::class,
            DeleteLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLocationEvent::class, 0],
            [DeleteLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLocationEvent::class,
            DeleteLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteLocationEvent::class, function (BeforeDeleteLocationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteLocationEvent::class, 0],
            [DeleteLocationEvent::class, 0],
        ]);
    }

    public function testUnhideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEvent::class,
            UnhideLocationEvent::class
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
            [BeforeUnhideLocationEvent::class, 0],
            [UnhideLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUnhideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEvent::class,
            UnhideLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(BeforeUnhideLocationEvent::class, function (BeforeUnhideLocationEvent $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUnhideLocationEvent::class, 10],
            [BeforeUnhideLocationEvent::class, 0],
            [UnhideLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnhideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEvent::class,
            UnhideLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(BeforeUnhideLocationEvent::class, function (BeforeUnhideLocationEvent $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUnhideLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnhideLocationEvent::class, 0],
            [UnhideLocationEvent::class, 0],
        ]);
    }

    public function testHideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEvent::class,
            HideLocationEvent::class
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
            [BeforeHideLocationEvent::class, 0],
            [HideLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnHideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEvent::class,
            HideLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(BeforeHideLocationEvent::class, function (BeforeHideLocationEvent $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeHideLocationEvent::class, 10],
            [BeforeHideLocationEvent::class, 0],
            [HideLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEvent::class,
            HideLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(BeforeHideLocationEvent::class, function (BeforeHideLocationEvent $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeHideLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeHideLocationEvent::class, 0],
            [HideLocationEvent::class, 0],
        ]);
    }

    public function testSwapLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSwapLocationEvent::class,
            SwapLocationEvent::class
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
            [BeforeSwapLocationEvent::class, 0],
            [SwapLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSwapLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSwapLocationEvent::class,
            SwapLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSwapLocationEvent::class, function (BeforeSwapLocationEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->swapLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSwapLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSwapLocationEvent::class, 0],
            [SwapLocationEvent::class, 0],
        ]);
    }

    public function testMoveSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveSubtreeEvent::class,
            MoveSubtreeEvent::class
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
            [BeforeMoveSubtreeEvent::class, 0],
            [MoveSubtreeEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveSubtreeEvent::class,
            MoveSubtreeEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMoveSubtreeEvent::class, function (BeforeMoveSubtreeEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->moveSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMoveSubtreeEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMoveSubtreeEvent::class, 0],
            [MoveSubtreeEvent::class, 0],
        ]);
    }

    public function testUpdateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEvent::class,
            UpdateLocationEvent::class
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
            [BeforeUpdateLocationEvent::class, 0],
            [UpdateLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEvent::class,
            UpdateLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(BeforeUpdateLocationEvent::class, function (BeforeUpdateLocationEvent $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLocationEvent::class, 10],
            [BeforeUpdateLocationEvent::class, 0],
            [UpdateLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEvent::class,
            UpdateLocationEvent::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(BeforeUpdateLocationEvent::class, function (BeforeUpdateLocationEvent $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateLocationEvent::class, 0],
            [UpdateLocationEvent::class, 0],
        ]);
    }

    public function testCreateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEvent::class,
            CreateLocationEvent::class
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
            [BeforeCreateLocationEvent::class, 0],
            [CreateLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEvent::class,
            CreateLocationEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCreateLocationEvent::class, function (BeforeCreateLocationEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLocationEvent::class, 10],
            [BeforeCreateLocationEvent::class, 0],
            [CreateLocationEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEvent::class,
            CreateLocationEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCreateLocationEvent::class, function (BeforeCreateLocationEvent $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLocationEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateLocationEvent::class, 0],
            [CreateLocationEvent::class, 0],
        ]);
    }
}
