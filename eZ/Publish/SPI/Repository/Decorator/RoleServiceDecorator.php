<?php

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
    /** @var eZ\Publish\API\Repository\RoleService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\RoleService
     */
    public function __construct(RoleService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createRole(RoleCreateStruct $roleCreateStruct)
    {
        $this->innerService->createRole($roleCreateStruct);
    }

    public function createRoleDraft(Role $role)
    {
        $this->innerService->createRoleDraft($role);
    }

    public function loadRoleDraft($id)
    {
        $this->innerService->loadRoleDraft($id);
    }

    public function loadRoleDraftByRoleId($roleId)
    {
        $this->innerService->loadRoleDraftByRoleId($roleId);
    }

    public function updateRoleDraft(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        $this->innerService->updateRoleDraft($roleDraft, $roleUpdateStruct);
    }

    public function addPolicyByRoleDraft(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct)
    {
        $this->innerService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
    }

    public function removePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policyDraft)
    {
        $this->innerService->removePolicyByRoleDraft($roleDraft, $policyDraft);
    }

    public function updatePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $this->innerService->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
    }

    public function deleteRoleDraft(RoleDraft $roleDraft)
    {
        $this->innerService->deleteRoleDraft($roleDraft);
    }

    public function publishRoleDraft(RoleDraft $roleDraft)
    {
        $this->innerService->publishRoleDraft($roleDraft);
    }

    public function updateRole(Role $role, RoleUpdateStruct $roleUpdateStruct)
    {
        $this->innerService->updateRole($role, $roleUpdateStruct);
    }

    public function addPolicy(Role $role, PolicyCreateStruct $policyCreateStruct)
    {
        $this->innerService->addPolicy($role, $policyCreateStruct);
    }

    public function deletePolicy(Policy $policy)
    {
        $this->innerService->deletePolicy($policy);
    }

    public function updatePolicy(Policy $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $this->innerService->updatePolicy($policy, $policyUpdateStruct);
    }

    public function loadRole($id)
    {
        $this->innerService->loadRole($id);
    }

    public function loadRoleByIdentifier($identifier)
    {
        $this->innerService->loadRoleByIdentifier($identifier);
    }

    public function loadRoles()
    {
        $this->innerService->loadRoles();
    }

    public function deleteRole(Role $role)
    {
        $this->innerService->deleteRole($role);
    }

    public function loadPoliciesByUserId($userId)
    {
        $this->innerService->loadPoliciesByUserId($userId);
    }

    public function assignRoleToUserGroup(Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null)
    {
        $this->innerService->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
    }

    public function unassignRoleFromUserGroup(Role $role, UserGroup $userGroup)
    {
        $this->innerService->unassignRoleFromUserGroup($role, $userGroup);
    }

    public function assignRoleToUser(Role $role, User $user, RoleLimitation $roleLimitation = null)
    {
        $this->innerService->assignRoleToUser($role, $user, $roleLimitation);
    }

    public function unassignRoleFromUser(Role $role, User $user)
    {
        $this->innerService->unassignRoleFromUser($role, $user);
    }

    public function loadRoleAssignment($roleAssignmentId)
    {
        $this->innerService->loadRoleAssignment($roleAssignmentId);
    }

    public function getRoleAssignments(Role $role)
    {
        $this->innerService->getRoleAssignments($role);
    }

    public function getRoleAssignmentsForUser(User $user, $inherited = false)
    {
        $this->innerService->getRoleAssignmentsForUser($user, $inherited);
    }

    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup)
    {
        $this->innerService->getRoleAssignmentsForUserGroup($userGroup);
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        $this->innerService->removeRoleAssignment($roleAssignment);
    }

    public function newRoleCreateStruct($name)
    {
        $this->innerService->newRoleCreateStruct($name);
    }

    public function newPolicyCreateStruct($module, $function)
    {
        $this->innerService->newPolicyCreateStruct($module, $function);
    }

    public function newPolicyUpdateStruct()
    {
        $this->innerService->newPolicyUpdateStruct();
    }

    public function newRoleUpdateStruct()
    {
        $this->innerService->newRoleUpdateStruct();
    }

    public function getLimitationType($identifier)
    {
        $this->innerService->getLimitationType($identifier);
    }

    public function getLimitationTypesByModuleFunction($module, $function)
    {
        $this->innerService->getLimitationTypesByModuleFunction($module, $function);
    }
}
