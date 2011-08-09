<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\User\Role;
use ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Role;

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
}
