<?php
namespace  ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\User\PolicyUpdateStruct;
use ezp\PublicAPI\Values\User\Policy;
use ezp\PublicAPI\Values\User\RoleUpdateStruct;
use ezp\PublicAPI\Values\User\PolicyCreateStruct;
use ezp\PublicAPI\Values\User\Role;
use ezp\PublicAPI\Values\User\RoleCreateStruct;
use ezp\PublicAPI\Values\User\RoleAssignment;
use ezp\PublicAPI\Values\User\Limitation\RoleLimitation;

/**
 * This service provides methods for managing Roles and Policies
 *
 * @todo add get roles for user including limitations
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface RoleService
{
    /**
     * Creates a new Role
     *
     * @param \ezp\PublicAPI\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \ezp\PublicAPI\Values\User\Role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \ezp\PublicAPI\Interfaces\IllegalArgumentException if the name of the role already exists
     */
    public function createRole( RoleCreateStruct $roleCreateStruct );

    /**
     * Updates the name and (5.x) description of the role
     * 
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \ezp\PublicAPI\Values\User\Role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \ezp\PublicAPI\Interfaces\IllegalArgumentException if the name of the role already exists
     */
    public function updateRole( Role $role, RoleUpdateStruct $update );

    /**
     * adds a new policy to the role
     * 
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \ezp\PublicAPI\Values\User\Role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to add  a policy
     */
    public function addPolicy( Role $role, PolicyCreateStruct $policyCreateStruct );

    /**
     * removes a policy from the role
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\Policy $policy the policy to remove from the role
     *
     * @return \ezp\PublicAPI\Values\User\Role the updated role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a policy
     */
    public function removePolicy( Role $role, Policy $policy );

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitaions are replaced by the ones in $roleUpdateStruct
     *
     * @param \ezp\PublicAPI\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \ezp\PublicAPI\Values\User\Policy $policy
     *
     * @return \ezp\PublicAPI\Values\User\Policy
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to u�date a policy
     */
    public function updatePolicy( Policy $policy, PolicyUpdateStruct $policyUpdateStruct );

    /**
     * loads a role for the given name
     *
     * @param string $name
     *
     * @return \ezp\PublicAPI\Values\User\Role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \ezp\PublicAPI\Interfaces\NotFoundException if a role with the given name was not found
     */
    public function loadRole( $name );

    /**
     * loads all roles
     *
     * @return array an array of {@link \ezp\PublicAPI\Values\User\Role}
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read the roles
     */
    public function loadRoles();

    /**
     * deletes the given role
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to delete this role
     */
    public function deleteRole( Role $role );

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @param $userId
     *
     * @return array an array of {@link Policy}
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundException if a user with the given id was not found
     */
    public function loadPoliciesByUserId( $userId );

    /**
     * assigns a role to the given user group
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\UserGroup $userGroup
     * @param \ezp\PublicAPI\Values\User\RoleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to assign a role
     */
    public function assignRoleToUserGroup( Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null );

    /**
     * removes a role from the given user group.
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\UserGroup $userGroup
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \ezp\PublicAPI\Interfaces\InvalidArgumentException  If the role is not assigned to the given user group
     */
    public function unassignRoleFromUserGroup( Role $role, UserGroup $userGroup );

    /**
     * assigns a role to the given user
     *
     * @todo add limitations
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\User $user
     * @param \ezp\PublicAPI\Values\User\RoleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to assign a role
     */
    public function assignRoleToUser( Role $role, User $user, RoleLimitation $roleLimitation = null );

    /**
     * removes a role from the given user.
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     * @param \ezp\PublicAPI\Values\User\User $user
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \ezp\PublicAPI\Interfaces\InvalidArgumentException If the role is not assigned to the user
     */
    public function unassignRoleFromUser( Role $role, User $user );

    /**
     * returns the assigned user and user groups to this role
     *
     * @param \ezp\PublicAPI\Values\User\Role $role
     *
     * @return array an array of {@link RoleAssignment}
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read a role
     */
    public function getRoleAssignments( Role $role );

    /**
     * returns the roles assigned to the given user
     *
     * @param \ezp\PublicAPI\Values\User\User $user
     *
     * @return array an array of {@link UserRoleAssignment}
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read a user
     */
    public function getRoleAssignmentsForUser( User $user );

    /**
     * returns the roles assigned to the given user group
     *
     * @param \ezp\PublicAPI\Values\User\UserGroup $userGroup
     *
     * @return array an array of {@link UserGroupRoleAssignment}
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read a user group
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup );

    /**
     * instanciates a role create class
     *
     * @param string $name
     *
     * @return \ezp\PublicAPI\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name );

    /**
     * instanciates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \ezp\PublicAPI\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function );

    /**
     * instanciates a policy update class
     *
     * @return \ezp\PublicAPI\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct();

    /**
     * instanciates a policy update class
     *
     * @return \ezp\PublicAPI\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct();
}
