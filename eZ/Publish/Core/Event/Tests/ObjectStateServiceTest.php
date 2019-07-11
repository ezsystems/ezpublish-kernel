<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateEvent as BeforeCreateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateGroupEvent as BeforeCreateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateEvent as BeforeDeleteObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateGroupEvent as BeforeDeleteObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetContentStateEvent as BeforeSetContentStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetPriorityOfObjectStateEvent as BeforeSetPriorityOfObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateEvent as BeforeUpdateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateGroupEvent as BeforeUpdateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Event\ObjectState\BeforeCreateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeCreateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeDeleteObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeDeleteObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeSetContentStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeSetPriorityOfObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeUpdateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeUpdateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\CreateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\CreateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\DeleteObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\DeleteObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\SetContentStateEvent;
use eZ\Publish\Core\Event\ObjectState\SetPriorityOfObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\UpdateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\UpdateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectStateService;

class ObjectStateServiceTest extends AbstractServiceTest
{
    public function testSetContentStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetContentStateEvent::class,
            SetContentStateEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setContentState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetContentStateEvent::class, 0],
            [SetContentStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetContentStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetContentStateEvent::class,
            SetContentStateEvent::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSetContentStateEvent::class, function (BeforeSetContentStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setContentState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetContentStateEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSetContentStateEvent::class, 0],
            [SetContentStateEvent::class, 0],
        ]);
    }

    public function testCreateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEvent::class,
            CreateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($objectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateGroupEvent::class, 0],
            [CreateObjectStateGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEvent::class,
            CreateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateGroupEvent::class, function (BeforeCreateObjectStateGroupEventInterface $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateGroupEvent::class, 10],
            [BeforeCreateObjectStateGroupEvent::class, 0],
            [CreateObjectStateGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEvent::class,
            CreateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateGroupEvent::class, function (BeforeCreateObjectStateGroupEventInterface $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateObjectStateGroupEvent::class, 0],
            [CreateObjectStateGroupEvent::class, 0],
        ]);
    }

    public function testUpdateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEvent::class,
            UpdateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateEvent::class, 0],
            [UpdateObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEvent::class,
            UpdateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateEvent::class, function (BeforeUpdateObjectStateEventInterface $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateEvent::class, 10],
            [BeforeUpdateObjectStateEvent::class, 0],
            [UpdateObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEvent::class,
            UpdateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateEvent::class, function (BeforeUpdateObjectStateEventInterface $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateObjectStateEvent::class, 0],
            [UpdateObjectStateEvent::class, 0],
        ]);
    }

    public function testCreateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEvent::class,
            CreateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($objectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateEvent::class, 0],
            [CreateObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEvent::class,
            CreateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateEvent::class, function (BeforeCreateObjectStateEventInterface $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateEvent::class, 10],
            [BeforeCreateObjectStateEvent::class, 0],
            [CreateObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEvent::class,
            CreateObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateEvent::class, function (BeforeCreateObjectStateEventInterface $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateObjectStateEvent::class, 0],
            [CreateObjectStateEvent::class, 0],
        ]);
    }

    public function testUpdateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEvent::class,
            UpdateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateGroupEvent::class, 0],
            [UpdateObjectStateGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEvent::class,
            UpdateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateGroupEvent::class, function (BeforeUpdateObjectStateGroupEventInterface $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateGroupEvent::class, 10],
            [BeforeUpdateObjectStateGroupEvent::class, 0],
            [UpdateObjectStateGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEvent::class,
            UpdateObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateGroupEvent::class, function (BeforeUpdateObjectStateGroupEventInterface $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateObjectStateGroupEvent::class, 0],
            [UpdateObjectStateGroupEvent::class, 0],
        ]);
    }

    public function testSetPriorityOfObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetPriorityOfObjectStateEvent::class,
            SetPriorityOfObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            'random_value_5cff79c31cf588.18908758',
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setPriorityOfObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetPriorityOfObjectStateEvent::class, 0],
            [SetPriorityOfObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetPriorityOfObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetPriorityOfObjectStateEvent::class,
            SetPriorityOfObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            'random_value_5cff79c31cf609.77890182',
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSetPriorityOfObjectStateEvent::class, function (BeforeSetPriorityOfObjectStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setPriorityOfObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetPriorityOfObjectStateEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSetPriorityOfObjectStateEvent::class, 0],
            [SetPriorityOfObjectStateEvent::class, 0],
        ]);
    }

    public function testDeleteObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateGroupEvent::class,
            DeleteObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateGroupEvent::class, 0],
            [DeleteObjectStateGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateGroupEvent::class,
            DeleteObjectStateGroupEvent::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteObjectStateGroupEvent::class, function (BeforeDeleteObjectStateGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteObjectStateGroupEvent::class, 0],
            [DeleteObjectStateGroupEvent::class, 0],
        ]);
    }

    public function testDeleteObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateEvent::class,
            DeleteObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateEvent::class, 0],
            [DeleteObjectStateEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateEvent::class,
            DeleteObjectStateEvent::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteObjectStateEvent::class, function (BeforeDeleteObjectStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteObjectStateEvent::class, 0],
            [DeleteObjectStateEvent::class, 0],
        ]);
    }
}
