<?php

/**
 * RoleService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
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
 * RoleService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class RoleService implements RoleServiceInterface
{
    /** @var \eZ\Publish\API\Repository\RoleService */
    protected $service;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\RoleService $service
     */
    public function __construct(
        RoleServiceInterface $service
    ) {
        $this->service = $service;
    }

    public function createRole(RoleCreateStruct $roleCreateStruct)
    {
        return $this->service->createRole($roleCreateStruct);
    }

    public function createRoleDraft(Role $role)
    {
        return $this->service->createRoleDraft($role);
    }

    public function loadRoleDraft($id)
    {
        return $this->service->loadRoleDraft($id);
    }

    public function loadRoleDraftByRoleId($roleId)
    {
        return $this->service->loadRoleDraftByRoleId($roleId);
    }

    public function updateRoleDraft(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        return $this->service->updateRoleDraft($roleDraft, $roleUpdateStruct);
    }

    public function addPolicyByRoleDraft(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct)
    {
        return $this->service->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
    }

    public function removePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policyDraft)
    {
        return $this->service->removePolicyByRoleDraft($roleDraft, $policyDraft);
    }

    public function updatePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        return $this->service->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
    }

    public function deleteRoleDraft(RoleDraft $roleDraft)
    {
        return $this->service->deleteRoleDraft($roleDraft);
    }

    public function publishRoleDraft(RoleDraft $roleDraft)
    {
        return $this->service->publishRoleDraft($roleDraft);
    }

    public function updateRole(Role $role, RoleUpdateStruct $roleUpdateStruct)
    {
        return $this->service->updateRole($role, $roleUpdateStruct);
    }

    public function addPolicy(Role $role, PolicyCreateStruct $policyCreateStruct)
    {
        return $this->service->addPolicy($role, $policyCreateStruct);
    }

    public function deletePolicy(Policy $policy)
    {
        return $this->service->deletePolicy($policy);
    }

    public function updatePolicy(Policy $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        return $this->service->updatePolicy($policy, $policyUpdateStruct);
    }

    public function loadRole($id)
    {
        return $this->service->loadRole($id);
    }

    public function loadRoleByIdentifier($identifier)
    {
        return $this->service->loadRoleByIdentifier($identifier);
    }

    public function loadRoles()
    {
        return $this->service->loadRoles();
    }

    public function deleteRole(Role $role)
    {
        return $this->service->deleteRole($role);
    }

    public function loadPoliciesByUserId($userId)
    {
        return $this->service->loadPoliciesByUserId($userId);
    }

    public function assignRoleToUserGroup(Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null)
    {
        return $this->service->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
    }

    public function unassignRoleFromUserGroup(Role $role, UserGroup $userGroup)
    {
        return $this->service->unassignRoleFromUserGroup($role, $userGroup);
    }

    public function assignRoleToUser(Role $role, User $user, RoleLimitation $roleLimitation = null)
    {
        return $this->service->assignRoleToUser($role, $user, $roleLimitation);
    }

    public function unassignRoleFromUser(Role $role, User $user)
    {
        return $this->service->unassignRoleFromUser($role, $user);
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        return $this->service->removeRoleAssignment($roleAssignment);
    }

    public function loadRoleAssignment($roleAssignmentId)
    {
        return $this->service->loadRoleAssignment($roleAssignmentId);
    }

    public function getRoleAssignments(Role $role)
    {
        return $this->service->getRoleAssignments($role);
    }

    public function getRoleAssignmentsForUser(User $user, $inherited = false)
    {
        return $this->service->getRoleAssignmentsForUser($user, $inherited);
    }

    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup)
    {
        return $this->service->getRoleAssignmentsForUserGroup($userGroup);
    }

    public function newRoleCreateStruct($name)
    {
        return $this->service->newRoleCreateStruct($name);
    }

    public function newPolicyCreateStruct($module, $function)
    {
        return $this->service->newPolicyCreateStruct($module, $function);
    }

    public function newPolicyUpdateStruct()
    {
        return $this->service->newPolicyUpdateStruct();
    }

    public function newRoleUpdateStruct()
    {
        return $this->service->newRoleUpdateStruct();
    }

    public function getLimitationType($identifier)
    {
        return $this->service->getLimitationType($identifier);
    }

    public function getLimitationTypesByModuleFunction($module, $function)
    {
        return $this->service->getLimitationTypesByModuleFunction($module, $function);
    }
}
