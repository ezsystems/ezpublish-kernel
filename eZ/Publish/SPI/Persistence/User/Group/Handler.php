<?php

namespace eZ\Publish\SPI\Persistence\User\Group;

/**
 * @todo: Should this be merged to User\Handler?
 */
interface Handler
{
    /**
     * Creates a user group as child of $parentGroupId and returns it
     *
     * @param mixed $parentGroupId
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function create( $parentGroupId );

    /**
     * Loads the group with the given $groupId
     *
     * @param mixed $groupId
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function load( $groupId );

    /**
     * Loads all direct children of $parentGroupId
     *
     * @param mixed $parentGroupId
     * @return \eZ\Publish\SPI\Persistence\User\Group[]
     */
    public function loadSubGroups( $parentGroupId );

    /**
     * Moves $groupId below $newParentId
     *
     * @param mixed $groupId
     * @param mixed $newParentId
     */
    public function move( $groupId, $newParentId );

    /**
     * Assigns $userId to $groupId
     *
     * @param mixed $userId
     * @param mixed $groupId
     */
    public function assignUserToGroup( $userId, $groupId );

    /**
     * Remove $userId from $groupId
     *
     * @param mixed $userId
     * @param mixed $groupId
     */
    public function unAssignUserFromGroup( $userId, $groupId );

    /**
     * Loads groups for $userId
     *
     * @param mixed $userId
     * @return \eZ\Publish\SPI\Persistence\User\Group[]
     */
    public function loadGroupsOfUser( $userId );

    /**
     * Loads all users that are assigned to $groupId
     *
     * @param mixed $groupId
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadUsersOfGroup( $groupId );
}
