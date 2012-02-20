<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct,
    eZ\Publish\SPI\Persistence\User\Policy,
    eZ\Publish\SPI\Persistence\User\Role;

/**
 * Base class for content type gateways.
 */
abstract class Gateway
{
    /**
     * Create new role
     *
     * @param Role $role
     * @return Role
     */
    abstract public function createRole( Role $role );

    /**
     * Load a specified role by id
     *
     * @param mixed $roleId
     * @return array
     */
    abstract public function loadRole( $roleId );

    /**
     * Load all roles
     *
     * @return array
     */
    abstract public function loadRoles();

    /**
     * Load all roles associated with the given content objects
     *
     * @param array $contentIds
     * @return array
     */
    abstract public function loadRolesForContentObjects( $contentIds );

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return UserPolicy[]
     */
    abstract public function loadPoliciesByUserId( $userId );

    /**
     * Update role
     *
     * @param RoleUpdateStruct $role
     */
    abstract public function updateRole( RoleUpdateStruct $role );

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    abstract public function deleteRole( $roleId );

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param Policy $policy
     * @return void
     */
    abstract public function addPolicy( $roleId, Policy $policy );

    /**
     * Add limitations to an existing policy
     *
     * @param int $policyId
     * @param array $limitations
     * @return void
     */
    abstract public function addPolicyLimitations( $policyId, array $limitations );

    /**
     * Removes a policy from a role
     *
     * @param mixed $policyId
     * @return void
     */
    abstract public function removePolicy( $policyId );

    /**
     * Removes a policy from a role
     *
     * @param mixed $policyId
     * @return void
     */
    abstract public function removePolicyLimitations( $policyId );
}
