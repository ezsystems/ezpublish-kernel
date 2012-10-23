<?php
/**
 * ObjectStateService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;

/**
 * ObjectStateService class
 * @package eZ\Publish\Core\SignalSlot
 */
class ObjectStateService implements ObjectStateServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\ObjectStateService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( ObjectStateServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates a new object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function createObjectStateGroup( \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreateStruct )
    {
        $returnValue = $this->service->createObjectStateGroup( $objectStateGroupCreateStruct );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\CreateObjectStateGroupSignal( array(
                'objectStateGroupId' => $returnValue->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Loads a object state group
     *
     * @param mixed $objectStateGroupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup( $objectStateGroupId )
    {
        $returnValue = $this->service->loadObjectStateGroup( $objectStateGroupId );
        return $returnValue;
    }

    /**
     * Loads all object state groups
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function loadObjectStateGroups( $offset = 0, $limit = -1 )
    {
        $returnValue = $this->service->loadObjectStateGroups( $offset, $limit );
        return $returnValue;
    }

    /**
     * This method returns the ordered list of object states of a group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function loadObjectStates( \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup )
    {
        $returnValue = $this->service->loadObjectStates( $objectStateGroup );
        return $returnValue;
    }

    /**
     * Updates an object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup( \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup, \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct )
    {
        $returnValue = $this->service->updateObjectStateGroup( $objectStateGroup, $objectStateGroupUpdateStruct );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\UpdateObjectStateGroupSignal( array(
                'objectStateGroupId' => $objectStateGroup->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Deletes a object state group including all states and links to content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function deleteObjectStateGroup( \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup )
    {
        $returnValue = $this->service->deleteObjectStateGroup( $objectStateGroup );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\DeleteObjectStateGroupSignal( array(
                'objectStateGroupId' => $objectStateGroup->id,
            ) )
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
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function createObjectState( \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup, \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct $objectStateCreateStruct )
    {
        $returnValue = $this->service->createObjectState( $objectStateGroup, $objectStateCreateStruct );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\CreateObjectStateSignal( array(
                'objectStateGroupId' => $objectStateGroup->id,
                'objectStateId' => $returnValue->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Loads an object state
     *
     * @param $stateId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function loadObjectState( $stateId )
    {
        $returnValue = $this->service->loadObjectState( $stateId );
        return $returnValue;
    }

    /**
     * Updates an object state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function updateObjectState( \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState, \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct )
    {
        $returnValue = $this->service->updateObjectState( $objectState, $objectStateUpdateStruct );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\UpdateObjectStateSignal( array(
                'objectStateId' => $objectState->id,
            ) )
        );
        return $returnValue;
    }

    /**
     * Changes the priority of the state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change priority on an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $priority
     */
    public function setPriorityOfObjectState( \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState, $priority )
    {
        $returnValue = $this->service->setPriorityOfObjectState( $objectState, $priority );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\SetPriorityOfObjectStateSignal( array(
                'objectStateId' => $objectState->id,
                'priority' => $priority,
            ) )
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
    public function deleteObjectState( \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState )
    {
        $returnValue = $this->service->deleteObjectState( $objectState );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\DeleteObjectStateSignal( array(
                'objectStateId' => $objectState->id,
            ) )
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
    public function setContentState( \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup, \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState )
    {
        $returnValue = $this->service->setContentState( $contentInfo, $objectStateGroup, $objectState );
        $this->signalDispatcher->emit(
            new Signal\ObjectStateService\SetContentStateSignal( array(
                'contentId' => $contentInfo->id,
                'objectStateGroupId' => $objectStateGroup->id,
                'objectStateId' => $objectState->id,
            ) )
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
    public function getObjectState( \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup )
    {
        $returnValue = $this->service->getObjectState( $contentInfo, $objectStateGroup );
        return $returnValue;
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return int
     */
    public function getContentCount( \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState )
    {
        $returnValue = $this->service->getContentCount( $objectState );
        return $returnValue;
    }

    /**
     * Instantiates a new Object State Group Create Struct and sets $identified in it.
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct
     */
    public function newObjectStateGroupCreateStruct( $identifier )
    {
        $returnValue = $this->service->newObjectStateGroupCreateStruct( $identifier );
        return $returnValue;
    }

    /**
     * Instantiates a new Object State Group Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function newObjectStateGroupUpdateStruct()
    {
        $returnValue = $this->service->newObjectStateGroupUpdateStruct();
        return $returnValue;
    }

    /**
     * Instantiates a new Object State Create Struct and sets $identifier in it.
     *
     * @param string $identifier
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public function newObjectStateCreateStruct( $identifier )
    {
        $returnValue = $this->service->newObjectStateCreateStruct( $identifier );
        return $returnValue;
    }

    /**
     * Instantiates a new Object State Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct
     */
    public function newObjectStateUpdateStruct()
    {
        $returnValue = $this->service->newObjectStateUpdateStruct();
        return $returnValue;
    }

}

