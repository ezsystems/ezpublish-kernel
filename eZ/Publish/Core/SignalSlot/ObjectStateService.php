<?php

/**
 * ObjectStateService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\CreateObjectStateGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\UpdateObjectStateGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\DeleteObjectStateGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\CreateObjectStateSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\UpdateObjectStateSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetPriorityOfObjectStateSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\DeleteObjectStateSignal;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService\SetContentStateSignal;

/**
 * ObjectStateService class.
 */
class ObjectStateService implements ObjectStateServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(ObjectStateServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates a new object state group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function createObjectStateGroup(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct)
    {
        $returnValue = $this->service->createObjectStateGroup($objectStateGroupCreateStruct);
        $this->signalDispatcher->emit(
            new CreateObjectStateGroupSignal(
                [
                    'objectStateGroupId' => $returnValue->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStateGroup($objectStateGroupId, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStateGroup($objectStateGroupId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStateGroups($offset = 0, $limit = -1, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStateGroups($offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStates(ObjectStateGroup $objectStateGroup, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStates($objectStateGroup, $prioritizedLanguages);
    }

    /**
     * Updates an object state group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup(ObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct)
    {
        $returnValue = $this->service->updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateObjectStateGroupSignal(
                [
                    'objectStateGroupId' => $objectStateGroup->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Deletes a object state group including all states and links to content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup)
    {
        $returnValue = $this->service->deleteObjectStateGroup($objectStateGroup);
        $this->signalDispatcher->emit(
            new DeleteObjectStateGroupSignal(
                [
                    'objectStateGroupId' => $objectStateGroup->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Creates a new object state in the given group.
     *
     * Note: in current kernel: If it is the first state all content objects will
     * set to this state.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state with provided identifier already exists in the same group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function createObjectState(ObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct)
    {
        $returnValue = $this->service->createObjectState($objectStateGroup, $objectStateCreateStruct);
        $this->signalDispatcher->emit(
            new CreateObjectStateSignal(
                [
                    'objectStateGroupId' => $objectStateGroup->id,
                    'objectStateId' => $returnValue->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectState($stateId, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectState($stateId, $prioritizedLanguages);
    }

    /**
     * Updates an object state.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state with provided identifier already exists in the same group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function updateObjectState(ObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct)
    {
        $returnValue = $this->service->updateObjectState($objectState, $objectStateUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateObjectStateSignal(
                [
                    'objectStateId' => $objectState->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Changes the priority of the state.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change priority on an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $priority
     */
    public function setPriorityOfObjectState(ObjectState $objectState, $priority)
    {
        $returnValue = $this->service->setPriorityOfObjectState($objectState, $priority);
        $this->signalDispatcher->emit(
            new SetPriorityOfObjectStateSignal(
                [
                    'objectStateId' => $objectState->id,
                    'priority' => $priority,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function deleteObjectState(ObjectState $objectState)
    {
        $returnValue = $this->service->deleteObjectState($objectState);
        $this->signalDispatcher->emit(
            new DeleteObjectStateSignal(
                [
                    'objectStateId' => $objectState->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Sets the object-state of a state group to $state for the given content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state does not belong to the given group
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change the object state
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function setContentState(ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup, ObjectState $objectState)
    {
        $returnValue = $this->service->setContentState($contentInfo, $objectStateGroup, $objectState);
        $this->signalDispatcher->emit(
            new SetContentStateSignal(
                [
                    'contentId' => $contentInfo->id,
                    'objectStateGroupId' => $objectStateGroup->id,
                    'objectStateId' => $objectState->id,
                ]
            )
        );

        return $returnValue;
    }

    /**
     * Gets the object-state of object identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function getContentState(ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup)
    {
        return $this->service->getContentState($contentInfo, $objectStateGroup);
    }

    /**
     * Returns the number of objects which are in this state.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return int
     */
    public function getContentCount(ObjectState $objectState)
    {
        return $this->service->getContentCount($objectState);
    }

    /**
     * Instantiates a new Object State Group Create Struct and sets $identified in it.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct
     */
    public function newObjectStateGroupCreateStruct($identifier)
    {
        return $this->service->newObjectStateGroupCreateStruct($identifier);
    }

    /**
     * Instantiates a new Object State Group Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function newObjectStateGroupUpdateStruct()
    {
        return $this->service->newObjectStateGroupUpdateStruct();
    }

    /**
     * Instantiates a new Object State Create Struct and sets $identifier in it.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public function newObjectStateCreateStruct($identifier)
    {
        return $this->service->newObjectStateCreateStruct($identifier);
    }

    /**
     * Instantiates a new Object State Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct
     */
    public function newObjectStateUpdateStruct()
    {
        return $this->service->newObjectStateUpdateStruct();
    }
}
