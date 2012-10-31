<?php
/**
 * File containing the ObjectState Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState;

use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as BaseObjectStateHandler,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * The Object State Handler class provides managing of object states and groups
 */
class Handler implements BaseObjectStateHandler
{
    /**
     * ObjectState Gateway
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected $objectStateGateway;

    /**
     * ObjectState Mapper
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected $objectStateMapper;

    /**
     * Creates a new ObjectState Handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway $objectStateGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper $objectStateMapper
     */
    public function __construct( Gateway $objectStateGateway, Mapper $objectStateMapper )
    {
        $this->objectStateGateway = $objectStateGateway;
        $this->objectStateMapper = $objectStateMapper;
    }

    /**
     * Creates a new object state group
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function createGroup( InputStruct $input )
    {
        $objectStateGroup = $this->objectStateMapper->createObjectStateGroupFromInputStruct( $input );
        $this->objectStateGateway->insertObjectStateGroup( $objectStateGroup );

        return $objectStateGroup;
    }

    /**
     * Loads an object state group
     *
     * @param mixed $groupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function loadGroup( $groupId )
    {
        $data = $this->objectStateGateway->loadObjectStateGroupData( $groupId );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "ObjectStateGroup", $groupId );
        }

        return $this->objectStateMapper->createObjectStateGroupFromData( $data );
    }

    /**
     * Loads a object state group by identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function loadGroupByIdentifier( $identifier )
    {
        $data = $this->objectStateGateway->loadObjectStateGroupDataByIdentifier( $identifier );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "ObjectStateGroup", $identifier );
        }

        return $this->objectStateMapper->createObjectStateGroupFromData( $data );
    }

    /**
     * Loads all object state groups
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[]
     */
    public function loadAllGroups( $offset = 0, $limit = -1 )
    {
        $data = $this->objectStateGateway->loadObjectStateGroupListData( $offset, $limit );
        return $this->objectStateMapper->createObjectStateGroupListFromData( $data );
    }

    /**
     * This method returns the ordered list of object states of a group
     *
     * @param mixed $groupId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState[]
     */
    public function loadObjectStates( $groupId )
    {
        $data = $this->objectStateGateway->loadObjectStateListData( $groupId );
        return $this->objectStateMapper->createObjectStateListFromData( $data );
    }

    /**
     * Updates an object state group
     *
     * @param mixed $groupId
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function updateGroup( $groupId, InputStruct $input )
    {
        $objectStateGroup = $this->objectStateMapper->createObjectStateGroupFromInputStruct( $input );
        $objectStateGroup->id = (int) $groupId;

        $this->objectStateGateway->updateObjectStateGroup( $objectStateGroup );

        return $this->loadGroup( $objectStateGroup->id );
    }

    /**
     * Deletes a object state group including all states and links to content
     *
     * @param mixed $groupId
     */
    public function deleteGroup( $groupId )
    {
        $objectStates = $this->loadObjectStates( $groupId );
        foreach ( $objectStates as $objectState )
        {
            $this->objectStateGateway->deleteObjectStateLinks( $objectState->id );
            $this->objectStateGateway->deleteObjectState( $objectState->id );
        }

        $this->objectStateGateway->deleteObjectStateGroup( $groupId );
    }

    /**
     * Creates a new object state in the given group.
     * The new state gets the last priority.
     * Note: in current kernel: If it is the first state all content objects will
     * set to this state.
     *
     * @param mixed $groupId
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function create( $groupId, InputStruct $input )
    {
        $objectState = $this->objectStateMapper->createObjectStateFromInputStruct( $input );
        $this->objectStateGateway->insertObjectState( $objectState, $groupId );

        return $objectState;
    }

    /**
     * Loads an object state
     *
     * @param mixed $stateId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function load( $stateId )
    {
        $data = $this->objectStateGateway->loadObjectStateData( $stateId );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "ObjectState", $stateId );
        }

        return $this->objectStateMapper->createObjectStateFromData( $data );
    }

    /**
     * Loads an object state by identifier and group it belongs to
     *
     * @param string $identifier
     * @param mixed $groupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function loadByIdentifier( $identifier, $groupId )
    {
        $data = $this->objectStateGateway->loadObjectStateDataByIdentifier( $identifier, $groupId );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "ObjectState", array( 'identifier' => $identifier, 'groupId' => $groupId ) );
        }

        return $this->objectStateMapper->createObjectStateFromData( $data );
    }

    /**
     * Updates an object state
     *
     * @param mixed $stateId
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function update( $stateId, InputStruct $input )
    {
        $objectState = $this->objectStateMapper->createObjectStateFromInputStruct( $input );
        $objectState->id = (int) $stateId;

        $this->objectStateGateway->updateObjectState( $objectState );

        return $this->load( $objectState->id );
    }

    /**
     * Changes the priority of the state
     *
     * @param mixed $stateId
     * @param int $priority
     */
    public function setPriority( $stateId, $priority )
    {
        $objectState = $this->load( $stateId );
        $groupStates = $this->loadObjectStates( $objectState->groupId );

        $priorityList = array();
        foreach ( $groupStates as $index => $groupState )
        {
            $priorityList[$groupState->id] = $index;
        }

        $priorityList[$objectState->id] = (int) $priority;
        asort( $priorityList );

        foreach ( array_keys( $priorityList ) as $objectStatePriority => $objectStateId )
        {
            $this->objectStateGateway->updateObjectStatePriority( $objectStateId, $objectStatePriority );
        }
    }

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If state with $stateId doesn't exist
     *
     * @param mixed $stateId
     */
    public function delete( $stateId )
    {
        // Get the object state first as we need group ID
        // to reorder the priorities and reassign content to another state in the group
        $objectState = $this->load( $stateId );

        $this->objectStateGateway->deleteObjectState( $objectState->id );

        $remainingStates = $this->loadObjectStates( $objectState->groupId );
        if ( empty( $remainingStates ) )
        {
            // If there are no more states in the group, just remove the state links
            $this->objectStateGateway->deleteObjectStateLinks( $objectState->id );
            return;
        }

        $priority = 0;
        foreach ( $remainingStates as $remainingState )
        {
            $this->objectStateGateway->updateObjectStatePriority( $remainingState->id, $priority );
            $priority++;
        }

        $remainingStates = $this->loadObjectStates( $objectState->groupId );
        $this->objectStateGateway->updateObjectStateLinks( $objectState->id, current( $remainingStates )->id );
    }

    /**
     * Sets the object-state of a state group to $stateId for the given content.
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     * @return boolean
     */
    public function setContentState( $contentId, $groupId, $stateId )
    {
        $this->objectStateGateway->setContentState( $contentId, $groupId, $stateId );
        return true;
    }

    /**
     * Gets the object-state of object identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no state is found
     *
     * @param mixed $contentId
     * @param mixed $stateGroupId
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function getContentState( $contentId, $stateGroupId )
    {
        $data = $this->objectStateGateway->loadObjectStateDataForContent( $contentId, $stateGroupId );

        if ( empty( $data ) )
        {
            throw new NotFoundException( "ObjectState", array( "groupId" => $stateGroupId ) );
        }

        return $this->objectStateMapper->createObjectStateFromData( $data );
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param mixed $stateId
     * @return int
     */
    public function getContentCount( $stateId )
    {
        return $this->objectStateGateway->getContentCount( $stateId );
    }
}
