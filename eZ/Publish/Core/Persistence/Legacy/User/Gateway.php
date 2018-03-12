<?php

/**
 * File containing the User Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct;

/**
 * Base class for user gateways.
 */
abstract class Gateway
{
    /**
     * Create user.
     *
     * @param user $user
     *
     * @return mixed
     */
    abstract public function createUser(User $user);

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    abstract public function deleteUser($userId);

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return array
     */
    abstract public function load($userId);

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @return array
     */
    abstract public function loadByLogin($login);

    /**
     * Loads user with user email.
     *
     * @param string $email
     *
     * @return array
     */
    abstract public function loadByEmail($email);

    /**
     * Loads user with user hash.
     *
     * @param string $hash
     *
     * @return array
     */
    abstract public function loadUserByToken($hash);

    /**
     * Update the user information specified by the user struct.
     *
     * @param User $user
     */
    abstract public function updateUser(User $user);

    /**
     * Update the user acoount key specified by the user account key struct.
     *
     * @param \eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct $userTokenUpdateStruct
     */
    abstract public function updateUserToken(UserTokenUpdateStruct $userTokenUpdateStruct);

    /**
     * Expires user account key with user hash.
     *
     * @param string $hash
     */
    abstract public function expireUserToken($hash);

    /**
     * Assigns role to user with given limitation.
     *
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     */
    abstract public function assignRole($contentId, $roleId, array $limitation);

    /**
     * Remove role from user or user group.
     *
     * @param mixed $contentId
     * @param mixed $roleId
     */
    abstract public function removeRole($contentId, $roleId);

    /**
     * Remove role from user or user group, by assignment ID.
     *
     * @param mixed $roleAssignmentId
     */
    abstract public function removeRoleAssignmentById($roleAssignmentId);
}
