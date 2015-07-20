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
     *
     * @return array
     */
    abstract public function loadRole($roleId);

    /**
     * Loads a specified role by $identifier.
     *
     * @param string $identifier
     *
     * @return array
     */
    abstract public function loadRoleByIdentifier($identifier);

    /**
     * Loads all roles.
     *
     * @return array
     */
    abstract public function loadRoles();

    /**
     * Loads all roles associated with the given content objects.
     *
     * @param array $contentIds
     *
     * @return array
     */
    abstract public function loadRolesForContentObjects($contentIds);

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
     * Update role.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param RoleUpdateStruct $role
     */
    abstract public function updateRole(RoleUpdateStruct $role);

    /**
     * Delete the specified role including all of its assignments.
     *
     * @param mixed $roleId
     */
    abstract public function deleteRole($roleId);

    /**
     * Adds a policy to a role.
     *
     * @param mixed $roleId
     * @param Policy $policy
     */
    abstract public function addPolicy($roleId, Policy $policy);

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
     */
    abstract public function removePolicy($policyId);

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     */
    abstract public function removePolicyLimitations($policyId);
}
