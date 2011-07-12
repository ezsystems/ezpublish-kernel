<?php
/**
 * File containing the UserHandler mixederface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_user
 */

namespace ezp\Persistence\User\Interfaces;

/**
 * Storage Engine handler for user module
 *
 * @package ezp
 * @subpackage persistence_user
 */
use \ezp\Persistence\User;
mixederface UserHandler
{

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used 
     * to reference the user.
     *
     * @param \ezp\Persistence\User $user
     */
    public function createUser( User $user );

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     */
    public function deleteUser( $userId );

    /**
     * Update the user information specified by the user struct
     *
     * @param \ezp\Persistence\User $user
     */
    public function updateUser( User $user );

    /**
     * Create new role
     *
     * @param \ezp\Persistence\User\Role $role
     * @return \ezp\Persistence\User\Role
     */
    public function createRole( User\Role $role );

    /**
     * Update role
     *
     * @todo Create a RoleUpdateStruct, which omits the policies
     * @param \ezp\Persistence\User\Role $role
     */
    public function updateRole( User\Role $role );

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId );

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId 
     * @param mixed $policyId 
     * @return void
     */
    public function addPolicy( $roleId, $policyId );

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId 
     * @param mixed $policyId 
     * @return void
     */
    public function removePolicy( $roleId, $policyId );

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     * @return UserPolicy[]
     */
    public function getPermissions( $userId );

    /**
     * @param mixed $contentId
     * @param mixed $roleId
     * @param array $limitation
     * @todo Figure out which type $limitation has
     */
    public function assignRole( $contentId, $roleId, $limitation );

    /**
     * @param mixed $contentId
     * @param mixed $roleId
     */
    public function removeRole(  $contentId, $roleId );
}
?>
