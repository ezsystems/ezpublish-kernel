<?php
/**
 * File containing the Object State InMemory Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\ObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * The Object State Handler class provides managing of object states and groups
 */
class ObjectStateHandler implements ObjectStateHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
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
        return $this->backend->create( 'Content\\ObjectState\\Group', $this->getInputData( $input ) );
    }

    /**
     * Loads a object state group
     *
     * @param mixed $groupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    public function loadGroup( $groupId )
    {
        return $this->backend->load( 'Content\\ObjectState\\Group', $groupId );
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
        $objectStateGroups = array();
        foreach ( $this->backend->find( 'Content\\ObjectState\\Group', array() ) as $objectStateGroup )
        {
            $objectStateGroups[] = $objectStateGroup;
        }
        return array_slice( $objectStateGroups, $offset, $limit >= 0 ? $limit : null );
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
        $objectStates = $this->backend->find( 'Content\\ObjectState', array( 'groupId' => $groupId ) );

        usort(
            $objectStates,
            function( ObjectState $first, ObjectState $second )
            {
                if ( $first->priority == $second->priority )
                    return 0;

                return $first->priority > $second->priority ? 1 : -1;
            }
        );

        return $objectStates;
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
        $this->backend->update( 'Content\\ObjectState\\Group', $groupId, $this->getInputData( $input ) );
        return $this->loadGroup( $groupId );
    }

    /**
     * Deletes a object state group including all states and links to content
     *
     * @param mixed $groupId
     */
    public function deleteGroup( $groupId )
    {
        $this->backend->deleteByMatch( 'Content\\ObjectState', array( 'groupId' => $groupId ) );
        //@todo object links?
        $this->backend->delete( 'Content\\ObjectState\\Group', $groupId );
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
        $inputData = $this->getInputData( $input );

        $newPriority = 0;
        $objectStates = $this->loadObjectStates( $groupId );
        if ( !empty( $objectStates ) )
        {
            $newPriority = $objectStates[count( $objectStates ) - 1]->priority + 1;
        }

        $inputData["groupId"] = (int) $groupId;
        $inputData["priority"] = $newPriority;

        return $this->backend->create( 'Content\\ObjectState', $inputData );

        // @todo object links?
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
        return $this->backend->load( 'Content\\ObjectState', $stateId );
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
        $this->backend->update( 'Content\\ObjectState', $stateId, $this->getInputData( $input ) );
        return $this->load( $stateId );
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

        $currentPriorityList = array();
        foreach ( $groupStates as $groupState )
        {
            $currentPriorityList[$groupState->id] = $groupState->priority;
        }

        $newPriorityList = $currentPriorityList;
        $newPriorityList[$objectState->id] = (int) $priority;
        asort( $newPriorityList );

        $currentPriorityList = array_keys( $currentPriorityList );
        $newPriorityList = array_keys( $newPriorityList );

        foreach ( $newPriorityList as $priority => $stateId )
        {
            if ( $currentPriorityList[$priority] == $stateId )
                continue;

            $this->backend->update( 'Content\\ObjectState', $stateId, array( "priority" => $priority ) );
        }
    }

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @param mixed $stateId
     */
    public function delete( $stateId )
    {
        $this->backend->delete( 'Content\\ObjectState', $stateId );
        // @todo object links?
    }

    /**
     * Sets the object-state of a state group to $stateId for the given content.
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     * @return boolean
     */
    public function setObjectState( $contentId, $groupId, $stateId )
    {
        //@todo implement
    }

    /**
     * Gets the object-state of object identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroupId
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function getObjectState( $contentId, $stateGroupId )
    {
        //@todo implement
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param mixed $stateId
     * @return int
     */
    public function getContentCount( $stateId )
    {
        //@todo implement
    }

    /**
     * Converts InputStruct to array and adds missing languageCodes array into it
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct $input
     *
     * @return array
     */
    protected function getInputData( InputStruct $input )
    {
        $inputData = (array) $input;
        $inputData["languageCodes"] = array_keys( $input->name );
        return $inputData;
    }
}
