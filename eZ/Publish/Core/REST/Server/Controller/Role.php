<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

/**
 * Role controller
 */
class Role extends RestController
{
    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * User service
     *
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( RoleService $roleService, UserService $userService,
                                 LocationService $locationService )
    {
        $this->roleService     = $roleService;
        $this->userService     = $userService;
        $this->locationService = $locationService;
    }

    /**
     * Create new role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedRole
     */
    public function createRole()
    {
        return new Values\CreatedRole(
            array(
                'role' => $this->roleService->createRole(
                    $this->inputDispatcher->parse(
                        new Message(
                            array( 'Content-Type' => $this->request->contentType ),
                            $this->request->body
                        )
                    )
                )
            )
        );
    }

    /**
     * Load list of roles
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function listRoles()
    {
        return new Values\RoleList(
            $this->roleService->loadRoles()
        );
    }

    /**
     * Load role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole()
    {
        $values = $this->urlHandler->parse( 'role', $this->request->path );
        return $this->roleService->loadRole( $values['role'] );
    }

    /**
     * Load role by identifier
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function loadRoleByIdentifier()
    {
        return new Values\RoleList(
            array(
                $this->roleService->loadRoleByIdentifier( $this->request->variables['identifier'] )
            )
        );
    }

    /**
     * Updates a role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole()
    {
        $values = $this->urlHandler->parse( 'role', $this->request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );
        return $this->roleService->updateRole(
            $this->roleService->loadRole( $values['role'] ),
            $this->mapToUpdateStruct( $createStruct )
        );
    }

    /**
     * Delete a role by ID
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteRole()
    {
        //@todo error handling if the role is assigned to user or user group
        //problem being that PAPI does not specify throwing an exception in that case

        $values = $this->urlHandler->parse( 'role', $this->request->path );
        $this->roleService->deleteRole(
            $this->roleService->loadRole( $values['role'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * Loads the policies for the role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function loadPolicies()
    {
        $values = $this->urlHandler->parse( 'policies', $this->request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );

        return new Values\PolicyList( $loadedRole->getPolicies(), $this->request->path );
    }

    /**
     * Deletes all policies from a role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deletePolicies()
    {
        $values = $this->urlHandler->parse( 'policies', $this->request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );

        foreach ( $loadedRole->getPolicies() as $rolePolicy )
        {
            $this->roleService->removePolicy( $loadedRole, $rolePolicy );
        }

        return new Values\ResourceDeleted();
    }

    /**
     * Loads a policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function loadPolicy()
    {
        $values = $this->urlHandler->parse( 'policy', $this->request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );
        foreach ( $loadedRole->getPolicies() as $policy )
        {
            if ( $policy->id == $values['policy'] )
                return $policy;
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->path}'." );
    }

    /**
     * Adds a policy to role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedPolicy
     */
    public function addPolicy()
    {
        $values = $this->urlHandler->parse( 'policies', $this->request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $role = $this->roleService->addPolicy(
            $this->roleService->loadRole( $values['role'] ),
            $createStruct
        );

        $policies = $role->getPolicies();

        $policyToReturn = $policies[0];
        for ( $i = 1, $count = count( $policies ); $i < $count; $i++ )
        {
            if ( $policies[$i]->id > $policyToReturn->id )
                $policyToReturn = $policies[$i];
        }

        return new Values\CreatedPolicy(
            array(
                'policy' => $policyToReturn
            )
        );
    }

    /**
     * Updates a policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy()
    {
        $values = $this->urlHandler->parse( 'policy', $this->request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $role = $this->roleService->loadRole( $values['role'] );
        foreach ( $role->getPolicies() as $policy )
        {
            if ( $policy->id == $values['policy'] )
            {
                return $this->roleService->updatePolicy(
                    $policy,
                    $updateStruct
                );
            }
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->path}'." );
    }

    /**
     * Delete a policy from role
     *
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deletePolicy()
    {
        $values = $this->urlHandler->parse( 'policy', $this->request->path );

        $role = $this->roleService->loadRole( $values['role'] );

        $policy = null;
        foreach ( $role->getPolicies() as $rolePolicy )
        {
            if ( $rolePolicy->id == $values['policy'] )
            {
                $policy = $rolePolicy;
                break;
            }
        }

        if ( $policy !== null )
        {
            $this->roleService->removePolicy( $role, $policy );
            return new Values\ResourceDeleted();
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->path}'." );
    }

    /**
     * Assigns role to user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUser()
    {
        //@todo error handling if the role is already assigned to user
        //problem being that PAPI does not specify throwing an exception in this case

        $values = $this->urlHandler->parse( 'userRoleAssignments', $this->request->path );

        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $user = $this->userService->loadUser( $values['user'] );
        $role = $this->roleService->loadRole( $roleAssignment->roleId );

        $this->roleService->assignRoleToUser( $role, $user, $roleAssignment->limitation );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Assigns role to user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUserGroup()
    {
        //@todo error handling if the role is already assigned to user group
        //problem being that PAPI does not specify throwing an exception in this case

        $values = $this->urlHandler->parse( 'groupRoleAssignments', $this->request->path );

        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->contentType ),
                $this->request->body
            )
        );

        $groupLocationParts = explode( '/', $values['group'] );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $role = $this->roleService->loadRole( $roleAssignment->roleId );
        $this->roleService->assignRoleToUserGroup( $role, $userGroup, $roleAssignment->limitation );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        return new Values\RoleAssignmentList( $roleAssignments, $values['group'], true );
    }

    /**
     * Un-assigns role from user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUser()
    {
        $values = $this->urlHandler->parse( 'userRoleAssignment', $this->request->path );

        $user = $this->userService->loadUser( $values['user'] );
        $role = $this->roleService->loadRole( $values['role'] );

        $this->roleService->unassignRoleFromUser( $role, $user );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Un-assigns role from user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUserGroup()
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignment', $this->request->path );

        $groupLocationParts = explode( '/', $values['group'] );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $role = $this->roleService->loadRole( $values['role'] );
        $this->roleService->unassignRoleFromUserGroup( $role, $userGroup );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        return new Values\RoleAssignmentList( $roleAssignments, $values['group'], true );
    }

    /**
     * Load role assignments for user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUser()
    {
        $values = $this->urlHandler->parse( 'userRoleAssignments', $this->request->path );

        $user = $this->userService->loadUser( $values['user'] );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Load role assignments for user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUserGroup()
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignments', $this->request->path );

        $groupLocationParts = explode( '/', $values['group'] );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );

        return new Values\RoleAssignmentList( $roleAssignments, $values['group'], true );
    }

    /**
     * Returns a role assignment to the given user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserRoleAssignment
     */
    public function loadRoleAssignmentForUser()
    {
        $values = $this->urlHandler->parse( 'userRoleAssignment', $this->request->path );

        $user = $this->userService->loadUser( $values['user'] );
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );

        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment->getRole()->id == $values['role'] )
            {
                return new Values\RestUserRoleAssignment( $roleAssignment, $values['user'] );
            }
        }

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$this->request->path}'." );
    }

    /**
     * Returns a role assignment to the given user group
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroupRoleAssignment
     */
    public function loadRoleAssignmentForUserGroup()
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignment', $this->request->path );

        $groupLocationParts = explode( '/', $values['group'] );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment->getRole()->id == $values['role'] )
            {
                return new Values\RestUserGroupRoleAssignment( $roleAssignment, $values['group'] );
            }
        }

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$this->request->path}'." );
    }

    /**
     * Search all policies which are applied to a given user
     *
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function listPoliciesForUser()
    {
        return new Values\PolicyList(
            $this->roleService->loadPoliciesByUserId(
                $this->request->variables['userId']
            ),
            $this->request->path
        );
    }

    /**
     * Maps a RoleCreateStruct to a RoleUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $createStruct
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    protected function mapToUpdateStruct( RoleCreateStruct $createStruct )
    {
        return new RoleUpdateStruct(
            array(
                'identifier' => $createStruct->identifier,
                'mainLanguageCode' => $createStruct->mainLanguageCode,
                'names' => $createStruct->names,
                'descriptions' => $createStruct->descriptions
            )
        );
    }
}
