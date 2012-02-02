<?php
/**
 * File containing the User Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\User;
use ezp\Persistence\User;

/**
 * Base class for user gateways.
 */
abstract class Gateway
{
    /**
     * Create user
     *
     * @param user $user
     * @return mixed
     */
    abstract public function createUser( User $user );

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    abstract public function deleteUser( $userId );

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    abstract public function updateUser( User $user );

    /**
     * Assign role to user with given limitation
     *
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     */
    abstract public function assignRole( $contentId, $roleId, array $limitation );

    /**
     * Remove role from user
     *
     * @param mixed $contentId
     * @param mixed $roleId
     */
    abstract public function removeRole( $contentId, $roleId );
}
