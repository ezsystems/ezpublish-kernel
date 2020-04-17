<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\RoleCopyStruct;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Limitation\Type;

abstract class RoleServiceDecorator implements RoleService
{
    /** @var \eZ\Publish\API\Repository\RoleService */
    protected $innerService;

    public function __construct(RoleService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createRole(RoleCreateStruct $roleCreateStruct): RoleDraft
    {
        return $this->innerService->createRole($roleCreateStruct);
    }

    public function createRoleDraft(Role $role): RoleDraft
    {
        return $this->innerService->createRoleDraft($role);
    }

    public function copyRole(
        Role $role,
        RoleCopyStruct $roleCopyStruct
    ): Role {
        return $this->innerService->copyRole($role, $roleCopyStruct);
    }

    public function loadRoleDraft(int $id): RoleDraft
    {
        return $this->innerService->loadRoleDraft($id);
    }

    public function loadRoleDraftByRoleId(int $roleId): RoleDraft
    {
        return $this->innerService->loadRoleDraftByRoleId($roleId);
    }

    public function updateRoleDraft(
        RoleDraft $roleDraft,
        RoleUpdateStruct $roleUpdateStruct
    ): RoleDraft {
        return $this->innerService->updateRoleDraft($roleDraft, $roleUpdateStruct);
    }

    public function addPolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyCreateStruct $policyCreateStruct
    ): RoleDraft {
        return $this->innerService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
    }

    public function removePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policyDraft
    ): RoleDraft {
        return $this->innerService->removePolicyByRoleDraft($roleDraft, $policyDraft);
    }

    public function updatePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ): PolicyDraft {
        return $this->innerService->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
    }

    public function deleteRoleDraft(RoleDraft $roleDraft): void
    {
        $this->innerService->deleteRoleDraft($roleDraft);
    }

    public function publishRoleDraft(RoleDraft $roleDraft): void
    {
        $this->innerService->publishRoleDraft($roleDraft);
    }

    public function loadRole(int $id): Role
    {
        return $this->innerService->loadRole($id);
    }

    public function loadRoleByIdentifier(string $identifier): Role
    {
        return $this->innerService->loadRoleByIdentifier($identifier);
    }

    public function loadRoles(): iterable
    {
        return $this->innerService->loadRoles();
    }

    public function deleteRole(Role $role): void
    {
        $this->innerService->deleteRole($role);
    }

    public function assignRoleToUserGroup(
        Role $role,
        UserGroup $userGroup,
        RoleLimitation $roleLimitation = null
    ): void {
        $this->innerService->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
    }

    public function assignRoleToUser(
        Role $role,
        User $user,
        RoleLimitation $roleLimitation = null
    ): void {
        $this->innerService->assignRoleToUser($role, $user, $roleLimitation);
    }

    public function loadRoleAssignment(int $roleAssignmentId): RoleAssignment
    {
        return $this->innerService->loadRoleAssignment($roleAssignmentId);
    }

    public function getRoleAssignments(Role $role): iterable
    {
        return $this->innerService->getRoleAssignments($role);
    }

    public function getRoleAssignmentsForUser(
        User $user,
        bool $inherited = false
    ): iterable {
        return $this->innerService->getRoleAssignmentsForUser($user, $inherited);
    }

    public function getRoleAssignmentsForUserGroup(UserGroup $userGroup): iterable
    {
        return $this->innerService->getRoleAssignmentsForUserGroup($userGroup);
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment): void
    {
        $this->innerService->removeRoleAssignment($roleAssignment);
    }

    public function newRoleCreateStruct(string $name): RoleCreateStruct
    {
        return $this->innerService->newRoleCreateStruct($name);
    }

    public function newRoleCopyStruct(string $name): RoleCopyStruct
    {
        return $this->innerService->newRoleCopyStruct($name);
    }

    public function newPolicyCreateStruct(
        string $module,
        string $function
    ): PolicyCreateStruct {
        return $this->innerService->newPolicyCreateStruct($module, $function);
    }

    public function newPolicyUpdateStruct(): PolicyUpdateStruct
    {
        return $this->innerService->newPolicyUpdateStruct();
    }

    public function newRoleUpdateStruct(): RoleUpdateStruct
    {
        return $this->innerService->newRoleUpdateStruct();
    }

    public function getLimitationType(string $identifier): Type
    {
        return $this->innerService->getLimitationType($identifier);
    }

    public function getLimitationTypesByModuleFunction(
        string $module,
        string $function
    ): iterable {
        return $this->innerService->getLimitationTypesByModuleFunction($module, $function);
    }
}
