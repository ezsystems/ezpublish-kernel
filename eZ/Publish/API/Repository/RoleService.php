<?php

/**
 * File containing the eZ\Publish\API\Repository\RoleService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace  eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;

/**
 * This service provides methods for managing Roles and Policies.
 */
interface RoleService
{
    /**
     * Creates a new RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the name of the role already exists or if limitation of the same type
     *         is repeated in the policy create struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRole(RoleCreateStruct $roleCreateStruct);

    /**
     * Creates a new RoleDraft for existing Role.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the Role already has a Role Draft that will need to be removed first
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRoleDraft(Role $role);

    /**
     * Loads a RoleDraft for the given id.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a RoleDraft with the given id was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraft($id);

    /**
     * Loads a RoleDraft by the ID of the role it was created from.
     *
     * @param mixed $roleId ID of the role the draft was created from.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a RoleDraft with the given id was not found
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function loadRoleDraftByRoleId($roleId);

    /**
     * Updates the properties of a RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier of the RoleDraft already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function updateRoleDraft(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct);

    /**
     * Adds a new policy to the RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function addPolicyByRoleDraft(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct);

    /**
     * Removes a policy from a RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if policy does not belong to the given RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param PolicyDraft $policyDraft the policy to remove from the RoleDraft
     * @return RoleDraft if the authenticated user is not allowed to remove a policy
     */
    public function removePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policyDraft);

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    public function updatePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct);

    /**
     * Deletes the given RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function deleteRoleDraft(RoleDraft $roleDraft);

    /**
     * Publishes the given RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to publish this RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function publishRoleDraft(RoleDraft $roleDraft);

    /**
     * Updates the name of the role.
     *
     * @deprecated since 6.0, use {@see updateRoleDraft}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole(Role $role, RoleUpdateStruct $roleUpdateStruct);

    /**
     * Adds a new policy to the role.
     *
     * @deprecated since 6.0, use {@see addPolicyByRoleDraft}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy(Role $role, PolicyCreateStruct $policyCreateStruct);

    /**
     * Deletes a policy.
     *
     * @deprecated since 6.0, use {@link removePolicyByRoleDraft()} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to delete
     */
    public function deletePolicy(Policy $policy);

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @deprecated since 6.0, use {@link updatePolicyByRoleDraft()} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy(Policy $policy, PolicyUpdateStruct $policyUpdateStruct);

    /**
     * Loads a role for the given id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole($id);

    /**
     * Loads a role for the given identifier.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier($identifier);

    /**
     * Loads all roles.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function loadRoles();

    /**
     * Deletes the given role.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole(Role $role);

    /**
     * Loads all policies from roles which are assigned to a user or to user groups to which the user belongs.
     *
     * @deprecated Since 6.8, not currently in use as permission system needs to know about role assignment limitations.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function loadPoliciesByUserId($userId);

    /**
     * Assigns a role to the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If assignment already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup(Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null);

    /**
     * Removes a role from the given user group.
     *
     * @deprecated since 6.0, use {@see removeRoleAssignment} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup(Role $role, UserGroup $userGroup);

    /**
     * Assigns a role to the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If assignment already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser(Role $role, User $user, RoleLimitation $roleLimitation = null);

    /**
     * Removes a role from the given user.
     *
     * @deprecated since 6.0, use {@see removeRoleAssignment} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser(Role $role, User $user);

    /**
     * Loads a role assignment for the given id.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the role assignment was not found
     *
     * @param mixed $roleAssignmentId
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment
     */
    public function loadRoleAssignment($roleAssignmentId);

    /**
     * Returns the assigned user and user groups to this role.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments(Role $role);

    /**
     * Returns UserRoleAssignments assigned to the given User.
     *
     * If second parameter \$inherited is true then UserGroupRoleAssignment is also returned for UserGroups User is
     * placed in as well as those inherited from parent UserGroups.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current user is not allowed to read a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException On invalid User object
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param bool $inherited Also return all inherited Roles from UserGroups User belongs to, and it's parents.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[]|\eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[]
     */
    public function getRoleAssignmentsForUser(User $user, $inherited = false);

    /**
     * Returns the UserGroupRoleAssignments assigned to the given UserGroup.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[]
     */
    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup);

    /**
     * Removes the given role assignment.
     *
     * i.e. unassigns a user or a user group from a role with the given limitations
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role assignment
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleAssignment $roleAssignment
     */
    public function removeRoleAssignment(RoleAssignment $roleAssignment);

    /**
     *  Instantiates a role create class.
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct($name);

    /**
     * Instantiates a policy create class.
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct($module, $function);

    /**
     * Instantiates a policy update class.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct();

    /**
     * Instantiates a policy update class.
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct();

    /**
     * Returns the LimitationType registered with the given identifier.
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     *
     * @throws \RuntimeException On missing Limitation
     */
    public function getLimitationType($identifier);

    /**
     * Returns the LimitationType's assigned to a given module/function.
     *
     * Typically used for:
     *  - Internal validation limitation value use on Policies
     *  - Role admin gui for editing policy limitations incl list limitation options via valueSchema()
     *
     * @param string $module Legacy name of "controller", it's a unique identifier like "content"
     * @param string $function Legacy name of a controller "action", it's a unique within the controller like "read"
     *
     * @return \eZ\Publish\SPI\Limitation\Type[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If module/function to limitation type mapping
     *                                                                 refers to a non existing identifier.
     */
    public function getLimitationTypesByModuleFunction($module, $function);
}
