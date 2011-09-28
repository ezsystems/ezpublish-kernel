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
    ezp\Persistence\Storage\Legacy\User\Role\Gateway as RoleGateway,
    \RuntimeException;

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
     * Mapper for user related objects
     *
     * @var \ezp\Persistence\Storage\Legacy\User\Mapper
     */
    protected $mapper;

    /**
     * Construct from userGateway
     *
     * @param \ezp\Persistence\Storage\Legacy\User\Gateway $userGateway
     * @param \ezp\Persistence\Storage\Legacy\User\Role\Gateway $roleGateway
     * @return void
     */
    public function __construct( Gateway $userGateway, RoleGateway $roleGateway, Mapper $mapper )
    {
        $this->userGateway = $userGateway;
        $this->roleGateway = $roleGateway;
        $this->mapper      = $mapper;
    }

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \ezp\Persistence\User $user
     * @return \ezp\Persistence\User
     */
    public function create( User $user )
    {
        $this->userGateway->createUser( $user );
        return $user;
    }

    /**
     * Load user with user ID.
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User
     */
    public function load( $userId )
    {
        $data = $this->userGateway->load( $userId );

        if ( empty( $data ) )
        {
            throw new \ezp\Base\Exception\NotFound( 'user', $userId );
        }

        $user = new User();
        $user->id            = $data[0]['contentobject_id'];
        $user->login         = $data[0]['login'];
        $user->email         = $data[0]['email'];
        $user->passwordHash  = $data[0]['password_hash'];
        $user->hashAlgorithm = $data[0]['password_hash_type'];
        $user->isEnabled     = (bool) $data[0]['is_enabled'];
        $user->maxLogin      = $data[0]['max_login'];

        return $user;
    }

    /**
     * Load user with user login / email.
     *
     * @param string $login
     * @param bool $alsoMatchEmail Also match user email, caller must verify that $login is a valid email address.
     * @return \ezp\Persistence\User[]
     */
    public function loadByLogin( $login, $alsoMatchEmail = false )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param \ezp\Persistence\User $user
     */
    public function update( User $user )
    {
        $this->userGateway->updateUser( $user );
    }

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function delete( $userId )
    {
        $this->userGateway->deleteUser( $userId );
    }

    /**
     * Create new role
     *
     * @param \ezp\Persistence\User\Role $role
     * @return \ezp\Persistence\User\Role
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
     * Load a specified role by id
     *
     * @param mixed $roleId
     * @return \ezp\Persistence\User\Role
     * @throws \ezp\Base\Exception\NotFound If role is not found
     */
    public function loadRole( $roleId )
    {
        $data = $this->roleGateway->loadRole( $roleId );

        if ( empty( $data ) )
        {
            throw new \ezp\Base\Exception\NotFound( 'role', $roleId );
        }

        return $this->mapper->mapRole( $data );
    }

    /**
     * Load roles assigned to a user/group (not including inherited roles)
     *
     * @param mixed $groupId
     * @return \ezp\Persistence\User\Role[]
     */
    public function loadRolesByGroupId( $groupId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Update role
     *
     * @param \ezp\Persistence\User\RoleUpdateStruct $role
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
     * @param \ezp\Persistence\User\Policy $policy
     * @return \ezp\Persistence\User\Policy
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $this->roleGateway->addPolicy( $roleId, $policy );

        return $policy;
    }

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \ezp\Persistence\User\Policy $policy
     */
    public function updatePolicy( Policy $policy )
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
        $this->roleGateway->removePolicy( $roleId, $policyId );
    }

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        // @TODO: Specification of how this works is pending by eZ.
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Assign role to user group with given limitation
     *
     * The limitation array may look like:
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
     * @param mixed $groupId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $groupId, $roleId, array $limitation = null )
    {
        $limitation = $limitation ?: array( '' => array( '' ) );
        $this->userGateway->assignRole( $groupId, $roleId, $limitation );
    }

    /**
     * Un-assign a role
     *
     * @param mixed $groupId The group / user Id to un-assign a role from
     * @param mixed $roleId
     */
    public function unAssignRole( $groupId, $roleId )
    {
        $this->userGateway->removeRole( $groupId, $roleId );
    }
}
?>
