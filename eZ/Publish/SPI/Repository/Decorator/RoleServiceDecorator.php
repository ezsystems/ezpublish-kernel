<?php /** @noinspection OverridingDeprecatedMethodInspection */

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\RoleService;
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

abstract class RoleServiceDecorator implements RoleService
{
    /** @var \eZ\Publish\API\Repository\RoleService */
    protected $innerService;

    public function __construct(RoleService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createRole(RoleCreateStruct $roleCreateStruct)
    {
        return $this->innerService->createRole($roleCreateStruct);
    }

    public function createRoleDraft(Role $role)
    {
        return $this->innerService->createRoleDraft($role);
    }

    public function loadRoleDraft($id)
    {
        return $this->innerService->loadRoleDraft($id);
    }

    public function loadRoleDraftByRoleId($roleId)
    {
        return $this->innerService->loadRoleDraftByRoleId($roleId);
    }

    public function updateRoleDraft(
        RoleDraft $roleDraft,
        RoleUpdateStruct $roleUpdateStruct
    ) {
        return $this->innerService->updateRoleDraft($roleDraft, $roleUpdateStruct);
    }

    public function addPolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyCreateStruct $policyCreateStruct
    ) {
        return $this->innerService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
    }

    public function removePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policyDraft
    ) {
        return $this->innerService->removePolicyByRoleDraft($roleDraft, $policyDraft);
    }

    public function updatePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ) {
        return $this->innerService->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
    }

    public function deleteRoleDraft(RoleDraft $roleDraft)
    {
        return $this->innerService->deleteRoleDraft($roleDraft);
    }

    public function publishRoleDraft(RoleDraft $roleDraft)
    {
        return $this->innerService->publishRoleDraft($roleDraft);
    }

    public function updateRole(
        Role $role,
        RoleUpdateStruct $roleUpdateStruct
    ) {
        return $this->innerService->updateRole($role, $roleUpdateStruct);
    }

    public function addPolicy(
        Role $role,
        PolicyCreateStruct $policyCreateStruct
    ) {
        return $this->innerService->addPolicy($role, $policyCreateStruct);
    }

    public function deletePolicy(Policy $policy)
    {
        return $this->innerService->deletePolicy($policy);
    }

    public function updatePolicy(
        Policy $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ) {
        return $this->innerService->updatePolicy($policy, $policyUpdateStruct);
    }

    public function loadRole($id)
    {
        return $this->innerService->loadRole($id);
    }

    public function loadRoleByIdentifier($identifier)
    {
        return $this->innerService->loadRoleByIdentifier($identifier);
    }

    public function loadRoles()
    {
        return $this->innerService->loadRoles();
    }

    public function deleteRole(Role $role)
    {
        return $this->innerService->deleteRole($role);
    }

    public function loadPoliciesByUserId($userId)
    {
        return $this->innerService->loadPoliciesByUserId($userId);
    }

    public function assignRoleToUserGroup(
        Role $role,
        UserGroup $userGroup,
        RoleLimitation $roleLimitation = null
    ) {
        return $this->innerService->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
    }

    public function unassignRoleFromUserGroup(
        Role $role,
        UserGroup $userGroup
    ) {
        return $this->innerService->unassignRoleFromUserGroup($role, $userGroup);
    }

    public function assignRoleToUser(
        Role $role,
        User $user,
        RoleLimitation $roleLimitation = null
    ) {
        return $this->innerService->assignRoleToUser($role, $user, $roleLimitation);
    }

    public function unassignRoleFromUser(
        Role $role,
        User $user
    ) {
        return $this->innerService->unassignRoleFromUser($role, $user);
    }

    public function loadRoleAssignment($roleAssignmentId)
    {
        return $this->innerService->loadRoleAssignment($roleAssignmentId);
    }

    public function getRoleAssignments(Role $role)
    {
        return $this->innerService->getRoleAssignments($role);
    }

    public function getRoleAssignmentsForUser(
        User $user,
        $inherited = false
    ) {
        return $this->innerService->getRoleAssignmentsForUser($user, $inherited);
    }

    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup)
    {
        return $this->innerService->getRoleAssignmentsForUserGroup($userGroup);
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        return $this->innerService->removeRoleAssignment($roleAssignment);
    }

    public function newRoleCreateStruct($name)
    {
        return $this->innerService->newRoleCreateStruct($name);
    }

    public function newPolicyCreateStruct(
        $module,
        $function
    ) {
        return $this->innerService->newPolicyCreateStruct($module, $function);
    }

    public function newPolicyUpdateStruct()
    {
        return $this->innerService->newPolicyUpdateStruct();
    }

    public function newRoleUpdateStruct()
    {
        return $this->innerService->newRoleUpdateStruct();
    }

    public function getLimitationType($identifier)
    {
        return $this->innerService->getLimitationType($identifier);
    }

    public function getLimitationTypesByModuleFunction(
        $module,
        $function
    ) {
        return $this->innerService->getLimitationTypesByModuleFunction($module, $function);
    }
}
