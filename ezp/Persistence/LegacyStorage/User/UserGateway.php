<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\User;

/**
 * Base class for content type gateways.
 */
abstract class UserGateway
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
}
