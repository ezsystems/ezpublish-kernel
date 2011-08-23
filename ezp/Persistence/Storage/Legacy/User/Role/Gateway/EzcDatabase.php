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

        $role->id = $this->handler->lastInsertId();
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
            )->where( $query->expr->eq(
                $this->handler->quoteColumn( 'id' ),
                $query->bindValue( $role->id )
            ) );
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
            ->where( $query->expr->eq(
                $this->handler->quoteColumn( 'id' ),
                $query->bindValue( $roleId )
            ) );
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
                $query->bindValue( $roleId )
            );
        $query->prepare()->execute();

        $policy->id = $this->handler->lastInsertId();

        // @TODO: Handle limitations -- this still has to be documented by eZ.
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
            ->where( $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn( 'id' ),
                    $query->bindValue( $policyId )
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn( 'role_id' ),
                    $query->bindValue( $roleId )
                )
            ) );
        $query->prepare()->execute();

        // @TODO: Cascade on delete to policy limitations and limitation
        // values.
    }
}
