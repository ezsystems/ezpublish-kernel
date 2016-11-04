<?php

/**
 * File containing the ContentTypeGateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        try {
            return $this->innerGateway->loadRole($roleId, $status);
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
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED)
    {
        try {
            return $this->innerGateway->loadRoleByIdentifier($identifier, $status);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads a role draft by the original role ID.
     *
     * @param mixed $roleId ID of the role the draft was created from.
     *
     * @return array
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        try {
            return $this->innerGateway->loadRoleDraftByRoleId($roleId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Loads all roles.
     *
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    public function loadRoles($status = Role::STATUS_DEFINED)
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
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    public function loadRolesForContentObjects($contentIds, $status = Role::STATUS_DEFINED)
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
     * Loads role assignment for specified assignment ID.
     *
     * @param mixed $roleAssignmentId
     *
     * @return array
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        try {
            return $this->innerGateway->loadRoleAssignment($roleAssignmentId);
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
     * Update role (draft).
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
     * Delete the specified role (draft).
     * If it's not a draft, the role assignments will also be deleted.
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        try {
            return $this->innerGateway->deleteRole($roleId, $status);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * Publish the specified role draft.
     * If the draft was created from an existing role, published version will take the original role ID.
     *
     * @param mixed $roleDraftId
     * @param mixed|null $originalRoleId ID of role the draft was created from. Will be null if the role draft was completely new.
     */
    public function publishRoleDraft($roleDraftId, $originalRoleId = null)
    {
        try {
            return $this->innerGateway->publishRoleDraft($roleDraftId, $originalRoleId);
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
