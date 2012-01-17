<?php
namespace  ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\User\PolicyUpdate;

use ezp\PublicAPI\Values\User\Policy;

use ezp\PublicAPI\Values\User\RoleUpdate;

use ezp\PublicAPI\Values\User\PolicyCreate;

use ezp\PublicAPI\Values\User\Role;

use ezp\PublicAPI\Values\User\RoleCreate;



/**
 * This service provides methods for managing Roles and Policies
 * @package ezp\PublicAPI\Interfaces
 */
interface RoleService {

    /**
     * Creates a new Role
     *
     * @param RoleCreate $role
     * @return Role
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws ezp\PublicAPI\Interfaces\ForbiddenException if the name of the role already exists
     */
    public function createRole(/*RoleCreate*/ $role);

    /**
     * Updates the name and (5.x) description of the role
     * @param Role $role
     * @param RoleUpdate $roleUpdate
     * @return Role
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws ezp\PublicAPI\Interfaces\ForbiddenException if the name of the role already exists
     */
    public function updateRole(/*Role*/ $role, /*RoleUpdate*/ $update);

    /**
     * adds a new policy to the role
     * @param Role $role
     * @param PolicyCreate $policy
     * @return Role
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to add  a policy
     */
    public function addPolicy(/*Role*/ $role, /*PolicyCreate*/ $policy);

    /**
     * removes a policy from the role
     * @param Role $role
     * @param Policy $policy the policy to remove from the role
     * @return Role the updated role
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a policy
     */
    public function removePolicy(/*Role*/ $role, /*Policy*/ $policy);

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitaions are replaced by the ones in $roleUpdate
     * @param PolicyUpdate $policyUpdate
     * @param Policy $policy
     * @return Policy
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to uŸdate a policy
     */
    public function updatePolicy(/*Policy*/ $policy, /*PolicyUpdate*/ $policyUpdate);

    /**
     * loads a role for the given name
     * @param string $name
     * @return Role
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws ezp\PublicAPI\Interfaces\NotFoundException if a role with the given name was not found
     */
    public function loadRole($name);

    /**
     * loads all roles
     * @return array an array of {@link Role}
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to read the roles
     */
    public function loadRoles();

    /**
     * deletes the given role
     * @param  Role $role
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to delete this role
     */
    public function deleteRole(/*Role*/ $role);

    /**
     *
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     * @param $userId
     * @return array an array of {@link Policy}
     * @throws ezp\PublicAPI\Interfaces\NotFoundException if a user with the given id was not found
     */
    public function loadPoliciesByUserId($userId);

    /**
     *
     * assigns a role to the given user group
     * @param Role $role
     * @param UserGroup $userGroup
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to assign a role
     */
    public function assignRoleToUserGroup(/*Role*/ $role, /*UserGroup*/ $userGroup);

    /**
     * removes a role from the given user group.
     * @param Role $role
     * @param UserGroup $userGroup
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws InvalidArgumentException  If the role is not assigned to the given user group
     */
    public function unassignRoleFromUserGroup(/*Role*/ $role, /*UserGroup*/ $userGroup);

    /**
     * assigns a role to the given user
     * @param Role $role
     * @param User $user
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to assign a role
     */
     
    public function assignRoleToUser(/*Role*/ $role, /*User*/ $user);

    /**
     * removes a role from the given user.
     * @param Role $role
     * @param User $user
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws InvalidArgumentException If the role is not assigned to the user
     */
    public function unassignRoleFromUser(/*Role*/ $role, /*User*/ $user);


    /**
     * instanciates a role create class
     * @param string $name
     * @return RoleCreate
     */
    public function newRoleCreate($name);

    /**
     * instanciates a policy create class
     * @param string $module
     * @param string $function
     * @return PolicyCreate
     */
    public function newRoleCreate( $module, $function);

    /**
     * instanciates a policy update class
     * @return PolicyUpdate
     */
    public function newPolicyUpdate();
}