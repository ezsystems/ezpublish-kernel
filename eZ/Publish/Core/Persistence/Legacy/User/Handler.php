<?php
/**
 * File containing the UserHandler interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Handler as BaseUserHandler;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway as RoleGateway;
use eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use LogicException;

/**
 * Storage Engine handler for user module
 */
class Handler implements BaseUserHandler
{
    /**
     * Gateway for storing user data
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Gateway
     */
    protected $userGateway;

    /**
     * Gateway for storing role data
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway
     */
    protected $roleGateway;

    /**
     * Mapper for user related objects
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Mapper
     */
    protected $mapper;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter
     */
    protected $limitationConverter;

    /**
     * Construct from userGateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Gateway $userGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway $roleGateway
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\Legacy\User\Role\LimitationConverter $limitationConverter
     */
    public function __construct( Gateway $userGateway, RoleGateway $roleGateway, Mapper $mapper, LimitationConverter $limitationConverter )
    {
        $this->userGateway = $userGateway;
        $this->roleGateway = $roleGateway;
        $this->mapper = $mapper;
        $this->limitationConverter = $limitationConverter;
    }

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create( User $user )
    {
        $this->userGateway->createUser( $user );
        return $user;
    }

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function load( $userId )
    {
        $data = $this->userGateway->load( $userId );

        if ( empty( $data ) )
        {
            throw new NotFound( 'user', $userId );
        }

        return $this->mapper->mapUser( reset( $data ) );
    }

    /**
     * Loads user with user login.
     *
     * @param string $login
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function loadByLogin( $login )
    {
        $data = $this->userGateway->loadByLogin( $login );

        if ( empty( $data ) )
        {
            throw new NotFound( 'user', $login );
        }
        else if ( isset( $data[1] ) )
        {
            throw new LogicException( "Found more then one user with login '{$login}'" );
        }

        return $this->mapper->mapUser( $data[0] );
    }

    /**
     * Loads user(s) with user email.
     *
     * As earlier eZ Publish versions supported several users having same email (ini config),
     * this function may return several users.
     *
     * @param string $email
     *
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadByEmail( $email )
    {
        $data = $this->userGateway->loadByEmail( $email );

        if ( empty( $data ) )
        {
            return array();
        }

        return $this->mapper->mapUsers( $data );
    }

    /**
     * Update the user information specified by the user struct
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
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
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
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
     * Loads a specified role by $roleId
     *
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRole( $roleId )
    {
        $data = $this->roleGateway->loadRole( $roleId );

        if ( empty( $data ) )
        {
            throw new NotFound( 'role', $roleId );
        }

        $role = $this->mapper->mapRole( $data );
        foreach ( $role->policies as $policy )
        {
            $this->limitationConverter->toSPI( $policy );
        }

        return $role;
    }

    /**
     * Loads a specified role by $identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRoleByIdentifier( $identifier )
    {
        $data = $this->roleGateway->loadRoleByIdentifier( $identifier );

        if ( empty( $data ) )
        {
            throw new NotFound( 'role', $identifier );
        }

        $role = $this->mapper->mapRole( $data );
        foreach ( $role->policies as $policy )
        {
            $this->limitationConverter->toSPI( $policy );
        }

        return $role;
    }

    /**
     * Loads all roles
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRoles()
    {
        $data = $this->roleGateway->loadRoles();

        $roles = $this->mapper->mapRoles( $data );
        foreach ( $roles as $role )
        {
            foreach ( $role->policies as $policy )
            {
                $this->limitationConverter->toSPI( $policy );
            }
        }

        return $roles;
    }

    /**
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
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
        $role = $this->loadRole( $roleId );

        foreach ( $role->policies as $policy )
        {
            $this->roleGateway->removePolicy( $policy->id );
        }

        $this->roleGateway->deleteRole( $role->id );
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $legacyPolicy = clone $policy;
        $this->limitationConverter->toLegacy( $legacyPolicy );

        $this->roleGateway->addPolicy( $roleId, $legacyPolicy );
        $policy->id = $legacyPolicy->id;
        $policy->roleId = $legacyPolicy->roleId;

        return $policy;
    }

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     */
    public function updatePolicy( Policy $policy )
    {
        $policy = clone $policy;
        $this->limitationConverter->toLegacy( $policy );

        $this->roleGateway->removePolicyLimitations( $policy->id );
        $this->roleGateway->addPolicyLimitations( $policy->id, $policy->limitations );
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function deletePolicy( $policyId )
    {
        // Each policy can only be associated to exactly one role. Thus it is
        // sufficient to use the policyId for identification and just remove
        // the policy completely.
        $this->roleGateway->removePolicy( $policyId );
    }

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        $data = $this->roleGateway->loadPoliciesByUserId( $userId );

        $policies = $this->mapper->mapPolicies( $data );

        foreach ( $policies as $policy )
        {
            $this->limitationConverter->toSPI( $policy );
        }

        return $policies;
    }

    /**
     * Assigns role to a user or user group with given limitations
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
     * @param mixed $contentId The groupId or userId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $contentId, $roleId, array $limitation = null )
    {
        $limitation = $limitation ?: array( '' => array( '' ) );
        $this->userGateway->assignRole( $contentId, $roleId, $limitation );
    }

    /**
     * Un-assign a role
     *
     * @param mixed $contentId The user or user group Id to un-assign the role from.
     * @param mixed $roleId
     */
    public function unAssignRole( $contentId, $roleId )
    {
        $this->userGateway->removeRole( $contentId, $roleId );
    }

    /**
     * Loads roles assignments Role
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByRoleId( $roleId )
    {
        $data = $this->roleGateway->loadRoleAssignmentsByRoleId( $roleId );

        if ( empty( $data ) )
        {
            return array();
        }

        return $this->mapper->mapRoleAssignments( $data );
    }

    /**
     * Loads roles assignments to a user/group
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $groupId In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     *                      By the nature of legacy this can currently also be used to get by $userId.
     * @param boolean $inherit If true also return inherited role assignments from user groups.
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherit = false )
    {
        $data = $this->roleGateway->loadRoleAssignmentsByGroupId( $groupId, $inherit );

        if ( empty( $data ) )
            return array();

        return $this->mapper->mapRoleAssignments( $data );
    }
}
