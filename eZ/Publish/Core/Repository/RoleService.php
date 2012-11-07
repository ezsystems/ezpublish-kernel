<?php
/**
 * File containing the eZ\Publish\Core\Repository\RoleService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct,
    eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct,
    eZ\Publish\Core\Repository\Values\User\Policy,
    eZ\Publish\API\Repository\Values\User\Policy as APIPolicy,
    eZ\Publish\API\Repository\Values\User\RoleUpdateStruct,
    eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct,
    eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct,
    eZ\Publish\Core\Repository\Values\User\Role,
    eZ\Publish\API\Repository\Values\User\Role as APIRole,
    eZ\Publish\Core\Repository\Values\User\RoleCreateStruct,
    eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct,
    eZ\Publish\Core\Repository\Values\User\UserRoleAssignment,
    eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment,
    eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation,
    eZ\Publish\API\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\User\UserGroup,

    eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy,
    eZ\Publish\SPI\Persistence\User\RoleAssignment as SPIRoleAssignment,
    eZ\Publish\SPI\Persistence\User\Role as SPIRole,
    eZ\Publish\SPI\Persistence\User\RoleUpdateStruct as SPIRoleUpdateStruct,

    eZ\Publish\API\Repository\RoleService as RoleServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\User\Handler,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException,

    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;

/**
 * This service provides methods for managing Roles and Policies
 *
 * @package eZ\Publish\Core\Repository
 */
