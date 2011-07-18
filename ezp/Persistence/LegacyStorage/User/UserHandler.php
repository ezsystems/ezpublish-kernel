<?php
/**
 * File containing the UserHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\LegacyStorage\User;
use ezp\Persistence\User,
    ezp\Persistence\User\Role;

/**
 * Storage Engine handler for user module
 *
 */
class UserHandler implements \ezp\Persistence\User\Interfaces\UserHandler
{
    /**
     * Gaateway for storing user data
     * 
     * @var UserGateway
     */
    protected $gateway;

    /**
     * Construct from gateway
     * 
     * @param UserGateway $gateway 
     * @return void
     */
    public function __construct( UserGateway $gateway )
    {
        $this->gateway = $gateway;
    }

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param User $user
     */
    public function createUser( User $user )
    {
        $this->gateway->createUser( $user );
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function deleteUser( $userId )
    {
        $this->gateway->deleteUser( $userId );
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    public function updateUser( User $user )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Create new role
     *
     * @param Role $role
     * @return Role
     */
    public function createRole( Role $role )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Update role
     *
     * @todo Create a RoleUpdateStruct, which omits the policies
     * @param Role $role
     */
    public function updateRole( Role $role )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     */
    public function addPolicy( $roleId, $policyId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     */
    public function removePolicy( $roleId, $policyId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return UserPolicy[]
     */
    public function getPermissions( $userId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     * @todo Figure out which type $limitation has
     */
    public function assignRole( $contentId, $roleId, $limitation )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @param mixed $contentId
     * @param mixed $roleId
     */
    public function removeRole(  $contentId, $roleId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
?>
