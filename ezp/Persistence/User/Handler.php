<?php
/**
 * File containing the User Handler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\User;
use ezp\Persistence\User,
    ezp\Persistence\User\Role,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy;

/**
 * Storage Engine handler for user module
 *
 */
interface Handler
{

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \ezp\Persistence\User $user
     * @return \ezp\Persistence\User
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
    public function createRole( Role $role );

    /**
     * Update role
     *
     * @param \ezp\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role );

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
     * @param \ezp\Persistence\User\Policy $policy
     * @return \ezp\Persistence\User\Policy
     */
    public function addPolicy( $roleId, Policy $policy );

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
     * @return \ezp\Persistence\User\Policy[]
     */
    public function getPermissions( $userId );

    /**
     * Assign role to user with given limitation
     *
     * The limitation array may look like:
     * <code>
     *  array(
     *      'Subtree' => array(
     *          '/1/2/',
     *          '/1/4/',
     *      ),
     *      'Foo' => array( 'Bar' ),
     *      …
     *  )
     * </code>
     *
     * Where the keys are the limitation identifiers, and the respective values
     * are an array of limitation values. The limitation parameter is optional.
     *
     * @todo It has been discussed to not support assigning roles with limitations, as it is kind of flawed in eZ Publish
     *       Hence you would simplify the design and reduce future bugs by forcing use of policy limitations instead.
     * @todo It has been decided to only allow assigning roles to user groups to be aligned with systems like ldap.
     * @param mixed $userId
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $userId, $roleId, array $limitation = null );

    /**
     * Un-assign a role
     *
     * @todo Rename to not confuse with deleteRole?
     * @param mixed $userId
     * @param mixed $roleId
     */
    public function removeRole(  $userId, $roleId );
}
?>
