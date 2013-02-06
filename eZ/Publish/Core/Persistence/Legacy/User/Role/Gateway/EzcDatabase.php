<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;

use eZ\Publish\Core\Persistence\Legacy\User\Role\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role;

/**
 * Base class for content type gateways.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
     */
    protected $handler;

    /**
     * Internal type ID for user groups
     */
    const GROUP_TYPE_ID = 3;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $handler
     *
     * @return void
     */
    public function __construct( EzcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return Role
     */
    public function createRole( Role $role )
    {
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( 'ezrole' ) )
            ->set(
                $this->handler->quoteColumn( 'id' ),
                $this->handler->getAutoIncrementValue( 'ezrole', 'id' )
            )->set(
                $this->handler->quoteColumn( 'is_new' ),
                0
            )->set(
                $this->handler->quoteColumn( 'name' ),
                $query->bindValue( $role->identifier )
            )->set(
                $this->handler->quoteColumn( 'value' ),
                0
            )->set(
                $this->handler->quoteColumn( 'version' ),
                0
            );
        $query->prepare()->execute();

        $role->id = $this->handler->lastInsertId(
            $this->handler->getSequenceName( 'ezrole', 'id' )
        );
    }

    /**
     * Loads a specified role by id
     *
     * @param mixed $roleId
     *
     * @return array
     */
    public function loadRole( $roleId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'contentobject_id', 'ezuser_role' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->handler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $this->handler->quoteTable( 'ezrole' )
        )->leftJoin(
            $this->handler->quoteTable( 'ezuser_role' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezuser_role' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( 'id', 'ezrole' ),
                $query->bindValue( $roleId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads a specified role by $identifier
     *
     * @param string $identifier
     *
     * @return array
     */
    public function loadRoleByIdentifier( $identifier )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'contentobject_id', 'ezuser_role' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->handler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $this->handler->quoteTable( 'ezrole' )
        )->leftJoin(
            $this->handler->quoteTable( 'ezuser_role' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezuser_role' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( 'name', 'ezrole' ),
                $query->bindValue( $identifier, null, \PDO::PARAM_STR )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads all roles
     *
     * @return array
     */
    public function loadRoles()
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'contentobject_id', 'ezuser_role' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->handler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $this->handler->quoteTable( 'ezrole' )
        )->leftJoin(
            $this->handler->quoteTable( 'ezuser_role' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezuser_role' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads all roles associated with the given content objects
     *
     * @param array $contentIds
     *
     * @return array
     */
    public function loadRolesForContentObjects( $contentIds )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn( $query, 'contentobject_id', 'ezuser_role' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->handler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->handler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $query->alias(
                $this->handler->quoteTable( 'ezuser_role' ),
                $this->handler->quoteIdentifier( 'ezuser_role_search' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezrole' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'id', 'ezrole' ),
                $this->handler->quoteColumn( 'role_id', 'ezuser_role_search' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezuser_role' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezuser_role' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->handler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->in(
                $this->handler->quoteColumn( 'contentobject_id', 'ezuser_role_search' ),
                $contentIds
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads role assignments for specified content ID
     *
     * @param mixed $groupId
     * @param boolean $inherited
     *
     * @return array
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherited = false )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn( 'contentobject_id' ),
            $this->handler->quoteColumn( 'limit_identifier' ),
            $this->handler->quoteColumn( 'limit_value' ),
            $this->handler->quoteColumn( 'role_id' )
        )->from(
            $this->handler->quoteTable( 'ezuser_role' )
        );

        if ( $inherited )
        {
            $groupIds = $this->fetchUserGroups( $groupId );
            $groupIds[] = $groupId;
            $query->where(
                $query->expr->in(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $groupIds
                )
            );
        }
        else
        {
            $query->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $groupId, null, \PDO::PARAM_INT )
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     *
     * @return UserPolicy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        $groupIds = $this->fetchUserGroups( $userId );
        $groupIds[] = $userId;

        return $this->loadRolesForContentObjects( $groupIds );
    }

    /**
     * Fetch all group IDs the user belongs to
     *
     * @param int $userId
     *
     * @return array
     */
    protected function fetchUserGroups( $userId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn( 'path_string', 'ezcontentobject_tree' )
        )->from(
            $this->handler->quoteTable( 'ezcontentobject_tree' )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' ),
                $query->bindValue( $userId )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $paths = $statement->fetchAll( \PDO::FETCH_COLUMN );
        $nodeIDs = array_unique(
            array_reduce(
                array_map(
                    function ( $pathString )
                    {
                        return array_filter( explode( '/', $pathString ) );
                    },
                    $paths
                ),
                'array_merge_recursive',
                array()
            )
        );

        if ( empty( $nodeIDs ) )
            return array();

        // Limit nodes to groups only
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->quoteColumn( 'id', 'ezcontentobject' )
        )->from(
            $this->handler->quoteTable( 'ezcontentobject_tree' )
        )->rightJoin(
            $this->handler->quoteTable( 'ezcontentobject' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'id', 'ezcontentobject' ),
                $this->handler->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' )
            )
        )->where(
            $query->expr->lAnd(
                $query->expr->in(
                    $this->handler->quoteColumn( 'node_id', 'ezcontentobject_tree' ),
                    $nodeIDs
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn( 'contentclass_id', 'ezcontentobject' ),
                    // We use the integer type ID here, to minimize joins and
                    // make use of existing keys. One might want to make this
                    // "injectable".
                    $query->bindValue( self::GROUP_TYPE_ID, null, \PDO::PARAM_INT )// @todo: Can not hard code group type id!
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_COLUMN );
    }

    /**
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezrole' ) )
            ->set(
                $this->handler->quoteColumn( 'name' ),
                $query->bindValue( $role->identifier )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'id' ),
                    $query->bindValue( $role->id, null, \PDO::PARAM_INT )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezrole' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'id' ),
                    $query->bindValue( $roleId, null, \PDO::PARAM_INT )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return void
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto( $this->handler->quoteTable( 'ezpolicy' ) )
            ->set(
                $this->handler->quoteColumn( 'id' ),
                $this->handler->getAutoIncrementValue( 'ezpolicy', 'id' )
            )->set(
                $this->handler->quoteColumn( 'function_name' ),
                $query->bindValue( $policy->function )
            )->set(
                $this->handler->quoteColumn( 'module_name' ),
                $query->bindValue( $policy->module )
            )->set(
                $this->handler->quoteColumn( 'original_id' ),
                0
            )->set(
                $this->handler->quoteColumn( 'role_id' ),
                $query->bindValue( $roleId, null, \PDO::PARAM_INT )
            );
        $query->prepare()->execute();

        $policy->id = $this->handler->lastInsertId(
            $this->handler->getSequenceName( 'ezpolicy', 'id' )
        );

        // Handle the only valid non-array value "*" by not inserting
        // anything. Still has not been documented by eZ Systems. So we
        // assume this is the right way to handle it.
        if ( is_array( $policy->limitations ) )
        {
            $this->addPolicyLimitations( $policy->id, $policy->limitations );
        }

        return $policy;
    }

    /**
     * Adds limitations to an existing policy
     *
     * @param int $policyId
     * @param array $limitations
     *
     * @return void
     */
    public function addPolicyLimitations( $policyId, array $limitations )
    {
        foreach ( $limitations as $identifier => $values )
        {
            $query = $this->handler->createInsertQuery();
            $query
                ->insertInto( $this->handler->quoteTable( 'ezpolicy_limitation' ) )
                ->set(
                    $this->handler->quoteColumn( 'id' ),
                    $this->handler->getAutoIncrementValue( 'ezpolicy_limitation', 'id' )
                )->set(
                    $this->handler->quoteColumn( 'identifier' ),
                    $query->bindValue( $identifier )
                )->set(
                    $this->handler->quoteColumn( 'policy_id' ),
                    $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                );
            $query->prepare()->execute();

            $limitationId = $this->handler->lastInsertId(
                $this->handler->getSequenceName( 'ezpolicy_limitation', 'id' )
            );

            foreach ( $values as $value )
            {
                $query = $this->handler->createInsertQuery();
                $query
                    ->insertInto( $this->handler->quoteTable( 'ezpolicy_limitation_value' ) )
                    ->set(
                        $this->handler->quoteColumn( 'id' ),
                        $this->handler->getAutoIncrementValue( 'ezpolicy_limitation_value', 'id' )
                    )->set(
                        $this->handler->quoteColumn( 'value' ),
                        $query->bindValue( $value )
                    )->set(
                        $this->handler->quoteColumn( 'limitation_id' ),
                        $query->bindValue( $limitationId, null, \PDO::PARAM_INT )
                    );
                $query->prepare()->execute();
            }
        }
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function removePolicy( $policyId )
    {
        $this->removePolicyLimitations( $policyId );

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezpolicy' ) )
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'id' ),
                    $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Remove all limitations for a policy
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function removePolicyLimitations( $policyId )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy_limitation' ),
            $this->handler->aliasedColumn( $query, 'id', 'ezpolicy_limitation_value' )
        )->from(
            $this->handler->quoteTable( 'ezpolicy' )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->handler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->handler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->handler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->eq(
                $this->handler->quoteColumn( 'id', 'ezpolicy' ),
                $query->bindValue( $policyId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $limitations = array();
        $values = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $limitations[] = $row['ezpolicy_limitation_id'];
            $values[] = $row['ezpolicy_limitation_value_id'];
        }

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezpolicy_limitation' ) )
            ->where(
                $query->expr->in( $this->handler->quoteColumn( 'id' ), array_unique( $limitations ) )
            );
        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezpolicy_limitation_value' ) )
            ->where(
                $query->expr->in( $this->handler->quoteColumn( 'id' ), array_unique( $values ) )
            );
        $query->prepare()->execute();
    }
}
