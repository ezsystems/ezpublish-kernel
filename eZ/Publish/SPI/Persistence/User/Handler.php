<?php
/**
 * File containing the User Handler interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;

/**
 * Storage Engine handler for user module
 */
interface Handler
{

    /**
     * Create a user
     *
     * @param \eZ\Publish\SPI\Persistence\User\CreateStruct $userCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create( CreateStruct $userCreateStruct );

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function load( $userId );

    /**
     * Loads user with user login.
     *
     * Note: This method loads user by $login case in-sensitive on certain storage engines!
     *
     * @param string $login
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function loadByLogin( $login );

    /**
     * Loads user(s) with user email.
     *
     * As earlier eZ Publish versions supported several users having same email (ini config),
     * this function may return several users.
     *
     * @todo deprecate the feature supporting multiple users with the same email
     *
     * Note: This method loads user by $email case in-sensitive on certain storage engines!
     *
     * @param string $email
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadByEmail( $email );

    /**
     * Update the user information specified by the user struct
     *
     * @param mixed $userId
     * @param \eZ\Publish\SPI\Persistence\User\UpdateStruct $userUpdateStruct
     */
    public function update( $userId, UpdateStruct $userUpdateStruct );

    /**
     * Delete user with the given ID.
     *
     * @invariant the user exists
     *
     * @param mixed $userId
     */
    public function delete( $userId );

    /**
     * Creates a user group as child of $parentGroupId and returns it
     *
     * @invariant the parent group exists
     *
     * @param mixed $parentGroupId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function createGroup( $parentGroupId );

    /**
     * Loads the group with the given $groupId
     *
     * @param mixed $groupId
     *
     *
     * @return \eZ\Publish\SPI\Persistence\User\Group
     */
    public function loadGroup( $groupId );

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
    public function moveGroup( $groupId, $newParentId );

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
