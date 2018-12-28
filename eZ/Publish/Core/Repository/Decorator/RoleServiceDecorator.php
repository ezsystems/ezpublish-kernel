<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

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
    /**
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\RoleService $service
     */
    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createRole(RoleCreateStruct $roleCreateStruct)
    {
        return $this->service->createRole($roleCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoleDraft(Role $role)
    {
        return $this->service->createRoleDraft($role);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleDraft($id)
    {
        return $this->service->loadRoleDraft($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        return $this->service->loadRoleDraftByRoleId($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRoleDraft(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        return $this->service->updateRoleDraft($roleDraft, $roleUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicyByRoleDraft(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct)
    {
        return $this->service->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function removePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policyDraft)
    {
        return $this->service->removePolicyByRoleDraft($roleDraft, $policyDraft);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        return $this->service->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRoleDraft(RoleDraft $roleDraft)
    {
        return $this->service->deleteRoleDraft($roleDraft);
    }

    /**
     * {@inheritdoc}
     */
    public function publishRoleDraft(RoleDraft $roleDraft)
    {
        return $this->service->publishRoleDraft($roleDraft);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRole(Role $role, RoleUpdateStruct $roleUpdateStruct)
    {
        return $this->service->updateRole($role, $roleUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicy(Role $role, PolicyCreateStruct $policyCreateStruct)
    {
        return $this->service->addPolicy($role, $policyCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deletePolicy(Policy $policy)
    {
        return $this->service->deletePolicy($policy);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePolicy(Policy $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        return $this->service->updatePolicy($policy, $policyUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRole($id)
    {
        return $this->service->loadRole($id);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleByIdentifier($identifier)
    {
        return $this->service->loadRoleByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoles()
    {
        return $this->service->loadRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRole(Role $role)
    {
        return $this->service->deleteRole($role);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPoliciesByUserId($userId)
    {
        return $this->service->loadPoliciesByUserId($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function assignRoleToUserGroup(Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null)
    {
        return $this->service->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignRoleFromUserGroup(Role $role, UserGroup $userGroup)
    {
        return $this->service->unassignRoleFromUserGroup($role, $userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function assignRoleToUser(Role $role, User $user, RoleLimitation $roleLimitation = null)
    {
        return $this->service->assignRoleToUser($role, $user, $roleLimitation);
    }

    /**
     * {@inheritdoc}
     */
    public function unassignRoleFromUser(Role $role, User $user)
    {
        return $this->service->unassignRoleFromUser($role, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        return $this->service->loadRoleAssignment($roleAssignmentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleAssignments(Role $role)
    {
        return $this->service->getRoleAssignments($role);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleAssignmentsForUser(User $user, $inherited = false)
    {
        return $this->service->getRoleAssignmentsForUser($user, $inherited);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup)
    {
        return $this->service->getRoleAssignmentsForUserGroup($userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        return $this->service->removeRoleAssignment($roleAssignment);
    }

    /**
     * {@inheritdoc}
     */
    public function newRoleCreateStruct($name)
    {
        return $this->service->newRoleCreateStruct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function newPolicyCreateStruct($module, $function)
    {
        return $this->service->newPolicyCreateStruct($module, $function);
    }

    /**
     * {@inheritdoc}
     */
    public function newPolicyUpdateStruct()
    {
        return $this->service->newPolicyUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newRoleUpdateStruct()
    {
        return $this->service->newRoleUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitationType($identifier)
    {
        return $this->service->getLimitationType($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getLimitationTypesByModuleFunction($module, $function)
    {
        return $this->service->getLimitationTypesByModuleFunction($module, $function);
    }
}
