<?php

/**
 * File containing the DoctrineDatabase User Role Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;

use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * User Role gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /**
     * Construct from database handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     */
    public function __construct(DatabaseHandler $handler)
    {
        $this->handler = $handler;
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

        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('ezrole'))
            ->set(
                $this->handler->quoteColumn('id'),
                $this->handler->getAutoIncrementValue('ezrole', 'id')
            )->set(
                $this->handler->quoteColumn('is_new'),
                0
            )->set(
                $this->handler->quoteColumn('name'),
                $query->bindValue($role->identifier)
            )->set(
                $this->handler->quoteColumn('value'),
                0
            )->set(
                // Column name "version" is misleading here as it stores originalId when creating a draft from an existing role.
                // But hey, this is legacy! :-)
                $this->handler->quoteColumn('version'),
                $query->bindValue($roleOriginalId)
            );
        $query->prepare()->execute();

        if (!isset($role->id) || (int)$role->id < 1 || $role->status === Role::STATUS_DRAFT) {
            $role->id = $this->handler->lastInsertId(
                $this->handler->getSequenceName('ezrole', 'id')
            );
        }

        $role->originalId = $roleOriginalId;
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
        $query = $this->handler->createSelectQuery();
        if ($status === Role::STATUS_DEFINED) {
            $draftCondition = $query->expr->eq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($status, null, \PDO::PARAM_INT)
            );
        } else {
            $draftCondition = $query->expr->neq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue(Role::STATUS_DEFINED, null, \PDO::PARAM_INT)
            );
        }

        $query->select(
            $this->handler->aliasedColumn($query, 'id', 'ezrole'),
            $this->handler->aliasedColumn($query, 'name', 'ezrole'),
            $this->handler->aliasedColumn($query, 'version', 'ezrole'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'function_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'module_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'original_id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'identifier', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'value', 'ezpolicy_limitation_value')
        )->from(
            $this->handler->quoteTable('ezrole')
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezpolicy'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('id', 'ezrole'),
                    $query->bindValue($roleId, null, \PDO::PARAM_INT)
                ),
                $draftCondition
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        if ($status === Role::STATUS_DEFINED) {
            $roleVersionCondition = $query->expr->eq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($status, null, \PDO::PARAM_INT)
            );
        } else {
            $roleVersionCondition = $query->expr->neq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($status, null, \PDO::PARAM_INT)
            );
        }

        $query->select(
            $this->handler->aliasedColumn($query, 'id', 'ezrole'),
            $this->handler->aliasedColumn($query, 'name', 'ezrole'),
            $this->handler->aliasedColumn($query, 'version', 'ezrole'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'function_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'module_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'original_id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'identifier', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'value', 'ezpolicy_limitation_value')
        )->from(
            $this->handler->quoteTable('ezrole')
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy'),
                $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezpolicy'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('name', 'ezrole'),
                    $query->bindValue($identifier, null, \PDO::PARAM_STR)
                ),
                $roleVersionCondition
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn($query, 'id', 'ezrole'),
            $this->handler->aliasedColumn($query, 'name', 'ezrole'),
            $this->handler->aliasedColumn($query, 'version', 'ezrole'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'function_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'module_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'original_id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'identifier', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'value', 'ezpolicy_limitation_value')
        )->from(
            $this->handler->quoteTable('ezrole')
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezpolicy'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where(
            $query->expr->eq(
                // Column name "version" is misleading as it stores originalId when creating a draft from an existing role.
                // But hey, this is legacy! :-)
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($roleId, null, \PDO::PARAM_STR)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        if ($status === Role::STATUS_DEFINED) {
            $roleVersionCondition = $query->expr->eq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($status, null, \PDO::PARAM_INT)
            );
        } else {
            $roleVersionCondition = $query->expr->neq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $query->bindValue($status, null, \PDO::PARAM_INT)
            );
        }

        $query->select(
            $this->handler->aliasedColumn($query, 'id', 'ezrole'),
            $this->handler->aliasedColumn($query, 'name', 'ezrole'),
            $this->handler->aliasedColumn($query, 'version', 'ezrole'),
            $this->handler->aliasedColumn($query, 'contentobject_id', 'ezuser_role'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'function_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'module_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'original_id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'identifier', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'value', 'ezpolicy_limitation_value')
        )->from(
            $this->handler->quoteTable('ezrole')
        )->leftJoin(
            $this->handler->quoteTable('ezuser_role'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezuser_role'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezpolicy'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where($roleVersionCondition);

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        if ($status === Role::STATUS_DEFINED) {
            $roleIdCondition = $query->expr->eq(
                $this->handler->quoteColumn('id', 'ezrole'),
                $this->handler->quoteColumn('role_id', 'ezuser_role_search')
            );
        } else {
            $roleIdCondition = $query->expr->eq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $this->handler->quoteColumn('role_id', 'ezuser_role_search')
            );
        }

        $query->select(
            $this->handler->aliasedColumn($query, 'contentobject_id', 'ezuser_role'),
            $this->handler->aliasedColumn($query, 'id', 'ezrole'),
            $this->handler->aliasedColumn($query, 'name', 'ezrole'),
            $this->handler->aliasedColumn($query, 'version', 'ezrole'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'function_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'module_name', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'original_id', 'ezpolicy'),
            $this->handler->aliasedColumn($query, 'identifier', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'value', 'ezpolicy_limitation_value')
        )->from(
            $query->alias(
                $this->handler->quoteTable('ezuser_role'),
                $this->handler->quoteIdentifier('ezuser_role_search')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezrole'),
            $roleIdCondition
        )->leftJoin(
            $this->handler->quoteTable('ezuser_role'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezuser_role'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy'),
            $query->expr->eq(
                $this->handler->quoteColumn('role_id', 'ezpolicy'),
                $this->handler->quoteColumn('id', 'ezrole')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where(
            $query->expr->in(
                $this->handler->quoteColumn('contentobject_id', 'ezuser_role_search'),
                $contentIds
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('id'),
            $this->handler->quoteColumn('contentobject_id'),
            $this->handler->quoteColumn('limit_identifier'),
            $this->handler->quoteColumn('limit_value'),
            $this->handler->quoteColumn('role_id')
        )->from(
            $this->handler->quoteTable('ezuser_role')
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id'),
                $query->bindValue($roleAssignmentId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('id'),
            $this->handler->quoteColumn('contentobject_id'),
            $this->handler->quoteColumn('limit_identifier'),
            $this->handler->quoteColumn('limit_value'),
            $this->handler->quoteColumn('role_id')
        )->from(
            $this->handler->quoteTable('ezuser_role')
        );

        if ($inherited) {
            $groupIds = $this->fetchUserGroups($groupId);
            $groupIds[] = $groupId;
            $query->where(
                $query->expr->in(
                    $this->handler->quoteColumn('contentobject_id'),
                    $groupIds
                )
            );
        } else {
            $query->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id'),
                    $query->bindValue($groupId, null, \PDO::PARAM_INT)
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('id'),
            $this->handler->quoteColumn('contentobject_id'),
            $this->handler->quoteColumn('limit_identifier'),
            $this->handler->quoteColumn('limit_value'),
            $this->handler->quoteColumn('role_id')
        )->from(
            $this->handler->quoteTable('ezuser_role')
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('role_id'),
                $query->bindValue($roleId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
    protected function fetchUserGroups($userId)
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('path_string', 'ezcontentobject_tree')
        )->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                $query->bindValue($userId)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $paths = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $nodeIDs = array_unique(
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

        if (empty($nodeIDs)) {
            return [];
        }

        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn('id', 'ezcontentobject')
        )->from(
            $this->handler->quoteTable('ezcontentobject_tree')
        )->innerJoin(
            $this->handler->quoteTable('ezcontentobject'),
            $query->expr->eq(
                $this->handler->quoteColumn('id', 'ezcontentobject'),
                $this->handler->quoteColumn('contentobject_id', 'ezcontentobject_tree')
            )
        )->where(
            $query->expr->in(
                $this->handler->quoteColumn('node_id', 'ezcontentobject_tree'),
                $nodeIDs
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
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
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezrole'))
            ->set(
                $this->handler->quoteColumn('name'),
                $query->bindValue($role->identifier)
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($role->id, null, \PDO::PARAM_INT)
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        // Commented due to EZP-24698: Role update leads to NotFoundException
        // Should be fixed with PDO::MYSQL_ATTR_FOUND_ROWS instead
        /*if ($statement->rowCount() < 1) {
            throw new NotFoundException('role', $role->id);
        }*/
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
        $deleteRoleQuery = $this->handler->createDeleteQuery();
        if ($status !== Role::STATUS_DRAFT) {
            $deleteAssignmentsQuery = $this->handler->createDeleteQuery();
            $deleteAssignmentsQuery
                ->deleteFrom($this->handler->quoteTable('ezuser_role'))
                ->where(
                    $deleteAssignmentsQuery->expr->eq(
                        $this->handler->quoteColumn('role_id'),
                        $deleteAssignmentsQuery->bindValue($roleId, null, \PDO::PARAM_INT)
                    )
                );

            $draftCondition = $deleteRoleQuery->expr->eq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $deleteRoleQuery->bindValue(Role::STATUS_DEFINED, null, \PDO::PARAM_INT)
            );
        } else {
            $draftCondition = $deleteRoleQuery->expr->neq(
                $this->handler->quoteColumn('version', 'ezrole'),
                $deleteRoleQuery->bindValue(Role::STATUS_DEFINED, null, \PDO::PARAM_INT)
            );
        }

        $deleteRoleQuery
            ->deleteFrom($this->handler->quoteTable('ezrole'))
            ->where(
                $deleteRoleQuery->expr->lAnd(
                    $deleteRoleQuery->expr->eq(
                        $this->handler->quoteColumn('id'),
                        $deleteRoleQuery->bindValue($roleId, null, \PDO::PARAM_INT)
                    ),
                    $draftCondition
                )
            );

        if ($status !== Role::STATUS_DRAFT) {
            $deleteAssignmentsQuery->prepare()->execute();
        }
        $deleteRoleQuery->prepare()->execute();
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
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('ezrole'))
            ->set(
                $this->handler->quoteColumn('version'),
                $query->bindValue(Role::STATUS_DEFINED, null, \PDO::PARAM_INT)
            );
        // Draft was created from an existing role, so published role must get the original ID.
        if ($originalRoleId !== null) {
            $query->set(
                $this->handler->quoteColumn('id'),
                $query->bindValue($originalRoleId, null, \PDO::PARAM_INT)
            );
        }

        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id'),
                $query->bindValue($roleDraftId, null, \PDO::PARAM_INT)
            )
        );
        $statement = $query->prepare();
        $statement->execute();

        $policyQuery = $this->handler->createUpdateQuery();
        $policyQuery
            ->update($this->handler->quoteTable('ezpolicy'))
            ->set(
                $this->handler->quoteColumn('original_id'),
                $policyQuery->bindValue(0, null, \PDO::PARAM_INT)
            );
        // Draft was created from an existing role, so published policies must get the original role ID.
        if ($originalRoleId !== null) {
            $policyQuery->set(
                $this->handler->quoteColumn('role_id'),
                $policyQuery->bindValue($originalRoleId, null, \PDO::PARAM_INT)
            );
        }

        $policyQuery->where(
            $policyQuery->expr->eq(
                $this->handler->quoteColumn('role_id'),
                $policyQuery->bindValue($roleDraftId, null, \PDO::PARAM_INT)
            )
        );
        $queryStatement = $policyQuery->prepare();
        $queryStatement->execute();
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
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('ezpolicy'))
            ->set(
                $this->handler->quoteColumn('id'),
                $this->handler->getAutoIncrementValue('ezpolicy', 'id')
            )->set(
                $this->handler->quoteColumn('function_name'),
                $query->bindValue($policy->function)
            )->set(
                $this->handler->quoteColumn('module_name'),
                $query->bindValue($policy->module)
            )->set(
                $this->handler->quoteColumn('original_id'),
                $query->bindValue($policy->originalId ?: 0, null, \PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('role_id'),
                $query->bindValue($roleId, null, \PDO::PARAM_INT)
            );
        $query->prepare()->execute();

        $policy->id = $this->handler->lastInsertId(
            $this->handler->getSequenceName('ezpolicy', 'id')
        );

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
            $query = $this->handler->createInsertQuery();
            $query
                ->insertInto($this->handler->quoteTable('ezpolicy_limitation'))
                ->set(
                    $this->handler->quoteColumn('id'),
                    $this->handler->getAutoIncrementValue('ezpolicy_limitation', 'id')
                )->set(
                    $this->handler->quoteColumn('identifier'),
                    $query->bindValue($identifier)
                )->set(
                    $this->handler->quoteColumn('policy_id'),
                    $query->bindValue($policyId, null, \PDO::PARAM_INT)
                );
            $query->prepare()->execute();

            $limitationId = $this->handler->lastInsertId(
                $this->handler->getSequenceName('ezpolicy_limitation', 'id')
            );

            foreach ($values as $value) {
                $query = $this->handler->createInsertQuery();
                $query
                    ->insertInto($this->handler->quoteTable('ezpolicy_limitation_value'))
                    ->set(
                        $this->handler->quoteColumn('id'),
                        $this->handler->getAutoIncrementValue('ezpolicy_limitation_value', 'id')
                    )->set(
                        $this->handler->quoteColumn('value'),
                        $query->bindValue($value)
                    )->set(
                        $this->handler->quoteColumn('limitation_id'),
                        $query->bindValue($limitationId, null, \PDO::PARAM_INT)
                    );
                $query->prepare()->execute();
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

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom($this->handler->quoteTable('ezpolicy'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($policyId, null, \PDO::PARAM_INT)
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Remove all limitations for a policy.
     *
     * @param mixed $policyId
     */
    public function removePolicyLimitations($policyId)
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy_limitation'),
            $this->handler->aliasedColumn($query, 'id', 'ezpolicy_limitation_value')
        )->from(
            $this->handler->quoteTable('ezpolicy')
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation'),
            $query->expr->eq(
                $this->handler->quoteColumn('policy_id', 'ezpolicy_limitation'),
                $this->handler->quoteColumn('id', 'ezpolicy')
            )
        )->leftJoin(
            $this->handler->quoteTable('ezpolicy_limitation_value'),
            $query->expr->eq(
                $this->handler->quoteColumn('limitation_id', 'ezpolicy_limitation_value'),
                $this->handler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id', 'ezpolicy'),
                $query->bindValue($policyId, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $limitationIdsSet = [];
        $limitationValuesSet = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            if ($row['ezpolicy_limitation_id'] !== null) {
                $limitationIdsSet[$row['ezpolicy_limitation_id']] = true;
            }

            if ($row['ezpolicy_limitation_value_id'] !== null) {
                $limitationValuesSet[$row['ezpolicy_limitation_value_id']] = true;
            }
        }

        if (!empty($limitationIdsSet)) {
            $query = $this->handler->createDeleteQuery();
            $query
                ->deleteFrom($this->handler->quoteTable('ezpolicy_limitation'))
                ->where(
                    $query->expr->in($this->handler->quoteColumn('id'), array_keys($limitationIdsSet))
                );
            $query->prepare()->execute();
        }

        if (!empty($limitationValuesSet)) {
            $query = $this->handler->createDeleteQuery();
            $query
                ->deleteFrom($this->handler->quoteTable('ezpolicy_limitation_value'))
                ->where(
                    $query->expr->in($this->handler->quoteColumn('id'), array_keys($limitationValuesSet))
                );
            $query->prepare()->execute();
        }
    }
}
