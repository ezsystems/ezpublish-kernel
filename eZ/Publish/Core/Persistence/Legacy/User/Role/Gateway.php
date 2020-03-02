<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * User Role Gateway.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    public const ROLE_SEQ = 'ezrole_id_seq';
    public const POLICY_SEQ = 'ezpolicy_id_seq';
    public const POLICY_LIMITATION_SEQ = 'ezpolicy_limitation_id_seq';

    /**
     * Create a new role.
     */
    abstract public function createRole(Role $role): Role;

    /**
     * Load a specified role by $roleId.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    abstract public function loadRole(int $roleId, int $status = Role::STATUS_DEFINED): array;

    /**
     * Load a specified role by $identifier.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function loadRoleByIdentifier(
        string $identifier,
        int $status = Role::STATUS_DEFINED
    ): array;

    /**
     * Load a role draft by the original role ID.
     *
     * @param int $roleId ID of the role the draft was created from.
     */
    abstract public function loadRoleDraftByRoleId(int $roleId): array;

    /**
     * Load all roles.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function loadRoles(int $status = Role::STATUS_DEFINED): array;

    /**
     * Load all roles associated with the given Content items.
     *
     * @param int[] $contentIds
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function loadRolesForContentObjects(
        array $contentIds,
        int $status = Role::STATUS_DEFINED
    ): array;

    /**
     * Load a role assignment for specified assignment ID.
     */
    abstract public function loadRoleAssignment(int $roleAssignmentId): array;

    /**
     * Load role assignment for specified User Group Content ID.
     */
    abstract public function loadRoleAssignmentsByGroupId(
        int $groupId,
        bool $inherited = false
    ): array;

    /**
     * Load a Role assignments for given Role ID.
     */
    abstract public function loadRoleAssignmentsByRoleId(int $roleId): array;

    /**
     * Return User Policies data associated with User.
     *
     * @return array
     */
    abstract public function loadPoliciesByUserId(int $userId): array;

    /**
     * Update role (draft).
     *
     * Will not throw anything if location id is invalid.
     */
    abstract public function updateRole(RoleUpdateStruct $role): void;

    /**
     * Delete the specified role (draft).
     * If it's not a draft, the role assignments will also be deleted.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function deleteRole(int $roleId, int $status = Role::STATUS_DEFINED): void;

    /**
     * Publish the specified role draft.
     * If the draft was created from an existing role, published version will take the original role ID.
     *
     * @param int|null $originalRoleId ID of role the draft was created from. Will be null
     *                                 if the role draft was completely new.
     */
    abstract public function publishRoleDraft(int $roleDraftId, ?int $originalRoleId = null): void;

    /**
     * Add a Policy to Role.
     */
    abstract public function addPolicy(int $roleId, Policy $policy): Policy;

    /**
     * Add Limitations to an existing Policy.
     *
     * @param array $limitations a map of Limitation identifiers to their raw values
     */
    abstract public function addPolicyLimitations(int $policyId, array $limitations): void;

    /**
     * Remove a Policy from Role.
     */
    abstract public function removePolicy(int $policyId): void;

    /**
     * Remove a Policy from Role.
     */
    abstract public function removePolicyLimitations(int $policyId): void;
}
