<?php
/**
 * File containing the Object State InMemory Handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as ObjectStateHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

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
        $objectStateGroups = $this->backend->find( 'Content\\ObjectState\\Group', array( 'identifier' => $identifier ) );
        if ( empty( $objectStateGroups ) )
        {
            throw new NotFound( "Content\\ObjectState\\Group", array( "identifier" => $identifier ) );
        }

        return reset( $objectStateGroups );
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
            function ( ObjectState $first, ObjectState $second )
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

        $inputData["groupId"] = (int)$groupId;
        $inputData["priority"] = $newPriority;

        $createdState = $this->backend->create( 'Content\\ObjectState', $inputData );

        if ( $newPriority == 0 )
        {
            $allContentInfos = $this->backend->find( "Content\\ContentInfo" );
            $allContentIds = array_map(
                function ( ContentInfo $contentInfo )
                {
                    return $contentInfo->id;
                },
                $allContentInfos
            );

            $this->backend->update( "Content\\ObjectState", $createdState->id, array( "_contentId" => $allContentIds ) );
        }

        return $createdState;
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
        $objectStates = $this->backend->find(
            'Content\\ObjectState',
            array(
                'identifier' => $identifier,
                'groupId' => $groupId
            )
        );

        if ( empty( $objectStates ) )
        {
            throw new NotFound( "Content\\ObjectState", array( "identifier" => $identifier, "groupId" => $groupId ) );
        }

        return reset( $objectStates );
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

        $priorityList = array();
        foreach ( $groupStates as $index => $groupState )
        {
            $priorityList[$groupState->id] = $index;
        }

        $priorityList[$objectState->id] = (int)$priority;
        asort( $priorityList );

        foreach ( array_keys( $priorityList ) as $objectStatePriority => $objectStateId )
        {
            $this->backend->update( 'Content\\ObjectState', $objectStateId, array( "priority" => $objectStatePriority ) );
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
        // We need to load the object state as we need $groupId
        $objectState = $this->load( $stateId );

        // Find all content for the current $stateId
        $contentList = $this->getObjectStateContentList( $stateId );

        // Delete the state
        $this->backend->delete( 'Content\\ObjectState', $stateId );

        // Update the priorities of the group states if there are any more states in the group
        $groupStates = $this->loadObjectStates( $objectState->groupId );
        if ( empty( $groupStates ) )
            return;

        $priority = 0;
        foreach ( $groupStates as $groupState )
        {
            $this->backend->update( 'Content\\ObjectState', $groupState->id, array( "priority" => $priority ) );
            $priority++;
        }

        // Now reassign content from old state to the first state in the group
        $firstObjectState = current( $this->backend->find( "Content\\ObjectState", array( "priority" => 0 ) ) );

        $existingContent = $this->getObjectStateContentList( $firstObjectState->id ) + $contentList;
        $existingContent = array_values( array_unique( $existingContent ) );

        $this->backend->update( 'Content\\ObjectState', $firstObjectState->id, array( "_contentId" => $existingContent ) );
    }

    /**
     * Sets the object-state of a state group to $stateId for the given content.
     *
     * @param mixed $contentId
     * @param mixed $groupId
     * @param mixed $stateId
     *
     * @return boolean
     */
    public function setContentState( $contentId, $groupId, $stateId )
    {
        $groupStateIds = $this->getGroupStateList( $groupId );
        if ( empty( $groupStateIds ) || !in_array( $stateId, $groupStateIds ) )
            return false;

        $contentToStateMap = $this->getContentToStateMap();

        // If the content was in one of the group states,
        // find all content for the old state and update the old state with excluded $contentId
        $existingStateIds = array_values( array_intersect( $groupStateIds, $contentToStateMap[(int)$contentId] ) );
        if ( !empty( $existingStateIds ) )
        {
            $oldStateContentList = $this->getObjectStateContentList( $existingStateIds[0] );
            $oldStateContentList = array_values( array_diff( $oldStateContentList, array( $contentId ) ) );

            $this->backend->update( "Content\\ObjectState", $existingStateIds[0], array( "_contentId" => $oldStateContentList ) );
        }

        // Find all content for the new state and update the new state with added $contentId
        $newStateContentList = $this->getObjectStateContentList( $stateId );
        $newStateContentList[] = $contentId;

        $this->backend->update( "Content\\ObjectState", $stateId, array( "_contentId" => $newStateContentList ) );

        return true;
    }

    /**
     * Gets the object-state of object identified by $contentId.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if no state is found
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    public function getContentState( $contentId, $stateGroupId )
    {
        $groupStateIds = $this->getGroupStateList( $stateGroupId );
        if ( empty( $groupStateIds ) )
            throw new NotFound( "Content\\ObjectState", array( "groupId" => $stateGroupId ) );

        $contentId = (int)$contentId;

        $contentToStateMap = $this->getContentToStateMap();
        if ( !isset( $contentToStateMap[$contentId] ) )
            throw new NotFound( "Content\\ObjectState", array( "groupId" => $stateGroupId ) );

        $foundStates = array_values( array_intersect( $groupStateIds, $contentToStateMap[$contentId] ) );
        if ( empty( $foundStates ) )
            throw new NotFound( "Content\\ObjectState", array( "groupId" => $stateGroupId ) );

        return $this->load( $foundStates[0] );
    }

    /**
     * Returns the number of objects which are in this state
     *
     * @param mixed $stateId
     *
     * @return int
     */
    public function getContentCount( $stateId )
    {
        return count( $this->getObjectStateContentList( $stateId ) );
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
        $inputData = (array)$input;
        $inputData["languageCodes"] = array_keys( $input->name );
        return $inputData;
    }

    /**
     * Gets a mapping array of all content and states they belong to
     *
     * This method serves as a hack because InMemory storage is unable to
     * store M:N relations between content and object states as there's no
     * value object for the link
     *
     * @return array
     */
    protected function getContentToStateMap()
    {
        $contentToStateMap = array();

        $contentInfoArray = $this->backend->find( "Content\\ContentInfo" );
        foreach ( $contentInfoArray as $contentInfo )
        {
            $objectStates = $this->backend->find( "Content\\ObjectState", array( "_contentId" => $contentInfo->id ) );
            foreach ( $objectStates as $objectState )
            {
                $contentToStateMap[$contentInfo->id][] = $objectState->id;
            }
        }

        return $contentToStateMap;
    }

    /**
     * Returns all content IDs that belong to $stateId
     *
     * @param int $stateId
     *
     * @return array
     */
    protected function getObjectStateContentList( $stateId )
    {
        $contentList = array();
        foreach ( $this->getContentToStateMap() as $contentId => $stateList )
        {
            if ( in_array( $stateId, $stateList ) )
                $contentList[] = $contentId;
        }

        return $contentList;
    }

    /**
     * Returns all state IDs that belong to $groupId
     *
     * @param int $groupId
     *
     * @return array
     */
    protected function getGroupStateList( $groupId )
    {
        $groupStates = $this->loadObjectStates( $groupId );
        return array_map(
            function ( ObjectState $objectState )
            {
                return $objectState->id;
            },
            $groupStates
        );
    }
}
