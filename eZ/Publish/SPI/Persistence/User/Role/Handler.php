<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\Role\Handler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\User\Role;

use eZ\Publish\SPI\Persistence\User\Role\Policy\UpdateStruct as PolicyUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role\Policy\CreateStruct as PolicyCreateStruct;


/**
 * The interface class for the role handler
 */
interface Handler
{
    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role\CreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function create( CreateStruct $roleCreateStruct );

    /**
     * Loads a specified role by $roleId
     *
     * @param mixed $roleId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If role is not found
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    public function load( $roleId );

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
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role\UpdateStruct $roleUpdateStruct
     */
    public function update( UpdateStruct $roleUpdateStruct );

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function delete( $roleId );

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Role\Policy\CreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\Policy
     */
    public function addPolicy( $roleId, PolicyCreateStruct $policyCreateStruct );

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @invariant the limitations are not empty
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role\Policy\UpdateStruct $policy
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\Policy
     */
    public function updatePolicy( $policyId, PolicyUpdateStruct $policy );

    /**
     * Deletes a policy
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function deletePolicy( $policyId );

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     *              In legacy storage engine this is the content object id roles are assigned to in ezuser_role.
     * @return \eZ\Publish\SPI\Persistence\User\Role\Policy[]
     */
    public function loadPoliciesByUserId( $userId );

    /**
     * Assigns role to a user with given limitations
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
     * @param mixed $userId The userId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\UserAssignment
     */
    public function assignRoleToUser( $userId, $roleId, array $limitation = null );

    /**
     * Assigns role to a user group with given limitations
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
     * @param mixed $groupId The groupId to assign the role to.
     * @param mixed $roleId
     * @param array $limitation
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\GroupAssignment
     */
    public function assignRoleToUserGroup( $groupId, $roleId, array $limitation = null );

    /**
     * Loads roles assignments for a user
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $userId
     * @param boolean $inherit If true also return inherited role assignments from user groups.
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\UserAssignment[]
     */
    public function loadUserRoleAssignments( $userId, $inherit = false );

    /**
     * Loads roles assignments Role
     *
     * Role Assignments with same roleId and limitationIdentifier will be merged together into one.
     *
     * @param mixed $roleId
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role\Assignment[]
     */
    public function loadRoleAssignments( $roleId );

    /**
     * Un-assign a role
     *
     * @param mixed $roleAssignmentId the id of the role assignment
     */
    public function removeRoleAssignment( $roleAssignmentId );
}
