<?php
/**
 * File containing the UserHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\User;
use ezp\Persistence\User,
    ezp\Persistence\User\Handler as BaseUserHandler,
    ezp\Persistence\User\Role,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy,
    ezp\Persistence\Storage\Legacy\User\Role\Gateway as RoleGateway;

/**
 * Storage Engine handler for user module
 *
 */
class Handler implements BaseUserHandler
{
    /**
     * Gaateway for storing user data
     *
     * @var \ezp\Persistence\Storage\Legacy\User\Gateway
     */
    protected $userGateway;

    /**
     * Gaateway for storing role data
     *
     * @var \ezp\Persistence\Storage\Legacy\User\Role\Gateway
     */
    protected $roleGateway;

    /**
     * Construct from userGateway
     *
     * @param \ezp\Persistence\Storage\Legacy\User\Gateway $userGateway
     * @param \ezp\Persistence\Storage\Legacy\User\Role\Gateway $roleGateway
     * @return void
     */
    public function __construct( Gateway $userGateway, RoleGateway $roleGateway )
    {
        $this->userGateway = $userGateway;
        $this->roleGateway = $roleGateway;
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
        $this->userGateway->createUser( $user );
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function deleteUser( $userId )
    {
        $this->userGateway->deleteUser( $userId );
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param User $user
     */
    public function updateUser( User $user )
    {
        $this->userGateway->updateUser( $user );
    }

    /**
     * Create new role
     *
     * @param Role $role
     * @return Role
     */
    public function createRole( Role $role )
    {
        $this->roleGateway->createRole( $role );

        foreach ( $role->policies as $policy )
        {
            $this->addPolicy( $role->id, $policy );
        }

        return $role;
    }

    /**
     * Update role
     *
     * @param RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        $this->roleGateway->updateRole( $role );
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        $this->roleGateway->deleteRole( $roleId );
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param Policy $policy
     * @return void
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $this->roleGateway->addPolicy( $roleId, $policy );

        return $policy;
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
        $this->roleGateway->removePolicy( $roleId, $policyId );
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
     * @param mixed $userId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $userId, $roleId, $limitation )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @param mixed $userId
     * @param mixed $roleId
     */
    public function removeRole( $userId, $roleId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
?>
