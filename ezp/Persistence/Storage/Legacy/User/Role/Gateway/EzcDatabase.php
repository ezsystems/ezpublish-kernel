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
     * @var \ezcDbHandler
     */
    protected $handler;

    /**
     * Construct from database handler
     *
     * @param \ezcDbHandler $handler
     * @return void
     */
    public function __construct( \ezcDbHandler $handler )
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
            ->insertInto( 'ezrole' )
            ->set( 'is_new', 0 )
            ->set( 'name', $query->bindValue( $role->name ) )
            ->set( 'value', 0 )
            ->set( 'version', 0 );
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
            ->update( 'ezrole' )
            ->set( 'name', $query->bindValue( $role->name ) )
            ->where( $query->expr->eq( 'id', $query->bindValue( $role->id ) ) );
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
            ->deleteFrom( 'ezrole' )
            ->where( $query->expr->eq( 'id', $query->bindValue( $roleId ) ) );
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
            ->insertInto( 'ezpolicy' )
            ->set( 'function_name', $query->bindValue( $policy->moduleFunction ) )
            ->set( 'module_name', $query->bindValue( $policy->module ) )
            ->set( 'original_id', 0 )
            ->set( 'role_id', $query->bindValue( $roleId ) );
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
        throw new RuntimeException( '@TODO: Implement' );
    }
}
