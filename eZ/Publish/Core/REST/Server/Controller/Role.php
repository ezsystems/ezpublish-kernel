<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;

use eZ\Publish\API\Repository\Exceptions\LimitationValidationException;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use eZ\Publish\Core\REST\Server\Values;
use eZ\Publish\Core\REST\Server\Controller as RestController;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;

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
                            array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                            $this->request->getContent()
                        )
                    )
                )
            )
        );
    }

    /**
     * Loads list of roles
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function listRoles()
    {
        $roles = array();
        if ( $this->request->query->has( 'identifier' ) )
        {
            try
            {
                $role = $this->roleService->loadRoleByIdentifier( $this->request->query->get( 'identifier' ) );
                $roles[] = $role;
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }
        else
        {
            $offset = $this->request->query->has( 'offset' ) ? (int)$this->request->query->get( 'offset' ) : 0;
            $limit = $this->request->query->has( 'limit' ) ? (int)$this->request->query->get( 'limit' ) : -1;

            $roles = array_slice(
                $this->roleService->loadRoles(),
                $offset >= 0 ? $offset : 0,
                $limit >= 0 ? $limit : null
            );
        }

        return new Values\RoleList( $roles, $this->request->getPathInfo() );
    }

    /**
     * Loads role
     *
     * @param $roleId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( $roleId )
    {
        return $this->roleService->loadRole( $roleId );
    }

    /**
     * Updates a role
     *
     * @param $roleId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( $roleId )
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );
        return $this->roleService->updateRole(
            $this->roleService->loadRole( $roleId ),
            $this->mapToUpdateStruct( $createStruct )
        );
    }

    /**
     * Delete a role by ID
     *
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deleteRole( $roleId )
    {
        $this->roleService->deleteRole(
            $this->roleService->loadRole( $roleId )
        );

        return new Values\NoContent();
    }

    /**
     * Loads the policies for the role
     *
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function loadPolicies( $roleId )
    {
        $loadedRole = $this->roleService->loadRole( $roleId  );
        return new Values\PolicyList( $loadedRole->getPolicies(), $this->request->getPathInfo() );
    }

    /**
     * Deletes all policies from a role
     *
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deletePolicies( $roleId )
    {
        $loadedRole = $this->roleService->loadRole( $roleId );

        foreach ( $loadedRole->getPolicies() as $policy )
        {
            $this->roleService->deletePolicy( $policy );
        }

        return new Values\NoContent();
    }

    /**
     * Loads a policy
     *
     * @param $roleId
     * @param $policyId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function loadPolicy( $roleId, $policyId )
    {
        $loadedRole = $this->roleService->loadRole( $roleId );
        foreach ( $loadedRole->getPolicies() as $policy )
        {
            if ( $policy->id == $policyId )
                return $policy;
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->getPathInfo()}'." );
    }

    /**
     * Adds a policy to role
     *
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedPolicy
     */
    public function addPolicy( $roleId )
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        try
        {
            $role = $this->roleService->addPolicy(
                $this->roleService->loadRole( $roleId ),
                $createStruct
            );
        }
        catch ( LimitationValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }

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
     * @param $roleId
     * @param $policyId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( $roleId, $policyId )
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        $role = $this->roleService->loadRole( $roleId );
        foreach ( $role->getPolicies() as $policy )
        {
            if ( $policy->id == $policyId )
            {
                try
                {
                    return $this->roleService->updatePolicy(
                        $policy,
                        $updateStruct
                    );
                }
                catch ( LimitationValidationException $e )
                {
                    throw new BadRequestException( $e->getMessage() );
                }
            }
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->getPathInfo()}'." );
    }

    /**
     * Delete a policy from role
     *
     * @param $roleId
     * @param $policyId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @return \eZ\Publish\Core\REST\Server\Values\NoContent
     */
    public function deletePolicy( $roleId, $policyId )
    {
        $role = $this->roleService->loadRole( $roleId );

        $policy = null;
        foreach ( $role->getPolicies() as $rolePolicy )
        {
            if ( $rolePolicy->id == $policyId )
            {
                $policy = $rolePolicy;
                break;
            }
        }

        if ( $policy !== null )
        {
            $this->roleService->deletePolicy( $policy );
            return new Values\NoContent();
        }

        throw new Exceptions\NotFoundException( "Policy not found: '{$this->request->getPathInfo()}'." );
    }

    /**
     * Assigns role to user
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUser( $userId )
    {
        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        $user = $this->userService->loadUser( $userId );
        $role = $this->roleService->loadRole( $roleAssignment->roleId );

        try
        {
            $this->roleService->assignRoleToUser( $role, $user, $roleAssignment->limitation );
        }
        catch ( LimitationValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Assigns role to user group
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function assignRoleToUserGroup( $groupPath )
    {
        $roleAssignment = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $this->request->headers->get( 'Content-Type' ) ),
                $this->request->getContent()
            )
        );

        $groupLocationParts = explode( '/', $groupPath );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $role = $this->roleService->loadRole( $roleAssignment->roleId );

        try
        {
            $this->roleService->assignRoleToUserGroup( $role, $userGroup, $roleAssignment->limitation );
        }
        catch ( LimitationValidationException $e )
        {
            throw new BadRequestException( $e->getMessage() );
        }

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        return new Values\RoleAssignmentList( $roleAssignments, $groupPath, true );
    }

    /**
     * Un-assigns role from user
     *
     * @param $userId
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUser( $userId, $roleId )
    {
        $user = $this->userService->loadUser( $userId );
        $role = $this->roleService->loadRole( $roleId );

        $this->roleService->unassignRoleFromUser( $role, $user );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Un-assigns role from user group
     *
     * @param $groupPath
     * @param $roleId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function unassignRoleFromUserGroup( $groupPath, $roleId )
    {
        $groupLocationParts = explode( '/', $groupPath );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $role = $this->roleService->loadRole( $roleId );
        $this->roleService->unassignRoleFromUserGroup( $role, $userGroup );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        return new Values\RoleAssignmentList( $roleAssignments, $groupPath, true );
    }

    /**
     * Loads role assignments for user
     *
     * @param $userId
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUser( $userId )
    {
        $user = $this->userService->loadUser( $userId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );
        return new Values\RoleAssignmentList( $roleAssignments, $user->id );
    }

    /**
     * Loads role assignments for user group
     *
     * @param $groupPath
     *
     * @return \eZ\Publish\Core\REST\Server\Values\RoleAssignmentList
     */
    public function loadRoleAssignmentsForUserGroup( $groupPath )
    {
        $groupLocationParts = explode( '/', $groupPath );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );

        return new Values\RoleAssignmentList( $roleAssignments, $groupPath, true );
    }

    /**
     * Returns a role assignment to the given user
     *
     * @param $userId
     * @param $roleId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserRoleAssignment
     */
    public function loadRoleAssignmentForUser( $userId, $roleId )
    {
        $user = $this->userService->loadUser( $userId );
        $roleAssignments = $this->roleService->getRoleAssignmentsForUser( $user );

        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment->getRole()->id == $roleId )
            {
                return new Values\RestUserRoleAssignment( $roleAssignment, $userId );
            }
        }

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$this->request->getPathInfo()}'." );
    }

    /**
     * Returns a role assignment to the given user group
     *
     * @param $groupPath
     * @param $roleId
     *
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\NotFoundException
     * @return \eZ\Publish\Core\REST\Server\Values\RestUserGroupRoleAssignment
     */
    public function loadRoleAssignmentForUserGroup( $groupPath, $roleId )
    {
        $groupLocationParts = explode( '/', $groupPath );
        $groupLocation = $this->locationService->loadLocation( array_pop( $groupLocationParts ) );
        $userGroup = $this->userService->loadUserGroup( $groupLocation->contentId );

        $roleAssignments = $this->roleService->getRoleAssignmentsForUserGroup( $userGroup );
        foreach ( $roleAssignments as $roleAssignment )
        {
            if ( $roleAssignment->getRole()->id == $roleId )
            {
                return new Values\RestUserGroupRoleAssignment( $roleAssignment, $groupPath );
            }
        }

        throw new Exceptions\NotFoundException( "Role assignment not found: '{$this->request->getPathInfo()}'." );
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
                $this->request->query->get( 'userId' )
            ),
            $this->request->getPathInfo()
        );
    }

    /**
     * Maps a RoleCreateStruct to a RoleUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $createStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    protected function mapToUpdateStruct( RoleCreateStruct $createStruct )
    {
        return new RoleUpdateStruct(
            array(
                'identifier' => $createStruct->identifier
            )
        );
    }
}
