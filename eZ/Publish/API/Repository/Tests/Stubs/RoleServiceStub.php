<?php
/**
 * File containing the RoleServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\Repository;
use \eZ\Publish\API\Repository\RoleService;
use \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use \eZ\Publish\API\Repository\Values\User\Policy;
use \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\Role;
use \eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\UserGroup;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\IllegalArgumentExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyCreateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyUpdateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleCreateStructStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\RoleService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\RoleService
 */
class RoleServiceStub implements RoleService
{
    /**
     * @var integer
     */
    private $nextRoleId = 0;

    /**
     * @var array
     */
    private $nameToRoleId = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role[]
     */
    private $roles;

    /**
     * Temporary solution to emulate user policies.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     * @todo REMOVE THIS WORKAROUND
     */
    private $userPolicies = array();

    /**
     * @var integer
     */
    private $nextPolicyId = 0;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        $this->repository = $repository;

        $this->roles = array(
            1 => new RoleStub( array( 'id' => 1, 'identifier' => 'Anonymous' ) ),
            2 => new RoleStub( array( 'id' => 2, 'identifier' => 'Administrator' ) ),
            3 => new RoleStub( array( 'id' => 3, 'identifier' => 'Editor' ) ),
            4 => new RoleStub( array( 'id' => 4, 'identifier' => 'Partner' ) ),
            5 => new RoleStub( array( 'id' => 5, 'identifier' => 'Member' ) ),
        );

