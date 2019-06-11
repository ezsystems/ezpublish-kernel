<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Event\ObjectStateService;
use eZ\Publish\Core\Event\ObjectState\BeforeCreateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeCreateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeDeleteObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeDeleteObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeSetContentStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeSetPriorityOfObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeUpdateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\BeforeUpdateObjectStateGroupEvent;
use eZ\Publish\Core\Event\ObjectState\ObjectStateEvents;

class ObjectStateServiceTest extends AbstractServiceTest
{
    public function testSetContentStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_SET_CONTENT_STATE,
            ObjectStateEvents::SET_CONTENT_STATE
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
            [ObjectStateEvents::BEFORE_SET_CONTENT_STATE, 0],
            [ObjectStateEvents::SET_CONTENT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetContentStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_SET_CONTENT_STATE,
            ObjectStateEvents::SET_CONTENT_STATE
        );

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_SET_CONTENT_STATE, function (BeforeSetContentStateEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setContentState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_SET_CONTENT_STATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::SET_CONTENT_STATE, 0],
            [ObjectStateEvents::BEFORE_SET_CONTENT_STATE, 0],
        ]);
    }

    public function testCreateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::CREATE_OBJECT_STATE_GROUP
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
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::CREATE_OBJECT_STATE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::CREATE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, function (BeforeCreateObjectStateGroupEvent $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, 10],
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::CREATE_OBJECT_STATE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::CREATE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroupCreateStruct::class),
        ];

        $objectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectStateGroup')->willReturn($objectStateGroup);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, function (BeforeCreateObjectStateGroupEvent $event) use ($eventObjectStateGroup) {
            $event->setObjectStateGroup($eventObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::CREATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, 0],
        ]);
    }

    public function testUpdateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE,
            ObjectStateEvents::UPDATE_OBJECT_STATE
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
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, 0],
            [ObjectStateEvents::UPDATE_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE,
            ObjectStateEvents::UPDATE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, function (BeforeUpdateObjectStateEvent $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, 10],
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, 0],
            [ObjectStateEvents::UPDATE_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE,
            ObjectStateEvents::UPDATE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $updatedObjectState = $this->createMock(ObjectState::class);
        $eventUpdatedObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectState')->willReturn($updatedObjectState);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, function (BeforeUpdateObjectStateEvent $event) use ($eventUpdatedObjectState) {
            $event->setUpdatedObjectState($eventUpdatedObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectState, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::UPDATE_OBJECT_STATE, 0],
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, 0],
        ]);
    }

    public function testCreateObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE,
            ObjectStateEvents::CREATE_OBJECT_STATE
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
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, 0],
            [ObjectStateEvents::CREATE_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateObjectStateResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE,
            ObjectStateEvents::CREATE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, function (BeforeCreateObjectStateEvent $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, 10],
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, 0],
            [ObjectStateEvents::CREATE_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE,
            ObjectStateEvents::CREATE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $objectState = $this->createMock(ObjectState::class);
        $eventObjectState = $this->createMock(ObjectState::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('createObjectState')->willReturn($objectState);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, function (BeforeCreateObjectStateEvent $event) use ($eventObjectState) {
            $event->setObjectState($eventObjectState);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventObjectState, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::CREATE_OBJECT_STATE, 0],
            [ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, 0],
        ]);
    }

    public function testUpdateObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP
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
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateObjectStateGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, function (BeforeUpdateObjectStateGroupEvent $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, 10],
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP,
            ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $updatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $eventUpdatedObjectStateGroup = $this->createMock(ObjectStateGroup::class);
        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);
        $innerServiceMock->method('updateObjectStateGroup')->willReturn($updatedObjectStateGroup);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, function (BeforeUpdateObjectStateGroupEvent $event) use ($eventUpdatedObjectStateGroup) {
            $event->setUpdatedObjectStateGroup($eventUpdatedObjectStateGroup);
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedObjectStateGroup, $result);
        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, 0],
        ]);
    }

    public function testSetPriorityOfObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE,
            ObjectStateEvents::SET_PRIORITY_OF_OBJECT_STATE
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
            [ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE, 0],
            [ObjectStateEvents::SET_PRIORITY_OF_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testSetPriorityOfObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE,
            ObjectStateEvents::SET_PRIORITY_OF_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectState::class),
            'random_value_5cff79c31cf609.77890182',
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE, function (BeforeSetPriorityOfObjectStateEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->setPriorityOfObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::SET_PRIORITY_OF_OBJECT_STATE, 0],
            [ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE, 0],
        ]);
    }

    public function testDeleteObjectStateGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP,
            ObjectStateEvents::DELETE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::DELETE_OBJECT_STATE_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP,
            ObjectStateEvents::DELETE_OBJECT_STATE_GROUP
        );

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP, function (BeforeDeleteObjectStateGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectStateGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::DELETE_OBJECT_STATE_GROUP, 0],
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP, 0],
        ]);
    }

    public function testDeleteObjectStateEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE,
            ObjectStateEvents::DELETE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE, 0],
            [ObjectStateEvents::DELETE_OBJECT_STATE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteObjectStateStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE,
            ObjectStateEvents::DELETE_OBJECT_STATE
        );

        $parameters = [
            $this->createMock(ObjectState::class),
        ];

        $innerServiceMock = $this->createMock(ObjectStateServiceInterface::class);

        $traceableEventDispatcher->addListener(ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE, function (BeforeDeleteObjectStateEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new ObjectStateService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteObjectState(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [ObjectStateEvents::DELETE_OBJECT_STATE, 0],
            [ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE, 0],
        ]);
    }
}
