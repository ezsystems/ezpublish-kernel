<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\Location\BeforeCopySubtreeEvent as BeforeCopySubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeCreateLocationEvent as BeforeCreateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeDeleteLocationEvent as BeforeDeleteLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeHideLocationEvent as BeforeHideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeMoveSubtreeEvent as BeforeMoveSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeSwapLocationEvent as BeforeSwapLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeUnhideLocationEvent as BeforeUnhideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\BeforeUpdateLocationEvent as BeforeUpdateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\CopySubtreeEvent as CopySubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\CreateLocationEvent as CreateLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent as DeleteLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent as HideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\MoveSubtreeEvent as MoveSubtreeEventInterface;
use eZ\Publish\API\Repository\Events\Location\SwapLocationEvent as SwapLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent as UnhideLocationEventInterface;
use eZ\Publish\API\Repository\Events\Location\UpdateLocationEvent as UpdateLocationEventInterface;
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
            BeforeCopySubtreeEventInterface::class,
            CopySubtreeEventInterface::class
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
            [BeforeCopySubtreeEventInterface::class, 0],
            [CopySubtreeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCopySubtreeResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopySubtreeEventInterface::class,
            CopySubtreeEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCopySubtreeEventInterface::class, function (BeforeCopySubtreeEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopySubtreeEventInterface::class, 10],
            [BeforeCopySubtreeEventInterface::class, 0],
            [CopySubtreeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCopySubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCopySubtreeEventInterface::class,
            CopySubtreeEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('copySubtree')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCopySubtreeEventInterface::class, function (BeforeCopySubtreeEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->copySubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCopySubtreeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCopySubtreeEventInterface::class, 0],
            [CopySubtreeEventInterface::class, 0],
        ]);
    }

    public function testDeleteLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLocationEventInterface::class,
            DeleteLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLocationEventInterface::class, 0],
            [DeleteLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteLocationEventInterface::class,
            DeleteLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteLocationEventInterface::class, function (BeforeDeleteLocationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteLocationEventInterface::class, 0],
            [DeleteLocationEventInterface::class, 0],
        ]);
    }

    public function testUnhideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEventInterface::class,
            UnhideLocationEventInterface::class
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
            [BeforeUnhideLocationEventInterface::class, 0],
            [UnhideLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUnhideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEventInterface::class,
            UnhideLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(BeforeUnhideLocationEventInterface::class, function (BeforeUnhideLocationEventInterface $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUnhideLocationEventInterface::class, 10],
            [BeforeUnhideLocationEventInterface::class, 0],
            [UnhideLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnhideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnhideLocationEventInterface::class,
            UnhideLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $revealedLocation = $this->createMock(Location::class);
        $eventRevealedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('unhideLocation')->willReturn($revealedLocation);

        $traceableEventDispatcher->addListener(BeforeUnhideLocationEventInterface::class, function (BeforeUnhideLocationEventInterface $event) use ($eventRevealedLocation) {
            $event->setRevealedLocation($eventRevealedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->unhideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRevealedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUnhideLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnhideLocationEventInterface::class, 0],
            [UnhideLocationEventInterface::class, 0],
        ]);
    }

    public function testHideLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEventInterface::class,
            HideLocationEventInterface::class
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
            [BeforeHideLocationEventInterface::class, 0],
            [HideLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnHideLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEventInterface::class,
            HideLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(BeforeHideLocationEventInterface::class, function (BeforeHideLocationEventInterface $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeHideLocationEventInterface::class, 10],
            [BeforeHideLocationEventInterface::class, 0],
            [HideLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testHideLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeHideLocationEventInterface::class,
            HideLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
        ];

        $hiddenLocation = $this->createMock(Location::class);
        $eventHiddenLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('hideLocation')->willReturn($hiddenLocation);

        $traceableEventDispatcher->addListener(BeforeHideLocationEventInterface::class, function (BeforeHideLocationEventInterface $event) use ($eventHiddenLocation) {
            $event->setHiddenLocation($eventHiddenLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->hideLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventHiddenLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeHideLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeHideLocationEventInterface::class, 0],
            [HideLocationEventInterface::class, 0],
        ]);
    }

    public function testSwapLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSwapLocationEventInterface::class,
            SwapLocationEventInterface::class
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
            [BeforeSwapLocationEventInterface::class, 0],
            [SwapLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSwapLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSwapLocationEventInterface::class,
            SwapLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSwapLocationEventInterface::class, function (BeforeSwapLocationEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->swapLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSwapLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSwapLocationEventInterface::class, 0],
            [SwapLocationEventInterface::class, 0],
        ]);
    }

    public function testMoveSubtreeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveSubtreeEventInterface::class,
            MoveSubtreeEventInterface::class
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
            [BeforeMoveSubtreeEventInterface::class, 0],
            [MoveSubtreeEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveSubtreeStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveSubtreeEventInterface::class,
            MoveSubtreeEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Location::class),
        ];

        $innerServiceMock = $this->createMock(LocationServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMoveSubtreeEventInterface::class, function (BeforeMoveSubtreeEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $service->moveSubtree(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMoveSubtreeEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMoveSubtreeEventInterface::class, 0],
            [MoveSubtreeEventInterface::class, 0],
        ]);
    }

    public function testUpdateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEventInterface::class,
            UpdateLocationEventInterface::class
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
            [BeforeUpdateLocationEventInterface::class, 0],
            [UpdateLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEventInterface::class,
            UpdateLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(BeforeUpdateLocationEventInterface::class, function (BeforeUpdateLocationEventInterface $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLocationEventInterface::class, 10],
            [BeforeUpdateLocationEventInterface::class, 0],
            [UpdateLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateLocationEventInterface::class,
            UpdateLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(LocationUpdateStruct::class),
        ];

        $updatedLocation = $this->createMock(Location::class);
        $eventUpdatedLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('updateLocation')->willReturn($updatedLocation);

        $traceableEventDispatcher->addListener(BeforeUpdateLocationEventInterface::class, function (BeforeUpdateLocationEventInterface $event) use ($eventUpdatedLocation) {
            $event->setUpdatedLocation($eventUpdatedLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateLocationEventInterface::class, 0],
            [UpdateLocationEventInterface::class, 0],
        ]);
    }

    public function testCreateLocationEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEventInterface::class,
            CreateLocationEventInterface::class
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
            [BeforeCreateLocationEventInterface::class, 0],
            [CreateLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateLocationResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEventInterface::class,
            CreateLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCreateLocationEventInterface::class, function (BeforeCreateLocationEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLocationEventInterface::class, 10],
            [BeforeCreateLocationEventInterface::class, 0],
            [CreateLocationEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateLocationStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateLocationEventInterface::class,
            CreateLocationEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(LocationCreateStruct::class),
        ];

        $location = $this->createMock(Location::class);
        $eventLocation = $this->createMock(Location::class);
        $innerServiceMock = $this->createMock(LocationServiceInterface::class);
        $innerServiceMock->method('createLocation')->willReturn($location);

        $traceableEventDispatcher->addListener(BeforeCreateLocationEventInterface::class, function (BeforeCreateLocationEventInterface $event) use ($eventLocation) {
            $event->setLocation($eventLocation);
            $event->stopPropagation();
        }, 10);

        $service = new LocationService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createLocation(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocation, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateLocationEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateLocationEventInterface::class, 0],
            [CreateLocationEventInterface::class, 0],
        ]);
    }
}
