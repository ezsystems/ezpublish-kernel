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

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

use Qafoo\RMF;

/**
 * Role controller
 */
class Role
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

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
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     * @param \eZ\Publish\API\Repository\UserService $userService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler,
                                 RoleService $roleService, UserService $userService,
                                 LocationService $locationService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->roleService     = $roleService;
        $this->userService     = $userService;
        $this->locationService = $locationService;
    }

    /**
     * Create new role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedRole
     */
    public function createRole( RMF\Request $request )
    {
        return new Values\CreatedRole(
            array(
                'role' => $this->roleService->createRole(
                    $this->inputDispatcher->parse(
                        new Message(
                            array( 'Content-Type' => $request->contentType ),
                            $request->body
                        )
                    )
                )
            )
        );
    }

    /**
     * Load list of roles
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function listRoles( RMF\Request $request )
    {
        return new Values\RoleList(
            $this->roleService->loadRoles()
        );
    }

    /**
     * Load role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        return $this->roleService->loadRole( $values['role'] );
    }

    /**
     * Load role by identifier
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function loadRoleByIdentifier( RMF\Request $request )
    {
        return new Values\RoleList(
            array(
                $this->roleService->loadRoleByIdentifier( $request->variables['identifier'] )
            )
        );
    }

    /**
     * Updates a role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deleteRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        $this->roleService->deleteRole(
            $this->roleService->loadRole( $values['role'] )
        );

        return new Values\ResourceDeleted();
    }

    /**
     * Loads the policies for the role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function loadPolicies( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );

        return new Values\PolicyList( $loadedRole->getPolicies(), $request->path );
    }

    /**
     * Deletes all policies from a role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deletePolicies( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );

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
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function loadPolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policy', $request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );
        foreach ( $loadedRole->getPolicies() as $policy )
        {
            if ( $policy->id == $values['policy'] )
                return $policy;
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$request->path}'." );
    }

    /**
     * Adds a policy to role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function addPolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policy', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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

        throw new Exceptions\NotFoundException( "Policy not found: '{$request->path}'." );
    }

    /**
     * Delete a policy from role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\ResourceDeleted
     */
    public function deletePolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policy', $request->path );

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

        throw new Exceptions\NotFoundException( "Policy not found: '{$request->path}'." );
    }

    /**
     * Assigns role to user
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUser( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'userRoleAssignments', $request->path );

        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUserGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignments', $request->path );

        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUser( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'userRoleAssignment', $request->path );

        $user = $this->userService->loadUser( $values['user'] );
        $role = $this->roleService->loadRole( $values['role'] );

        $this->roleService->unassignRoleFromUser( $role, $user );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Un-assigns role from user group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUserGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignment', $request->path );

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
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUser( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'userRoleAssignments', $request->path );

        $user = $this->userService->loadUser( $values['user'] );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Load role assignments for user group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUserGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignments', $request->path );

        $groupLocationParts = explode( '/', $values['group'] );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );

        return new Values\RoleAssignmentList( $roleAssignments, $values['group'], true );
    }

    /**
     * Returns a role assignment to the given user
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
     */
    public function loadRoleAssignmentForUser( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'userRoleAssignment', $request->path );

        $user = $this->userService->loadUser( $values['user'] );
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );

        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment->getRole()->id == $values['role'] )
            {
                return new Values\RestUserRoleAssignment( $roleAssignment, $values['user'] );
            }
        }

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$request->path}'." );
    }

    /**
     * Returns a role assignment to the given user group
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     */
    public function loadRoleAssignmentForUserGroup( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'groupRoleAssignment', $request->path );

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

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$request->path}'." );
    }

    /**
     * Search all policies which are applied to a given user
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function listPoliciesForUser( RMF\Request $request )
    {
        return new Values\PolicyList(
            $this->roleService->loadPoliciesByUserId(
                $request->variables['userId']
            ),
            $request->path
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
