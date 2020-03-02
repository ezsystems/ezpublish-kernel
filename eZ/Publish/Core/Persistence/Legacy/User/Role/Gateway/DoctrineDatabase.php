<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * User Role gateway implementation using the Doctrine database.
 *
 * @internal Gateway implementation is considered internal. Use Persistence User Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\User\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $handler;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Construct from database handler.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
        $this->connection = $handler->getConnection();
        $this->dbPlatform = $this->connection->getDatabasePlatform();
    }

    /**
     * Create new role.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return Role
     */
    public function createRole(Role $role)
    {
        // Role original ID is set when creating a draft from an existing role
        if ($role->status === Role::STATUS_DRAFT && $role->id) {
            $roleOriginalId = $role->id;
        } elseif ($role->status === Role::STATUS_DRAFT) {
            // Not using a constant here as this is legacy storage engine specific.
            // -1 means "Newly created role".
            $roleOriginalId = -1;
        } else {
            // Role::STATUS_DEFINED value is 0, which is the expected value for version column for this status.
            $roleOriginalId = Role::STATUS_DEFINED;
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('ezrole')
            ->values(
                [
                    'is_new' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'name' => $query->createPositionalParameter(
                        $role->identifier,
                        ParameterType::STRING
                    ),
                    'value' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'version' => $query->createPositionalParameter(
                    // Column name "version" is misleading here as it stores originalId when creating a draft from an existing role.
                    // But hey, this is legacy! :-)
                        $roleOriginalId,
                        ParameterType::STRING
                    ),
                ]
            );
        $query->execute();

        if (!isset($role->id) || (int)$role->id < 1 || $role->status === Role::STATUS_DRAFT) {
            $role->id = (int)$this->connection->lastInsertId(self::ROLE_SEQ);
        }

        $role->originalId = $roleOriginalId;

        return $role;
    }

    private function getLoadRoleQueryBuilder(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'r.id AS ezrole_id',
                'r.name AS ezrole_name',
                'r.version AS ezrole_version',
                'p.id AS ezpolicy_id',
                'p.function_name AS ezpolicy_function_name',
                'p.module_name AS ezpolicy_module_name',
                'p.original_id AS ezpolicy_original_id',
                'l.identifier AS ezpolicy_limitation_identifier',
                'v.value AS ezpolicy_limitation_value_value'
            )
            ->from('ezrole', 'r')
            ->leftJoin('r', 'ezpolicy', 'p', 'p.role_id = r.id')
            ->leftJoin('p', 'ezpolicy_limitation', 'l', 'l.policy_id = p.id')
            ->leftJoin('l', 'ezpolicy_limitation_value', 'v', 'v.limitation_id = l.id');

        return $query;
    }

    /**
     * Loads a specified role by id.
     *
     * @param mixed $roleId
     * @param int $status One of Role::STATUS_DEFINED|Role::STATUS_DRAFT
     *
     * @return array
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $query = $this->getLoadRoleQueryBuilder();
        $query
            ->where(
                $query->expr()->eq(
                    'r.id',
                    $query->createPositionalParameter($roleId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $this->buildRoleDraftQueryConstraint($status, $query)
            );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->getLoadRoleQueryBuilder();
        $query
            ->where(
                $query->expr()->eq(
                    'r.name',
                    $query->createPositionalParameter($identifier, ParameterType::STRING)
                )
            )
            ->andWhere(
                $this->buildRoleDraftQueryConstraint($status, $query)
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->getLoadRoleQueryBuilder();
        $query
            ->where(
                $query->expr()->eq(
                // Column name "version" is misleading as it stores originalId when creating a draft from an existing role.
                // But hey, this is legacy! :-)
                    'r.version',
                    $query->createPositionalParameter($roleId, ParameterType::STRING)
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->getLoadRoleQueryBuilder();
        $query->where(
            $this->buildRoleDraftQueryConstraint($status, $query)
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select(
                'ur.contentobject_id AS ezuser_role_contentobject_id',
                'r.id AS ezrole_id',
                'r.name AS ezrole_name',
                'r.version AS ezrole_version',
                'p.id AS ezpolicy_id',
                'p.function_name AS ezpolicy_function_name',
                'p.module_name AS ezpolicy_module_name',
                'p.original_id AS ezpolicy_original_id',
                'l.identifier AS ezpolicy_limitation_identifier',
                'v.value AS ezpolicy_limitation_value_value'
            )
            ->from('ezuser_role', 'urs')
            ->leftJoin(
                'urs',
                'ezrole',
                'r',
                $expr->eq(
                // for drafts the "version" column contains the original role ID...
                    $status === Role::STATUS_DEFINED ? 'r.id' : 'r.version',
                    'urs.role_id'
                )
            )
            ->leftJoin('r', 'ezuser_role', 'ur', 'ur.role_id = r.id')
            ->leftJoin('r', 'ezpolicy', 'p', 'p.role_id = r.id')
            ->leftJoin('p', 'ezpolicy_limitation', 'l', 'l.policy_id = p.id')
            ->leftJoin('l', 'ezpolicy_limitation_value', 'v', 'v.limitation_id = l.id')
            ->where(
                $expr->in(
                    'urs.contentobject_id',
                    $query->createPositionalParameter($contentIds, Connection::PARAM_INT_ARRAY)
                )
            );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'id',
            'contentobject_id',
            'limit_identifier',
            'limit_value',
            'role_id'
        )->from(
            'ezuser_role'
        )->where(
            $query->expr()->eq(
                'id',
                $query->createPositionalParameter($roleAssignmentId, ParameterType::INTEGER)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'id',
            'contentobject_id',
            'limit_identifier',
            'limit_value',
            'role_id'
        )->from(
            'ezuser_role'
        );

        if ($inherited) {
            $groupIds = $this->fetchUserGroups($groupId);
            $groupIds[] = $groupId;
            $query->where(
                $query->expr()->in(
                    'contentobject_id',
                    $groupIds
                )
            );
        } else {
            $query->where(
                $query->expr()->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($groupId, ParameterType::INTEGER)
                )
            );
        }

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'id',
            'contentobject_id',
            'limit_identifier',
            'limit_value',
            'role_id'
        )->from(
            'ezuser_role'
        )->where(
            $query->expr()->eq(
                'role_id',
                $query->createPositionalParameter($roleId, ParameterType::INTEGER)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
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
        $groupIds = $this->fetchUserGroups($userId);
        $groupIds[] = $userId;

        return $this->loadRolesForContentObjects($groupIds);
    }

    /**
     * Fetch all group IDs the user belongs to.
     *
     * This method will return Content ids of all ancestor Locations for the given $userId.
     * Note that not all of these might be used as user groups,
     * but we will need to check all of them.
     *
     * @param int $userId
     *
     * @return array
     */
    private function fetchUserGroups($userId)
    {
        $nodeIDs = $this->getAncestorLocationIdsForUser($userId);

        if (empty($nodeIDs)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->select('c.id')
            ->from('ezcontentobject_tree', 't')
            ->innerJoin('t', 'ezcontentobject', 'c', 'c.id = t.contentobject_id')
            ->where(
                $query->expr()->in(
                    't.node_id',
                    $nodeIDs
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::COLUMN);
    }

    /**
     * Update role (draft).
     *
     * Will not throw anything if location id is invalid.
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
     *
     * @return array
     */
    public function updateRole(RoleUpdateStruct $role)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezrole')
            ->set(
                'name',
                $query->createPositionalParameter($role->identifier, ParameterType::STRING)
            )
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($role->id, ParameterType::INTEGER)
                )
            );
        $query->execute();
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
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->delete('ezrole')
            ->where(
                $expr->eq(
                    'id',
                    $query->createPositionalParameter($roleId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $this->buildRoleDraftQueryConstraint($status, $query, 'ezrole')
            );

        if ($status !== Role::STATUS_DRAFT) {
            $this->deleteRoleAssignments($roleId);
        }
        $query->execute();
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
        $this->markRoleAsPublished($roleDraftId, $originalRoleId);
        $this->publishRolePolicies($roleDraftId, $originalRoleId);
    }

    /**
     * Adds a policy to a role.
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return Policy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('ezpolicy')
            ->values(
                [
                    'function_name' => $query->createPositionalParameter(
                        $policy->function,
                        ParameterType::STRING
                    ),
                    'module_name' => $query->createPositionalParameter(
                        $policy->module,
                        ParameterType::STRING
                    ),
                    'original_id' => $query->createPositionalParameter(
                        $policy->originalId ?? 0,
                        ParameterType::INTEGER
                    ),
                    'role_id' => $query->createPositionalParameter($roleId, ParameterType::INTEGER),
                ]
            );
        $query->execute();

        $policy->id = (int)$this->connection->lastInsertId(self::POLICY_SEQ);
        $policy->roleId = $roleId;

        // Handle the only valid non-array value "*" by not inserting
        // anything. Still has not been documented by eZ Systems. So we
        // assume this is the right way to handle it.
        if (is_array($policy->limitations)) {
            $this->addPolicyLimitations($policy->id, $policy->limitations);
        }

        return $policy;
    }

    /**
     * Adds limitations to an existing policy.
     *
     * @param int $policyId
     * @param array $limitations
     */
    public function addPolicyLimitations($policyId, array $limitations)
    {
        foreach ($limitations as $identifier => $values) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert('ezpolicy_limitation')
                ->values(
                    [
                        'identifier' => $query->createPositionalParameter(
                            $identifier,
                            ParameterType::STRING
                        ),
                        'policy_id' => $query->createPositionalParameter(
                            $policyId,
                            ParameterType::INTEGER
                        ),
                    ]
                );
            $query->execute();

            $limitationId = (int)$this->connection->lastInsertId(self::POLICY_LIMITATION_SEQ);

            foreach ($values as $value) {
                $query = $this->connection->createQueryBuilder();
                $query
                    ->insert('ezpolicy_limitation_value')
                    ->values(
                        [
                            'value' => $query->createPositionalParameter(
                                $value,
                                ParameterType::STRING
                            ),
                            'limitation_id' => $query->createPositionalParameter(
                                $limitationId,
                                ParameterType::INTEGER
                            ),
                        ]
                    );
                $query->execute();
            }
        }
    }

    /**
     * Removes a policy from a role.
     *
     * @param mixed $policyId
     */
    public function removePolicy($policyId)
    {
        $this->removePolicyLimitations($policyId);

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezpolicy')
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($policyId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    /**
     * @param int[] $limitationIds
     */
    private function deletePolicyLimitations(array $limitationIds): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezpolicy_limitation')
            ->where(
                $query->expr()->in(
                    'id',
                    $query->createPositionalParameter(
                        $limitationIds,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );
        $query->execute();
    }

    /**
     * @param int[] $limitationValueIds
     */
    private function deletePolicyLimitationValues(array $limitationValueIds): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezpolicy_limitation_value')
            ->where(
                $query->expr()->in(
                    'id',
                    $query->createPositionalParameter(
                        $limitationValueIds,
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );
        $query->execute();
    }

    private function loadPolicyLimitationValues(int $policyId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'l.id AS ezpolicy_limitation_id',
                'v.id AS ezpolicy_limitation_value_id'
            )
            ->from('ezpolicy', 'p')
            ->leftJoin('p', 'ezpolicy_limitation', 'l', 'l.policy_id = p.id')
            ->leftJoin('l', 'ezpolicy_limitation_value', 'v', 'v.limitation_id = l.id')
            ->where(
                $query->expr()->eq(
                    'p.id',
                    $query->createPositionalParameter($policyId, ParameterType::INTEGER)
                )
            );

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Remove all limitations for a policy.
     *
     * @param mixed $policyId
     */
    public function removePolicyLimitations($policyId)
    {
        $limitationValues = $this->loadPolicyLimitationValues($policyId);

        $limitationIds = array_map(
            'intval',
            array_column($limitationValues, 'ezpolicy_limitation_id')
        );
        $limitationValueIds = array_map(
            'intval',
            array_column($limitationValues, 'ezpolicy_limitation_value_id')
        );

        if (!empty($limitationValueIds)) {
            $this->deletePolicyLimitationValues($limitationValueIds);
        }

        if (!empty($limitationIds)) {
            $this->deletePolicyLimitations($limitationIds);
        }
    }

    /**
     * Delete Role assignments to Users.
     */
    private function deleteRoleAssignments(int $roleId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezuser_role')
            ->where(
                $query->expr()->eq(
                    'role_id',
                    $query->createPositionalParameter($roleId, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    /**
     * Load all Ancestor Location IDs of the given User Location.
     *
     * @param int $userId
     *
     * @return int[]
     */
    private function getAncestorLocationIdsForUser(int $userId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('t.path_string')
            ->from('ezcontentobject_tree', 't')
            ->where(
                $query->expr()->eq(
                    't.contentobject_id',
                    $query->createPositionalParameter($userId, ParameterType::STRING)
                )
            );

        $paths = $query->execute()->fetchAll(FetchMode::COLUMN);
        $nodeIds = array_unique(
            array_reduce(
                array_map(
                    function ($pathString) {
                        return array_filter(explode('/', $pathString));
                    },
                    $paths
                ),
                'array_merge_recursive',
                []
            )
        );

        return array_map('intval', $nodeIds);
    }

    private function buildRoleDraftQueryConstraint(
        int $status,
        QueryBuilder $query,
        string $columnAlias = 'r'
    ): string {
        if ($status === Role::STATUS_DEFINED) {
            $draftCondition = $query->expr()->eq(
                "{$columnAlias}.version",
                $query->createPositionalParameter($status, ParameterType::INTEGER)
            );
        } else {
            // version stores original Role ID when Role is a draft...
            $draftCondition = $query->expr()->neq(
                "{$columnAlias}.version",
                $query->createPositionalParameter(Role::STATUS_DEFINED, ParameterType::INTEGER)
            );
        }

        return $draftCondition;
    }

    private function markRoleAsPublished(int $roleDraftId, ?int $originalRoleId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezrole')
            ->set(
                'version',
                $query->createPositionalParameter(Role::STATUS_DEFINED, ParameterType::INTEGER)
            );
        // Draft was created from an existing role, so published role must get the original ID.
        if ($originalRoleId !== null) {
            $query->set(
                'id',
                $query->createPositionalParameter($originalRoleId, ParameterType::INTEGER)
            );
        }

        $query->where(
            $query->expr()->eq(
                'id',
                $query->createPositionalParameter($roleDraftId, ParameterType::INTEGER)
            )
        );
        $query->execute();
    }

    private function publishRolePolicies(int $roleDraftId, ?int $originalRoleId): void
    {
        $policyQuery = $this->connection->createQueryBuilder();
        $policyQuery
            ->update('ezpolicy')
            ->set(
                'original_id',
                $policyQuery->createPositionalParameter(0, ParameterType::INTEGER)
            );
        // Draft was created from an existing role, so published policies must get the original role ID.
        if ($originalRoleId !== null) {
            $policyQuery->set(
                'role_id',
                $policyQuery->createPositionalParameter($originalRoleId, ParameterType::INTEGER)
            );
        }

        $policyQuery->where(
            $policyQuery->expr()->eq(
                'role_id',
                $policyQuery->createPositionalParameter($roleDraftId, ParameterType::INTEGER)
            )
        );
        $policyQuery->execute();
    }
}
