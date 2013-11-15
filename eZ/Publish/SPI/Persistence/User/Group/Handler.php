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

    /**
     * Assigns role to a user group with given limitations
     *
     * The limitation array looks like:
     * <code>
     *  array(
     *      'Subtree' => array(
     *          '/1/2/',
     *          '/1/4/',
     *      ),
     *      'Foo' => array( 'Bar' ),
     *      …
     *  )
     * </code>
     *
     * Where the keys are the limitation identifiers, and the respective values
     * are an array of limitation values. The limitation parameter is optional.
     *
     * @param mixed $groupId The groupId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $groupId, $roleId, array $limitation = null );

    /**
     * Un-assign a role
     *
     * @param mixed $groupId The group Id to un-assign the role from.
     * @param mixed $roleId
     */
    public function unAssignRole( $groupId, $roleId );
}
