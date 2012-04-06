<?php
/**
 * File containing the User Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User,
    eZ\Publish\SPI\Persistence\User\Role,
    eZ\Publish\SPI\Persistence\User\RoleUpdateStruct,
    eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Storage Engine handler for user module
 */
interface Handler
{

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create( User $user );

    /**
     * Load user with user ID.
     *
     * @param mixed $userId
     * @return \eZ\Publish\SPI\Persistence\User
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     */
    public function load( $userId );

    /**
     * Load user(s) with user login / email.
     *
     * Optimized for login use (hence the possibility to match email and resturn several users).
     *
     * @param string $login
     * @param boolean $alsoMatchEmail Also match user email, caller must verify that $login is a valid email address.
     * @return \eZ\Publish\SPI\Persistence\User[]
     */
    public function loadByLogin( $login, $alsoMatchEmail = false );

    /**
     * Update the user information specified by the user struct
     *
     * @param \eZ\Publish\SPI\Persistence\User $user
     */
    public function update( User $user );

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     * @todo Throw on missing user?
     */
    public function delete( $userId );

    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRole( Role $role );

    /**
     * Load a specified role by $roleId
     *
     * @param mixed $roleId
     * @return \eZ\Publish\SPI\Persistence\User\Role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     */
    public function loadRole( $roleId );

    /**
     * Load a specified role by $identifier
     *
     * @param string $identifier
     * @return \eZ\Publish\SPI\Persistence\User\Role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     */
    public function loadRoleByIdentifier( $identifier );

    /**
     * Load all roles
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRoles();

    /**
     * Load roles assigned to a user/group (not including inherited roles)
     *
     * @param mixed $groupId
     *              In legacy storage engine this is the content object id a role is assigned to.
     *              By the nature of legacy storage engine, it is therefor possible to use $userId as well here.
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRolesByGroupId( $groupId );

    /**
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
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
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     * @todo Throw on invalid Role Id?
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $policy->limitation is empty (null, empty string/array..)
     */
    public function addPolicy( $roleId, Policy $policy );

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $policy->limitation is empty (null, empty string/array..)
     */
    public function updatePolicy( Policy $policy );

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     * @todo Throw exception on missing role / policy?
     */
    public function removePolicy( $roleId, $policyId );

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     *              In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     * @return \eZ\Publish\SPI\Persistence\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId );

    /**
     * Assign role to user group with given limitation
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
     * @param mixed $groupId The group Id to assign the role to.
     *                       In Legacy storage engine this is the content object id of the group to assign to.
     *                       By the nature of legacy storage engine, it is therefor possible to use $userId as well here.
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $groupId, $roleId, array $limitation = null );

    /**
     * Un-assign a role
     *
     * @param mixed $groupId The group Id to un-assign the role from.
     *                       In Legacy storage engine this is the content object id of the group to un-assign from.
     *                       By the nature of legacy storage engine, it is therefor possible to use $userId as well here.
     * @param mixed $roleId
     */
    public function unAssignRole( $groupId, $roleId );
}
