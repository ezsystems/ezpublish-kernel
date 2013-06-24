<?php
/**
 * File containing the User Gateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;

/**
 * Base class for user gateways.
 */
abstract class Gateway
{
    /**
     * Create user
     *
     * @param user $user
     *
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
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return array
     */
    abstract public function load( $userId );

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @return array
     */
     abstract public function loadByLogin( $login );

    /**
     * Loads user with user email.
     *
     * @param string $email
     *
     * @return array
     */
     abstract public function loadByEmail( $email );

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    abstract public function updateUser( User $user );

    /**
     * Assigns role to user with given limitation
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
