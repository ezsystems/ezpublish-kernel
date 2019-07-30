<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateEvent as BeforeCreateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateGroupEvent as BeforeCreateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateEvent as BeforeDeleteObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateGroupEvent as BeforeDeleteObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetContentStateEvent as BeforeSetContentStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetPriorityOfObjectStateEvent as BeforeSetPriorityOfObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateEvent as BeforeUpdateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateGroupEvent as BeforeUpdateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\CreateObjectStateEvent as CreateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\CreateObjectStateGroupEvent as CreateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateEvent as DeleteObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateGroupEvent as DeleteObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent as SetContentStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\SetPriorityOfObjectStateEvent as SetPriorityOfObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\UpdateObjectStateEvent as UpdateObjectStateEventInterface;
use eZ\Publish\API\Repository\Events\ObjectState\UpdateObjectStateGroupEvent as UpdateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\API\Repository\Events\ObjectStateService;

class ObjectStateServiceTest extends AbstractServiceTest
{
    public function testSetContentStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetContentStateEventInterface::class,
            SetContentStateEventInterface::class
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
            [BeforeSetContentStateEventInterface::class, 0],
            [SetContentStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetContentStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetContentStateEventInterface::class,
            SetContentStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSetContentStateEventInterface::class, function (BeforeSetContentStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setContentState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetContentStateEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSetContentStateEventInterface::class, 0],
            [SetContentStateEventInterface::class, 0],
        ]);
    }

    public function testCreateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEventInterface::class,
            CreateObjectStateGroupEventInterface::class
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
            [BeforeCreateObjectStateGroupEventInterface::class, 0],
            [CreateObjectStateGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEventInterface::class,
            CreateObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateGroupEventInterface::class, function (BeforeCreateObjectStateGroupEventInterface $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateGroupEventInterface::class, 10],
            [BeforeCreateObjectStateGroupEventInterface::class, 0],
            [CreateObjectStateGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateGroupEventInterface::class,
            CreateObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateGroupEventInterface::class, function (BeforeCreateObjectStateGroupEventInterface $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateObjectStateGroupEventInterface::class, 0],
            [CreateObjectStateGroupEventInterface::class, 0],
        ]);
    }

    public function testUpdateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEventInterface::class,
            UpdateObjectStateEventInterface::class
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
            [BeforeUpdateObjectStateEventInterface::class, 0],
            [UpdateObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEventInterface::class,
            UpdateObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateEventInterface::class, function (BeforeUpdateObjectStateEventInterface $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateEventInterface::class, 10],
            [BeforeUpdateObjectStateEventInterface::class, 0],
            [UpdateObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateEventInterface::class,
            UpdateObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateEventInterface::class, function (BeforeUpdateObjectStateEventInterface $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateObjectStateEventInterface::class, 0],
            [UpdateObjectStateEventInterface::class, 0],
        ]);
    }

    public function testCreateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEventInterface::class,
            CreateObjectStateEventInterface::class
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
            [BeforeCreateObjectStateEventInterface::class, 0],
            [CreateObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEventInterface::class,
            CreateObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateEventInterface::class, function (BeforeCreateObjectStateEventInterface $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateEventInterface::class, 10],
            [BeforeCreateObjectStateEventInterface::class, 0],
            [CreateObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateObjectStateEventInterface::class,
            CreateObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(BeforeCreateObjectStateEventInterface::class, function (BeforeCreateObjectStateEventInterface $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateObjectStateEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateObjectStateEventInterface::class, 0],
            [CreateObjectStateEventInterface::class, 0],
        ]);
    }

    public function testUpdateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEventInterface::class,
            UpdateObjectStateGroupEventInterface::class
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
            [BeforeUpdateObjectStateGroupEventInterface::class, 0],
            [UpdateObjectStateGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEventInterface::class,
            UpdateObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateGroupEventInterface::class, function (BeforeUpdateObjectStateGroupEventInterface $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateGroupEventInterface::class, 10],
            [BeforeUpdateObjectStateGroupEventInterface::class, 0],
            [UpdateObjectStateGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateObjectStateGroupEventInterface::class,
            UpdateObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateObjectStateGroupEventInterface::class, function (BeforeUpdateObjectStateGroupEventInterface $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateObjectStateGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateObjectStateGroupEventInterface::class, 0],
            [UpdateObjectStateGroupEventInterface::class, 0],
        ]);
    }

    public function testSetPriorityOfObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetPriorityOfObjectStateEventInterface::class,
            SetPriorityOfObjectStateEventInterface::class
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
            [BeforeSetPriorityOfObjectStateEventInterface::class, 0],
            [SetPriorityOfObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetPriorityOfObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeSetPriorityOfObjectStateEventInterface::class,
            SetPriorityOfObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            'random_value_5cff79c31cf609.77890182',
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeSetPriorityOfObjectStateEventInterface::class, function (BeforeSetPriorityOfObjectStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setPriorityOfObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeSetPriorityOfObjectStateEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeSetPriorityOfObjectStateEventInterface::class, 0],
            [SetPriorityOfObjectStateEventInterface::class, 0],
        ]);
    }

    public function testDeleteObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateGroupEventInterface::class,
            DeleteObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateGroupEventInterface::class, 0],
            [DeleteObjectStateGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateGroupEventInterface::class,
            DeleteObjectStateGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteObjectStateGroupEventInterface::class, function (BeforeDeleteObjectStateGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteObjectStateGroupEventInterface::class, 0],
            [DeleteObjectStateGroupEventInterface::class, 0],
        ]);
    }

    public function testDeleteObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateEventInterface::class,
            DeleteObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateEventInterface::class, 0],
            [DeleteObjectStateEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteObjectStateEventInterface::class,
            DeleteObjectStateEventInterface::class
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteObjectStateEventInterface::class, function (BeforeDeleteObjectStateEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteObjectStateEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteObjectStateEventInterface::class, 0],
            [DeleteObjectStateEventInterface::class, 0],
        ]);
    }
}