class RoleService implements RoleServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\User\Handler
     */
    protected $userHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $userHandler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->userHandler = $userHandler;
        $this->settings = $settings + array(// Union makes sure default settings are ignored if provided in argument
            'limitationTypes' => array(),
            'limitationMap' => array(),
        );
    }

    /**
     * Creates a new Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function createRole( APIRoleCreateStruct $roleCreateStruct )
    {
        if ( !is_string( $roleCreateStruct->identifier ) || empty( $roleCreateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $roleCreateStruct->identifier, "RoleCreateStruct" );

        if ( $this->repository->hasAccess( 'role', 'create' ) !== true )
            throw new UnauthorizedException( 'role', 'create' );

        try
        {
            $existingRole = $this->loadRoleByIdentifier( $roleCreateStruct->identifier );
            if ( $existingRole !== null )
                throw new InvalidArgumentException( "roleCreateStruct", "role with specified identifier already exists" );
        }
        catch ( APINotFoundException $e )
        {
            // Do nothing
        }

        $spiRole = $this->buildPersistenceRoleObject( $roleCreateStruct );

        $this->repository->beginTransaction();
        try
        {
            $createdRole = $this->userHandler->createRole( $spiRole );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainRoleObject( $createdRole );
    }

    /**
     * Updates the name of the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( APIRole $role, RoleUpdateStruct $roleUpdateStruct )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( $roleUpdateStruct->identifier !== null && !is_string( $roleUpdateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $roleUpdateStruct->identifier, "RoleUpdateStruct" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        if ( $roleUpdateStruct->identifier !== null )
        {
            try
            {
                $existingRole = $this->loadRoleByIdentifier( $roleUpdateStruct->identifier );
                if ( $existingRole !== null )
                    throw new InvalidArgumentException( "roleUpdateStruct", "role with specified identifier already exists" );
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $loadedRole = $this->loadRole( $role->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->updateRole(
                new SPIRoleUpdateStruct(
                    array(
                        'id' => $loadedRole->id,
                        'identifier' => $roleUpdateStruct->identifier ?: $role->identifier
                    )
                )
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * adds a new policy to the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy( APIRole $role, APIPolicyCreateStruct $policyCreateStruct )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_string( $policyCreateStruct->module ) || empty( $policyCreateStruct->module ) )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( !is_string( $policyCreateStruct->function ) || empty( $policyCreateStruct->function ) )
            throw new InvalidArgumentValue( "function", $policyCreateStruct->function, "PolicyCreateStruct" );

        if ( $policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*' )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $loadedRole = $this->loadRole( $role->id );

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $policyCreateStruct->getLimitations()
        );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->addPolicy( $loadedRole->id, $spiPolicy );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * removes a policy from the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to remove from the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role the updated role
     */
    public function removePolicy( APIRole $role, APIPolicy $policy )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $policy->id ) )
            throw new InvalidArgumentValue( "id", $policy->id, "Policy" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $loadedRole = $this->loadRole( $role->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->removePolicy( $loadedRole->id, $policy->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct )
    {
        if ( !is_numeric( $policy->id ) )
            throw new InvalidArgumentValue( "id", $policy->id, "Policy" );

        if ( !is_numeric( $policy->roleId ) )
            throw new InvalidArgumentValue( "roleId", $policy->roleId, "Policy" );

        if ( !is_string( $policy->module ) )
            throw new InvalidArgumentValue( "module", $policy->module, "Policy" );

        if ( !is_string( $policy->function ) )
            throw new InvalidArgumentValue( "function", $policy->function, "Policy" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policy->module,
            $policy->function,
            $policyUpdateStruct->getLimitations()
        );

        $spiPolicy->id = $policy->id;
        $spiPolicy->roleId = $policy->roleId;

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->updatePolicy( $spiPolicy );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainPolicyObject( $spiPolicy );
    }

    /**
     * loads a role for the given id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given id was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( $id )
    {
        if ( !is_numeric( $id ) )
            throw new InvalidArgumentValue( "id", $id );

        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $spiRole = $this->userHandler->loadRole( $id );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * loads a role for the given identifier
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier( $identifier )
    {
        if ( !is_string( $identifier ) )
            throw new InvalidArgumentValue( "identifier", $identifier );

        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $spiRole = $this->userHandler->loadRoleByIdentifier( $identifier );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role[]
     */
    public function loadRoles()
    {
        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $spiRoles = $this->userHandler->loadRoles();

        $roles = array();
        foreach ( $spiRoles as $spiRole )
        {
            $roles[] = $this->buildDomainRoleObject( $spiRole );
        }

        return $roles;
    }

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( APIRole $role )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( $this->repository->hasAccess( 'role', 'delete' ) !== true )
            throw new UnauthorizedException( 'role', 'delete' );

        $loadedRole = $this->loadRole( $role->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->deleteRole( $loadedRole->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        if ( !is_numeric( $userId ) )
            throw new InvalidArgumentValue( "userId", $userId );

        $spiPolicies = $this->userHandler->loadPoliciesByUserId( $userId );

        $policies = array();
        foreach ( $spiPolicies as $spiPolicy )
        {
            $policies[] = $this->buildDomainPolicyObject( $spiPolicy );
        }

        if ( empty( $policies ) )
            $this->userHandler->load( $userId );// For NotFoundException in case userId is invalid

        return $policies;
    }

    /**
     * assigns a role to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup( APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        if ( $this->repository->canUser( 'role', 'assign', $userGroup, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        $loadedRole = $this->loadRole( $role->id );
        $loadedUserGroup = $this->repository->getUserService()->loadUserGroup( $userGroup->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->assignRole(
                $loadedUserGroup->id,
                $loadedRole->id,
                $roleLimitation ? array( $roleLimitation->getIdentifier() => $roleLimitation->limitationValues ) : null
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * removes a role from the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup( APIRole $role, UserGroup $userGroup )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        if ( $this->repository->canUser( 'role', 'assign', $userGroup, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        $spiRole = $this->userHandler->loadRole( $role->id );

        if ( !in_array( $userGroup->id, $spiRole->groupIds ) )
            throw new InvalidArgumentException( "userGroup", "role is not assigned to the user group" );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->unAssignRole( $userGroup->id, $role->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * assigns a role to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser( APIRole $role, User $user, RoleLimitation $roleLimitation = null )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( $this->repository->canUser( 'role', 'assign', $user, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        $loadedRole = $this->loadRole( $role->id );
        $loadedUser = $this->repository->getUserService()->loadUser( $user->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->assignRole(
                $loadedUser->id,
                $loadedRole->id,
                $roleLimitation ? array( $roleLimitation->getIdentifier() => $roleLimitation->limitationValues ) : null
            );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * removes a role from the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser( APIRole $role, User $user )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( $this->repository->canUser( 'role', 'assign', $user, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        $spiRole = $this->userHandler->loadRole( $role->id );

        if ( !in_array( $user->id, $spiRole->groupIds ) )
            throw new InvalidArgumentException( "user", "role is not assigned to the user" );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->unAssignRole( $user->id, $role->id );
            $this->repository->commit();
        }
        catch ( \Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments( APIRole $role )
    {
        if ( !is_numeric( $role->id ) )
            throw new InvalidArgumentValue( "id", $role->id, "Role" );

        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $userHandler = $this->userHandler;
        $spiRole = $userHandler->loadRole( $role->id );

        $userService = $this->repository->getUserService();

        $roleAssignments = array();
        foreach ( $spiRole->groupIds as $groupId )
        {
            // $spiRole->groupIds can contain both group and user IDs
            // We'll check if the ID belongs to group, if not, see if it belongs to user
            try
            {
                $userGroup = $userService->loadUserGroup( $groupId );

                $spiRoleAssignments = $userHandler->loadRoleAssignmentsByGroupId( $userGroup->id );
                foreach ( $spiRoleAssignments as $spiRoleAssignment )
                {
                    if ( $spiRoleAssignment->role->id == $role->id )
                    {
                        $roleAssignments[] = $this->buildDomainUserGroupRoleAssignmentObject(
                            $spiRoleAssignment,
                            $userGroup,
                            $role
                        );
                    }
                }
            }
            catch ( APINotFoundException $e )
            {
                try
                {
                    $user = $userService->loadUser( $groupId );

                    $spiRoleAssignments = $userHandler->loadRoleAssignmentsByGroupId( $user->id );
                    foreach ( $spiRoleAssignments as $spiRoleAssignment )
                    {
                        if ( $spiRoleAssignment->role->id == $role->id )
                        {
                            $roleAssignments[] = $this->buildDomainUserRoleAssignmentObject(
                                $spiRoleAssignment,
                                $user,
                                $role
                            );
                        }
                    }
                }
                catch ( APINotFoundException $e )
                {
                    // Do nothing
                }
            }
        }

        return $roleAssignments;
    }

    /**
     * Returns the roles assigned to the given user
     *
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param bool $inherited
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException If the current user is not allowed to read a role
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue On invalid User object
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[]
     */
    public function getRoleAssignmentsForUser( User $user, $inherited = false )
    {
        if ( !is_numeric( $user->id ) )
            throw new InvalidArgumentValue( "id", $user->id, "User" );

        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $roleAssignments = array();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId( $user->id, $inherited );
        foreach ( $spiRoleAssignments as $spiRoleAssignment )
        {
            if ( !$inherited || $spiRoleAssignment->contentId == $user->id )
                $roleAssignments[] = $this->buildDomainUserRoleAssignmentObject( $spiRoleAssignment, $user );
            else
                $roleAssignments[] = $this->buildDomainUserGroupRoleAssignmentObject( $spiRoleAssignment );
        }

        return $roleAssignments;
    }

    /**
     * Returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[]
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        if ( !is_numeric( $userGroup->id ) )
            throw new InvalidArgumentValue( "id", $userGroup->id, "UserGroup" );

        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $roleAssignments = array();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId( $userGroup->id );
        foreach ( $spiRoleAssignments as $spiRoleAssignment )
        {
            $roleAssignments[] = $this->buildDomainUserGroupRoleAssignmentObject( $spiRoleAssignment, $userGroup );
        }

        return $roleAssignments;
    }

    /**
     * instantiates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new RoleCreateStruct(
            array(
                'identifier' => $name,
                'policies' => array()
            )
        );
    }

    /**
     * instantiates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStruct(
            array(
                'module' => $module,
                'function' => $function,
                'limitations' => array()
            )
        );
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStruct(
            array(
                'limitations' => array()
            )
        );
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    /**
     * Maps provided SPI Role value object to API Role value object
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    protected function buildDomainRoleObject( SPIRole $role )
    {
        $rolePolicies = array();
        foreach ( $role->policies as $spiPolicy )
        {
            $rolePolicies[] = $this->buildDomainPolicyObject( $spiPolicy, $role );
        }

        return new Role(
            array(
                'id' => (int)$role->id,
                'identifier' => $role->identifier,
                'policies' => $rolePolicies
            )
        );
    }

    /**
     * Maps provided SPI Policy value object to API Policy value object
     *
     * @access private Only accessible for other services and the internals of the repository
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     * @param \eZ\Publish\SPI\Persistence\User\Role|null $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function buildDomainPolicyObject( SPIPolicy $policy, SPIRole $role = null )
    {
        $policyLimitations = array();
        if ( $policy->module !== '*' && $policy->function !== '*' && $policy->limitations !== '*' )
        {
            foreach ( $policy->limitations as $identifier => $values )
            {
                $policyLimitations[] = $this->getLimitationType( $identifier )->buildValue( $values );
            }
        }

        return new Policy(
            array(
                'id' => (int)$policy->id,
                'roleId' => $role !== null ? (int)$role->id : (int)$policy->roleId,
                'module' => $policy->module,
                'function' => $policy->function,
                'limitations' => $policyLimitations
            )
        );
    }

    /**
     * Builds the API UserRoleAssignment object from provided SPI RoleAssignment object
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment
     */
    public function buildDomainUserRoleAssignmentObject( SPIRoleAssignment $spiRoleAssignment, User $user = null, APIRole $role = null )
    {
        $limitation = null;
        if ( !empty( $spiRoleAssignment->limitationIdentifier ) )
        {
            $limitation = $this
                ->getLimitationType( $spiRoleAssignment->limitationIdentifier )
                ->buildValue( $spiRoleAssignment->values );
        }

        $user = $user ?: $this->repository->getUserService()->loadUser( $spiRoleAssignment->contentId );
        $role = $role ?: $this->buildDomainRoleObject( $spiRoleAssignment->role );

        return new UserRoleAssignment(
            array(
                'limitation' => $limitation,
                'role' => $role,
                'user' => $user
            )
        );
    }

    /**
     * Builds the API UserGroupRoleAssignment object from provided SPI RoleAssignment object
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleAssignment $spiRoleAssignment
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment
     */
    public function buildDomainUserGroupRoleAssignmentObject( SPIRoleAssignment $spiRoleAssignment, UserGroup $userGroup = null, APIRole $role = null )
    {
        $limitation = null;
        if ( !empty( $spiRoleAssignment->limitationIdentifier ) )
        {
            $limitation = $this
                ->getLimitationType( $spiRoleAssignment->limitationIdentifier )
                ->buildValue( $spiRoleAssignment->values );
        }

        $userGroup = $userGroup ?: $this->repository->getUserService()->loadUserGroup( $spiRoleAssignment->contentId );
        $role = $role ?: $this->buildDomainRoleObject( $spiRoleAssignment->role );

        return new UserGroupRoleAssignment(
            array(
                'limitation' => $limitation,
                'role' => $role,
                'userGroup' => $userGroup
            )
        );
    }

    /**
     * Returns the LimitationType registered with the given identifier
     *
     * Returns the correct implementation of API Limitation value object
     * based on provided identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if there is no LimitationType with $identifier
     */
    public function getLimitationType( $identifier )
    {
        if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
            throw new NotFoundException( 'Limitation', $identifier );

        return $this->settings['limitationTypes'][$identifier];
    }

    /**
     * Returns the LimitationType's assigned to a given module/function
     *
     * Typically used for:
     *  - Internal validation limitation value use on Policies
     *  - Role admin gui for editing policy limitations incl list limitation options via valueSchema()
     *
     * @param string $module Legacy name of "controller", it's a unique identifier like "content"
     * @param string $function Legacy name of a controller "action", it's a unique within the controller like "read"
     *
     * @return \eZ\Publish\SPI\Limitation\Type[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If module/function to limitation type mapping
     *                                                                 refers to a non existing identifier.
     */
    public function getLimitationTypesByModuleFunction( $module, $function )
    {
        if ( empty( $this->settings['limitationMap'][$module][$function] ) )
            return array();

        $types = array();
        foreach ( $this->settings['limitationMap'][$module][$function] as $identifier )
        {
            if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
            {
                throw new \eZ\Publish\Core\Base\Exceptions\BadStateException(
                    '$identifier',
                    "'{$identifier}' does not exists but was configured as limitation on {$module}/{$function}"
                );
            }
            $types[$identifier] = $this->settings['limitationTypes'][$identifier];
        }
        return $types;
    }

    /**
     * Creates SPI Role value object from provided API role create struct
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\User\Role
     */
    protected function buildPersistenceRoleObject( APIRoleCreateStruct $roleCreateStruct )
    {
        $policiesToCreate = array();
        foreach ( $roleCreateStruct->getPolicies() as $policyCreateStruct )
        {
            $policiesToCreate[] = $this->buildPersistencePolicyObject(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );
        }

        return new SPIRole(
            array(
                'identifier' => $roleCreateStruct->identifier,
                'policies' => $policiesToCreate
            )
        );
    }

    /**
     * Creates SPI Policy value object from provided module, function and limitations
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\SPI\Persistence\User\Policy
     */
    protected function buildPersistencePolicyObject( $module, $function, array $limitations )
    {
        $limitationsToCreate = '*';
        if ( $module !== '*' && $function !== '*' && !empty( $limitations ) )
        {
            $limitationsToCreate = array();
            foreach ( $limitations as $limitation )
            {
                $limitationsToCreate[$limitation->getIdentifier()] = $limitation->limitationValues;
            }
        }

        return new SPIPolicy(
            array(
                'module' => $module,
                'function' => $function,
                'limitations' => $limitationsToCreate
            )
        );
    }
}
