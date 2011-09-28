<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\User\Role\Gateway;
use ezp\Persistence\Storage\Legacy\User\Role\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\User\Policy,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Role;

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
     * Construct from database handler
     *
     * @param EzcDbHandler $handler
     * @return void
     */
    public function __construct( EzcDbHandler $handler )
    {
        $this->handler = $handler;
    }

    /**
     * Create new role
     *
     * @param Role $role
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
                $query->bindValue( $role->name )
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
     * Load a specified role by id
     *
     * @param mixed $roleId
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
     * Update role
     *
     * @param RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update( $this->handler->quoteTable( 'ezrole' ) )
            ->set(
                $this->handler->quoteColumn( 'name' ),
                $query->bindValue( $role->name )
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
     * @param Policy $policy
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

        if ( !is_array( $policy->limitations ) )
        {
            // Handle the only valid non-array value "*" by not inserting
            // anything. Still has not been documented by eZ Systems. So we
            // assume this is the right way to handle it.
            return $policy;
        }

        foreach ( $policy->limitations as $identifier => $values )
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
                    $query->bindValue( $policy->id, null, \PDO::PARAM_INT )
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

        return $policy;
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     */
    public function removePolicy( $roleId, $policyId )
    {
        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom( $this->handler->quoteTable( 'ezpolicy' ) )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'id' ),
                        $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn( 'role_id' ),
                        $query->bindValue( $roleId, null, \PDO::PARAM_INT )
                    )
                )
            );
        $query->prepare()->execute();

        // @TODO: Cascade on delete to policy limitations and limitation
        // values.
    }
}
