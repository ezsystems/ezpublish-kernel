<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

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
use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateGroupEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeDeleteObjectStateGroupEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetContentStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeSetPriorityOfObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\BeforeUpdateObjectStateGroupEvent;
use eZ\Publish\API\Repository\Events\ObjectState\CreateObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\CreateObjectStateGroupEvent;
use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\DeleteObjectStateGroupEvent;
use eZ\Publish\API\Repository\Events\ObjectState\SetContentStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\SetPriorityOfObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\UpdateObjectStateEvent;
use eZ\Publish\API\Repository\Events\ObjectState\UpdateObjectStateGroupEvent;
use eZ\Publish\SPI\Repository\Decorator\ObjectStateServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ObjectStateService extends ObjectStateServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateObjectStateGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getObjectStateGroup();
        }

        $objectStateGroup = $beforeEvent->hasObjectStateGroup()
            ? $beforeEvent->getObjectStateGroup()
            : $this->innerService->createObjectStateGroup($objectStateGroupCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateObjectStateGroupEvent($objectStateGroup, ...$eventData),
            CreateObjectStateGroupEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateObjectStateGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedObjectStateGroup();
        }

        $updatedObjectStateGroup = $beforeEvent->hasUpdatedObjectStateGroup()
            ? $beforeEvent->getUpdatedObjectStateGroup()
            : $this->innerService->updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateObjectStateGroupEvent($updatedObjectStateGroup, ...$eventData),
            UpdateObjectStateGroupEventInterface::class
        );

        return $updatedObjectStateGroup;
    }

    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup): void
    {
        $eventData = [$objectStateGroup];

        $beforeEvent = new BeforeDeleteObjectStateGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteObjectStateGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteObjectStateGroup($objectStateGroup);

        $this->eventDispatcher->dispatch(
            new DeleteObjectStateGroupEvent(...$eventData),
            DeleteObjectStateGroupEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateObjectStateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getObjectState();
        }

        $objectState = $beforeEvent->hasObjectState()
            ? $beforeEvent->getObjectState()
            : $this->innerService->createObjectState($objectStateGroup, $objectStateCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateObjectStateEvent($objectState, ...$eventData),
            CreateObjectStateEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateObjectStateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedObjectState();
        }

        $updatedObjectState = $beforeEvent->hasUpdatedObjectState()
            ? $beforeEvent->getUpdatedObjectState()
            : $this->innerService->updateObjectState($objectState, $objectStateUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateObjectStateEvent($updatedObjectState, ...$eventData),
            UpdateObjectStateEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeSetPriorityOfObjectStateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->setPriorityOfObjectState($objectState, $priority);

        $this->eventDispatcher->dispatch(
            new SetPriorityOfObjectStateEvent(...$eventData),
            SetPriorityOfObjectStateEventInterface::class
        );
    }

    public function deleteObjectState(ObjectState $objectState): void
    {
        $eventData = [$objectState];

        $beforeEvent = new BeforeDeleteObjectStateEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteObjectStateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteObjectState($objectState);

        $this->eventDispatcher->dispatch(
            new DeleteObjectStateEvent(...$eventData),
            DeleteObjectStateEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeSetContentStateEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->setContentState($contentInfo, $objectStateGroup, $objectState);

        $this->eventDispatcher->dispatch(
            new SetContentStateEvent(...$eventData),
            SetContentStateEventInterface::class
        );
    }
}
