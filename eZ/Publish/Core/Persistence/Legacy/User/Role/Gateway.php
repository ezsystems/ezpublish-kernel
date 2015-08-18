<?php

/**
 * File containing the ContentTypeGateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role;

use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * Base class for content type gateways.
 */
abstract class Gateway
{
    /**
     * Create new role.
     *
     * @param Role $role
     *
     * @return Role
     */
    abstract public function createRole(Role $role);

    /**
     * Loads a specified role by $roleId.
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    abstract public function loadRole($roleId, $status = Role::STATUS_DEFINED);

    /**
     * Loads a specified role by $identifier.
     *
     * @param string $identifier
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    abstract public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED);

    /**
     * Loads all roles.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    abstract public function loadRoles($status = Role::STATUS_DEFINED);

    /**
     * Loads all roles associated with the given content objects.
     *
     * @param array $contentIds
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    abstract public function loadRolesForContentObjects($contentIds, $status = Role::STATUS_DEFINED);

    /**
     * Loads role assignments for specified content ID.
     *
     * @param mixed $groupId
     * @param bool $inherited
     *
     * @return array
     */
    abstract public function loadRoleAssignmentsByGroupId($groupId, $inherited = false);

    /**
     * Loads role assignments for given role ID.
     *
     * @param mixed $roleId
     *
     * @return array
     */
    abstract public function loadRoleAssignmentsByRoleId($roleId);

    /**
     * Returns the user policies associated with the user.
     *
     * @param mixed $userId
     *
     * @return UserPolicy[]
     */
    abstract public function loadPoliciesByUserId($userId);

    /**
     * Update role (draft).
     *
     * Will not throw anything if location id is invalid.
     *
     * @param RoleUpdateStruct $role
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function updateRole(RoleUpdateStruct $role, $status = Role::STATUS_DEFINED);

    /**
     * Delete the specified role (draft).
     * If it's not a draft, the role assignments will also be deleted.
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function deleteRole($roleId, $status = Role::STATUS_DEFINED);

    /**
     * Publish the specified role draft.
     *
     * @param mixed $roleId
     */
    abstract public function publishRoleDraft($roleId);

    /**
     * Adds a policy to a role.
     *
     * @param mixed $roleId
     * @param Policy $policy
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function addPolicy($roleId, Policy $policy, $status = Role::STATUS_DEFINED);

    /**
     * Adds limitations to an existing policy.
     *
     * @param int $policyId
     * @param array $limitations
     */
    abstract public function addPolicyLimitations($policyId, array $limitations);

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    abstract public function removePolicy($policyId, $status = Role::STATUS_DEFINED);

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     */
    abstract public function removePolicyLimitations($policyId);
}