        foreach ( $this->roles as $role )
        {
            ++$this->nextRoleId;

            $this->nameToRoleId[$role->identifier] = $role->id;
        }
    }

    /**
     * Creates a new Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function createRole( RoleCreateStruct $roleCreateStruct )
    {
        if ( isset( $this->nameToRoleId[$roleCreateStruct->identifier] ) )
        {
            throw new IllegalArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $role = new RoleStub(
            array(
                'id'            =>  ++$this->nextRoleId,
                'identifier'    =>  $roleCreateStruct->identifier,
                'names'         =>  $roleCreateStruct->names,
                'descriptions'  =>  $roleCreateStruct->descriptions
            )
        );

        $this->roles[$role->id]                = $role;
        $this->nameToRoleId[$role->identifier] = $role->id;

        foreach ( $roleCreateStruct->getPolicies() as $policy )
        {
            $role = $this->addPolicy( $role, $policy );
        }
        return $role;
    }

    /**
     * Updates the name and (5.x) description of the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( Role $role, RoleUpdateStruct $roleUpdateStruct )
    {
        $roleName = $roleUpdateStruct->identifier ?: $role->identifier;

        if ( isset( $this->nameToRoleId[$roleName] ) && $this->nameToRoleId[$roleName] !== $role->id )
        {
            throw new IllegalArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $updatedRole = new RoleStub(
            array(
                'id'            =>  $role->id,
                'identifier'    =>  $roleName,
                'names'         =>  $roleUpdateStruct->names ?: $role->names,
                'descriptions'  =>  $roleUpdateStruct->descriptions ?: $role->descriptions
            )
        );

        unset( $this->roles[$role->id], $this->nameToRoleId[$role->identifier] );

        $this->roles[$updatedRole->id]                = $updatedRole;
        $this->nameToRoleId[$updatedRole->identifier] = $updatedRole->id;

        return $this->roles[$updatedRole->id];
    }

    /**
     * adds a new policy to the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy( Role $role, PolicyCreateStruct $policyCreateStruct )
    {
        $policies   = $role->getPolicies();
        $policies[] = new PolicyStub(
            array(
                'id'        =>  ++$this->nextPolicyId,
                'roleId'    =>  $role->id,
                'module'    =>  $policyCreateStruct->module,
                'function'  =>  $policyCreateStruct->function
            )
        );

        $this->roles[$role->id] = new RoleStub(
            array(
                'id'            =>  $role->id,
                'identifier'    =>  $role->identifier,
                'names'         =>  $role->names,
                'descriptions'  =>  $role->descriptions
            ),
            $policies
        );

        return $this->roles[$role->id];
    }

    /**
     * removes a policy from the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to remove from the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role the updated role
     */
    public function removePolicy( Role $role, Policy $policy )
    {
        // TODO: Implement removePolicy() method.
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitaions are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to u�date a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( Policy $policy, PolicyUpdateStruct $policyUpdateStruct )
    {
        $newPolicy = new PolicyStub(
            array(
                'id'           =>  $policy->id,
                'roleId'       =>  $policy->roleId,
                'module'       =>  $policy->module,
                'function'     =>  $policy->function,
                'limitations'  =>  $policyUpdateStruct->getLimitations()
            )
        );

        $policies = $this->roles[$policy->roleId]->getPolicies();
        foreach ( $policies as $i => $rolePolicy )
        {
            if ( $rolePolicy->id !== $policy->id )
            {
                continue;
            }

            $policies[$i] = $newPolicy;
            break;
        }

        $this->roles[$policy->roleId] = new RoleStub(
            array(
                'id'            =>  $this->roles[$policy->roleId]->id,
                'identifier'    =>  $this->roles[$policy->roleId]->identifier,
                'names'         =>  $this->roles[$policy->roleId]->names,
                'descriptions'  =>  $this->roles[$policy->roleId]->descriptions
            ),
            $policies
        );

        return $newPolicy;
    }

    /**
     * loads a role for the given id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( $id )
    {
        if ( isset( $this->roles[$id] ) )
        {
            return $this->roles[$id];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
    }

    /**
     * loads a role for the given name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier( $name )
    {
        if ( isset( $this->nameToRoleId[$name] ) )
        {
            return $this->roles[$this->nameToRoleId[$name]];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
    }

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Role}
     */
    public function loadRoles()
    {
        return array_values( $this->roles );
    }

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( Role $role )
    {
        unset( $this->roles[$role->id], $this->nameToRoleId[$role->identifier] );
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param $userId
     *
     * @return array an array of {@link Policy}
     */
    public function loadPoliciesByUserId( $userId )
    {
        if ( isset( $this->userPolicies[$userId] ) )
        {
            return $this->userPolicies[$userId];
        }
        throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
        // TODO: Implement loadPoliciesByUserId() method.
    }

    /**
     * assigns a role to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup( Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        // TODO: Implement assignRoleToUserGroup() method.
    }

    /**
     * removes a role from the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup( Role $role, UserGroup $userGroup )
    {
        // TODO: Implement unassignRoleFromUserGroup() method.
    }

    /**
     * assigns a role to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @todo add limitations
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser( Role $role, User $user, RoleLimitation $roleLimitation = null )
    {
        // TODO: Implement assignRoleToUser() method.
    }

    /**
     * removes a role from the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser( Role $role, User $user )
    {
        // TODO: Implement unassignRoleFromUser() method.
    }

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return array an array of {@link RoleAssignment}
     */
    public function getRoleAssignments( Role $role )
    {
        // TODO: Implement getRoleAssignments() method.
    }

    /**
     * returns the roles assigned to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return array an array of {@link UserRoleAssignment}
     */
    public function getRoleAssignmentsForUser( User $user )
    {
        // TODO: Implement getRoleAssignmentsForUser() method.
    }

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link UserGroupRoleAssignment}
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        // TODO: Implement getRoleAssignmentsForUserGroup() method.
    }

    /**
     * instanciates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new RoleCreateStructStub( $name );
    }

    /**
     * instanciates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStructStub( $module, $function );
    }

    /**
     * instanciates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStructStub();
    }

    /**
     * instanciates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    /**
     * Temporary method to simulate user policies.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Policy[] $policies
     *
     * @return void
     * @todo REMOVE THIS WORKAROUND
     */
    public function setPoliciesForUser( User $user, array $policies )
    {
        $this->userPolicies[$user->id] = $policies;
    }
}
