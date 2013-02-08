<?php
/**
 * File containing the eZ\Publish\API\Repository\ObjectStateService interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\API\Repository
 */

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;

/**
 * ObjectStateService service
 *
 * @example Examples/objectstates.php tbd.
 *
 * @package eZ\Publish\API\Repository
 */
interface ObjectStateService
{
    /**
     * Creates a new object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct $objectStateGroupCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function createObjectStateGroup( ObjectStateGroupCreateStruct $objectStateGroupCreateStruct );

    /**
     * Loads a object state group
     *
     * @param mixed $objectStateGroupId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the group was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function loadObjectStateGroup( $objectStateGroupId );

    /**
     * Loads all object state groups
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup[]
     */
    public function loadObjectStateGroups( $offset = 0, $limit = -1 );

    /**
     * This method returns the ordered list of object states of a group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState[]
     */
    public function loadObjectStates( ObjectStateGroup $objectStateGroup );

    /**
     * Updates an object state group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state group with provided identifier already exists
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    public function updateObjectStateGroup( ObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct );

    /**
     * Deletes a object state group including all states and links to content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     */
    public function deleteObjectStateGroup( ObjectStateGroup $objectStateGroup );

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
    public function createObjectState( ObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct );

    /**
     * Loads an object state
     *
     * @param mixed $stateId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the state was not found
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function loadObjectState( $stateId );

    /**
     * Updates an object state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update an object state
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the object state with provided identifier already exists in the same group
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct $objectStateUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    public function updateObjectState( ObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct );

    /**
     * Changes the priority of the state
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to change priority on an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     * @param int $priority
     */
    public function setPriorityOfObjectState( ObjectState $objectState, $priority );

    /**
     * Deletes a object state. The state of the content objects is reset to the
     * first object state in the group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete an object state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     */
    public function deleteObjectState( ObjectState $objectState );

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
    public function setContentState( ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup, ObjectState $objectState );

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
    public function getContentState( ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup );

    /**
     * Returns the number of objects which are in this state
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectState $objectState
     *
     * @return int
     */
    public function getContentCount( ObjectState $objectState );

    /**
     * Instantiates a new Object State Group Create Struct and sets $identified in it.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct
     */
    public function newObjectStateGroupCreateStruct( $identifier );

    /**
     * Instantiates a new Object State Group Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    public function newObjectStateGroupUpdateStruct();

    /**
     * Instantiates a new Object State Create Struct and sets $identifier in it.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct
     */
    public function newObjectStateCreateStruct( $identifier );

    /**
     * Instantiates a new Object State Update Struct.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct
     */
    public function newObjectStateUpdateStruct();
}
