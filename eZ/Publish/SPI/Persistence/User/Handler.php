<?php
/**
 * File containing the User Handler interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User;

use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

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
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function create( User $user );

    /**
     * Loads user with user ID.
     *
     * @param mixed $userId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If user is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User
     */
    public function load( $userId );

    /**
     * Loads user(s) with user login / email.
     *
     * Optimized for login use (hence the possibility to match email and return several users).
     *
     * @param string $login
     * @param boolean $alsoMatchEmail Also match user email, caller must verify that $login is a valid email address.
     *
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
     *
     * @todo Throw on missing user?
     */
    public function delete( $userId );

    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function createRole( Role $role );

    /**
     * Loads a specified role by $roleId
     *
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRole( $roleId );

    /**
     * Loads a specified role by $identifier
     *
     * @param string $identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function loadRoleByIdentifier( $identifier );

    /**
     * Loads all roles
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role[]
     */
    public function loadRoles();

    /**
     * Loads roles assignments Role
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByRoleId( $roleId );

    /**
     * Loads roles assignments to a user/group
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $groupId In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     *                      By the nature of legacy this can currently also be used to get by $userId.
     * @param boolean $inherit If true also return inherited role assignments from user groups.
     *
     * @return \eZ\Publish\SPI\Persistence\User\RoleAssignment[]
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherit = false );

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
     *
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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $policy->limitation is empty (null, empty string/array..)
     */
    public function updatePolicy( Policy $policy );

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     *
     * @todo Throw exception on missing role / policy?
     *
     * @return void
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
     * Assigns role to a user or user group with given limitations
     *
     * The limitation array looks like:
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
     * @param mixed $contentId The groupId or userId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $contentId, $roleId, array $limitation = null );

    /**
     * Un-assign a role
     *
     * @param mixed $contentId The user or user group Id to un-assign the role from.
     * @param mixed $roleId
     */
    public function unAssignRole( $contentId, $roleId );
}
