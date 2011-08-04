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
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Update role
     *
     * @param Role $role
     */
    public function updateRole( Role $role )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
