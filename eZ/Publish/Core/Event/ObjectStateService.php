<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\ObjectStateServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
use eZ\Publish\Core\Event\ObjectState\ObjectStateEvents;
use eZ\Publish\Core\Event\ObjectState\SetContentStateEvent;
use eZ\Publish\Core\Event\ObjectState\SetPriorityOfObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\UpdateObjectStateEvent;
use eZ\Publish\Core\Event\ObjectState\UpdateObjectStateGroupEvent;

class ObjectStateService extends ObjectStateServiceDecorator implements ObjectStateServiceInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        ObjectStateServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createObjectStateGroup(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct): ObjectStateGroup
    {
        $eventData = [$objectStateGroupCreateStruct];

        $beforeEvent = new BeforeCreateObjectStateGroupEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getObjectStateGroup();
        }

        $objectStateGroup = $beforeEvent->hasObjectStateGroup()
            ? $beforeEvent->getObjectStateGroup()
            : parent::createObjectStateGroup($objectStateGroupCreateStruct);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::CREATE_OBJECT_STATE_GROUP,
            new CreateObjectStateGroupEvent($objectStateGroup, ...$eventData)
        );

        return $objectStateGroup;
    }

    public function updateObjectStateGroup(
        ObjectStateGroup $objectStateGroup,
        ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
    ): ObjectStateGroup {
        $eventData = [
            $objectStateGroup,
            $objectStateGroupUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateObjectStateGroupEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUpdatedObjectStateGroup();
        }

        $updatedObjectStateGroup = $beforeEvent->hasUpdatedObjectStateGroup()
            ? $beforeEvent->getUpdatedObjectStateGroup()
            : parent::updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::UPDATE_OBJECT_STATE_GROUP,
            new UpdateObjectStateGroupEvent($updatedObjectStateGroup, ...$eventData)
        );

        return $updatedObjectStateGroup;
    }

    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup): void
    {
        $eventData = [$objectStateGroup];

        $beforeEvent = new BeforeDeleteObjectStateGroupEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteObjectStateGroup($objectStateGroup);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::DELETE_OBJECT_STATE_GROUP,
            new DeleteObjectStateGroupEvent(...$eventData)
        );
    }

    public function createObjectState(
        ObjectStateGroup $objectStateGroup,
        ObjectStateCreateStruct $objectStateCreateStruct
    ): ObjectState {
        $eventData = [
            $objectStateGroup,
            $objectStateCreateStruct,
        ];

        $beforeEvent = new BeforeCreateObjectStateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_CREATE_OBJECT_STATE, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getObjectState();
        }

        $objectState = $beforeEvent->hasObjectState()
            ? $beforeEvent->getObjectState()
            : parent::createObjectState($objectStateGroup, $objectStateCreateStruct);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::CREATE_OBJECT_STATE,
            new CreateObjectStateEvent($objectState, ...$eventData)
        );

        return $objectState;
    }

    public function updateObjectState(
        ObjectState $objectState,
        ObjectStateUpdateStruct $objectStateUpdateStruct
    ): ObjectState {
        $eventData = [
            $objectState,
            $objectStateUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateObjectStateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_UPDATE_OBJECT_STATE, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getUpdatedObjectState();
        }

        $updatedObjectState = $beforeEvent->hasUpdatedObjectState()
            ? $beforeEvent->getUpdatedObjectState()
            : parent::updateObjectState($objectState, $objectStateUpdateStruct);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::UPDATE_OBJECT_STATE,
            new UpdateObjectStateEvent($updatedObjectState, ...$eventData)
        );

        return $updatedObjectState;
    }

    public function setPriorityOfObjectState(
        ObjectState $objectState,
        $priority
    ): void {
        $eventData = [
            $objectState,
            $priority,
        ];

        $beforeEvent = new BeforeSetPriorityOfObjectStateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_SET_PRIORITY_OF_OBJECT_STATE, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::setPriorityOfObjectState($objectState, $priority);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::SET_PRIORITY_OF_OBJECT_STATE,
            new SetPriorityOfObjectStateEvent(...$eventData)
        );
    }

    public function deleteObjectState(ObjectState $objectState): void
    {
        $eventData = [$objectState];

        $beforeEvent = new BeforeDeleteObjectStateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_DELETE_OBJECT_STATE, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteObjectState($objectState);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::DELETE_OBJECT_STATE,
            new DeleteObjectStateEvent(...$eventData)
        );
    }

    public function setContentState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup,
        ObjectState $objectState
    ): void {
        $eventData = [
            $contentInfo,
            $objectStateGroup,
            $objectState,
        ];

        $beforeEvent = new BeforeSetContentStateEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ObjectStateEvents::BEFORE_SET_CONTENT_STATE, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::setContentState($contentInfo, $objectStateGroup, $objectState);

        $this->eventDispatcher->dispatch(
            ObjectStateEvents::SET_CONTENT_STATE,
            new SetContentStateEvent(...$eventData)
        );
    }
}
