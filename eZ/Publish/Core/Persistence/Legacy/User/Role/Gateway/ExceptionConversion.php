<?php

/**
 * File containing the ContentTypeGateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;

use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

/**
 * Base class for content type gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Create new role.
     *
     * @param Role $role
     *
     * @return Role
     */
    public function createRole(Role $role)
    {
        try {
            return $this->innerGateway->createRole($role);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads a specified role by $roleId.
     *
     * @param mixed $roleId
     *
     * @return array
     */
    public function loadRole($roleId)
    {
        try {
            return $this->innerGateway->loadRole($roleId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads a specified role by $identifier.
     *
     * @param string $identifier
     *
     * @return array
     */
    public function loadRoleByIdentifier($identifier)
    {
        try {
            return $this->innerGateway->loadRoleByIdentifier($identifier);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads all roles.
     *
     * @return array
     */
    public function loadRoles()
    {
        try {
            return $this->innerGateway->loadRoles();
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads all roles associated with the given content objects.
     *
     * @param array $contentIds
     *
     * @return array
     */
    public function loadRolesForContentObjects($contentIds)
    {
        try {
            return $this->innerGateway->loadRolesForContentObjects($contentIds);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads role assignments for specified content ID.
     *
     * @param mixed $groupId
     * @param bool $inherited
     *
     * @return array
     */
    public function loadRoleAssignmentsByGroupId($groupId, $inherited = false)
    {
        try {
            return $this->innerGateway->loadRoleAssignmentsByGroupId($groupId, $inherited);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads role assignments for given role ID.
     *
     * @param mixed $roleId
     *
     * @return array
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        try {
            return $this->innerGateway->loadRoleAssignmentsByRoleId($roleId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Returns the user policies associated with the user.
     *
     * @param mixed $userId
     *
     * @return UserPolicy[]
     */
    public function loadPoliciesByUserId($userId)
    {
        try {
            return $this->innerGateway->loadPoliciesByUserId($userId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Update role.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param RoleUpdateStruct $role
     */
    public function updateRole(RoleUpdateStruct $role)
    {
        try {
            return $this->innerGateway->updateRole($role);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Delete the specified role including all of its assignments.
     *
     * @param mixed $roleId
     */
    public function deleteRole($roleId)
    {
        try {
            return $this->innerGateway->deleteRole($roleId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Adds a policy to a role.
     *
     * @param mixed $roleId
     * @param Policy $policy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        try {
            return $this->innerGateway->addPolicy($roleId, $policy);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Adds limitations to an existing policy.
     *
     * @param int $policyId
     * @param array $limitations
     */
    public function addPolicyLimitations($policyId, array $limitations)
    {
        try {
            return $this->innerGateway->addPolicyLimitations($policyId, $limitations);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     */
    public function removePolicy($policyId)
    {
        try {
            return $this->innerGateway->removePolicy($policyId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     */
    public function removePolicyLimitations($policyId)
    {
        try {
            return $this->innerGateway->removePolicyLimitations($policyId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
