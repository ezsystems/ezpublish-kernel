<?php
/**
 * File containing the eZ\Publish\Core\Repository\RoleService class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\Core\Base\Exceptions\LimitationValidationException;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\Policy as APIPolicy;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct as APIPolicyCreateStruct;
use eZ\Publish\Core\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\Role as APIRole;
use eZ\Publish\Core\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct as APIRoleCreateStruct;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\Core\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Persistence\User\Policy as SPIPolicy;
use eZ\Publish\SPI\Persistence\User\RoleAssignment as SPIRoleAssignment;
use eZ\Publish\SPI\Persistence\User\Role as SPIRole;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct as SPIRoleUpdateStruct;
use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\User\Handler;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use Exception;

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
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            'limitationTypes' => array(),
            'limitationMap' => array(
                // @todo Inject these dynamically by activated eZ Controllers, see PR #252
                'content' => array(
                    'read' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'State' => true ),
                    'diff' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true ),
                    'view_embed' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true ),
                    'create' => array( 'Class' => true, 'Section' => true, 'ParentOwner' => true, 'ParentGroup' => true, 'ParentClass' => true, 'ParentDepth' => true, 'Node' => true, 'Subtree' => true, 'Language' => true ),
                    'edit' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'Language' => true, 'State' => true ),
                    'manage_locations' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Subtree' => true ),
                    'hide' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'Language' => true ),
                    'translate' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true, 'Language' => true ),
                    'remove' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true, 'State' => true ),
                    'versionread' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Status' => true, 'Node' => true, 'Subtree' => true ),
                    'versionremove' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Status' => true, 'Node' => true, 'Subtree' => true ),
                    'pdf' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Node' => true, 'Subtree' => true ),
                ),
                'section' => array(
                    'assign' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'NewSection' => true ),
                ),
                'state' => array(
                    // @todo 'NewState' Limitation and Limitation type is missing (like 'NewSection')
                    'assign' => array( 'Class' => true, 'Section' => true, 'Owner' => true, 'Group' => true, 'Node' => true, 'Subtree' => true, 'State' => true, 'NewState' => true ),
                ),
                'user' => array(
                    'login' => array( 'SiteAccess' => true ),
                )
            ),
        );
    }

    /**
     * Creates a new Role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists or if limitation of the
     *                                                                        same type is repeated in the policy create struct or if
     *                                                                        limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
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

        $limitationValidationErrors = $this->validateRoleCreateStruct( $roleCreateStruct );
        if ( !empty( $limitationValidationErrors ) )
        {
            throw new LimitationValidationException( $limitationValidationErrors );
        }

        $spiRole = $this->buildPersistenceRoleObject( $roleCreateStruct );

        $this->repository->beginTransaction();
        try
        {
            $createdRole = $this->userHandler->createRole( $spiRole );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
        if ( $roleUpdateStruct->identifier !== null && !is_string( $roleUpdateStruct->identifier ) )
            throw new InvalidArgumentValue( "identifier", $roleUpdateStruct->identifier, "RoleUpdateStruct" );

        $loadedRole = $this->loadRole( $role->id );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        if ( $roleUpdateStruct->identifier !== null )
        {
            try
            {
                $existingRole = $this->loadRoleByIdentifier( $roleUpdateStruct->identifier );

                if ( $existingRole->id != $loadedRole->id )
                {
                    throw new InvalidArgumentException(
                        "\$roleUpdateStruct",
                        "Role with provided identifier already exists"
                    );
                }
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->updateRole(
                new SPIRoleUpdateStruct(
                    array(
                        'id' => $loadedRole->id,
                        'identifier' => $roleUpdateStruct->identifier ?: $loadedRole->identifier
                    )
                )
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * Adds a new policy to the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function addPolicy( APIRole $role, APIPolicyCreateStruct $policyCreateStruct )
    {
        if ( !is_string( $policyCreateStruct->module ) || empty( $policyCreateStruct->module ) )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( !is_string( $policyCreateStruct->function ) || empty( $policyCreateStruct->function ) )
            throw new InvalidArgumentValue( "function", $policyCreateStruct->function, "PolicyCreateStruct" );

        if ( $policyCreateStruct->module === '*' && $policyCreateStruct->function !== '*' )
            throw new InvalidArgumentValue( "module", $policyCreateStruct->module, "PolicyCreateStruct" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $loadedRole = $this->loadRole( $role->id );

        $limitations = $policyCreateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );
        if ( !empty( $limitationValidationErrors ) )
        {
            throw new LimitationValidationException( $limitationValidationErrors );
        }

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policyCreateStruct->module,
            $policyCreateStruct->function,
            $limitations
        );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->addPolicy( $loadedRole->id, $spiPolicy );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadRole( $loadedRole->id );
    }

    /**
     * removes a policy from the role
     *
     * @deprecated since 5.3, use {@link deletePolicy()} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if policy does not belong to the given role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to remove from the role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role the updated role
     */
    public function removePolicy( APIRole $role, APIPolicy $policy )
    {
        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        if ( $policy->roleId != $role->id )
        {
            throw new InvalidArgumentException( "\$policy", "Policy does not belong to the given role" );
        }

        $this->internalDeletePolicy( $policy );

        return $this->loadRole( $role->id );
    }

    /**
     * Deletes a policy
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to delete
     */
    public function deletePolicy( APIPolicy $policy )
    {
        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $this->internalDeletePolicy( $policy );
    }

    /**
     * Deletes a policy
     *
     * Used by {@link removePolicy()} and {@link deletePolicy()}
     *
     * @param APIPolicy $policy
     *
     * @throws \Exception
     *
     * @return void
     */
    protected function internalDeletePolicy( APIPolicy $policy )
    {
        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->deletePolicy( $policy->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( APIPolicy $policy, APIPolicyUpdateStruct $policyUpdateStruct )
    {
        if ( !is_string( $policy->module ) )
            throw new InvalidArgumentValue( "module", $policy->module, "Policy" );

        if ( !is_string( $policy->function ) )
            throw new InvalidArgumentValue( "function", $policy->function, "Policy" );

        if ( $this->repository->hasAccess( 'role', 'update' ) !== true )
            throw new UnauthorizedException( 'role', 'update' );

        $limitations = $policyUpdateStruct->getLimitations();
        $limitationValidationErrors = $this->validatePolicy(
            $policy->module,
            $policy->function,
            $limitations
        );
        if ( !empty( $limitationValidationErrors ) )
        {
            throw new LimitationValidationException( $limitationValidationErrors );
        }

        $spiPolicy = $this->buildPersistencePolicyObject(
            $policy->module,
            $policy->function,
            $limitations
        );
        $spiPolicy->id = $policy->id;
        $spiPolicy->roleId = $policy->roleId;

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->updatePolicy( $spiPolicy );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildDomainPolicyObject( $spiPolicy );
    }

    /**
     * Loads a role for the given id
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
        if ( $this->repository->hasAccess( 'role', 'read' ) !== true )
            throw new UnauthorizedException( 'role', 'read' );

        $spiRole = $this->userHandler->loadRole( $id );
        return $this->buildDomainRoleObject( $spiRole );
    }

    /**
     * Loads a role for the given identifier
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
     * Loads all roles
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
     * Deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( APIRole $role )
    {
        if ( $this->repository->hasAccess( 'role', 'delete' ) !== true )
            throw new UnauthorizedException( 'role', 'delete' );

        $loadedRole = $this->loadRole( $role->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->deleteRole( $loadedRole->id );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param mixed $userId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
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
     * Assigns a role to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup( APIRole $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        if ( $this->repository->canUser( 'role', 'assign', $userGroup, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        if ( $roleLimitation === null )
        {
            $limitation = null;
        }
        else
        {
            $limitationValidationErrors = $this->validateLimitation( $roleLimitation );
            if ( !empty( $limitationValidationErrors ) )
            {
                throw new LimitationValidationException( $limitationValidationErrors );
            }

            $limitation = array( $roleLimitation->getIdentifier() => $roleLimitation->limitationValues );
        }

        $loadedRole = $this->loadRole( $role->id );
        $loadedUserGroup = $this->repository->getUserService()->loadUserGroup( $userGroup->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->assignRole(
                $loadedUserGroup->id,
                $loadedRole->id,
                $limitation
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Assigns a role to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser( APIRole $role, User $user, RoleLimitation $roleLimitation = null )
    {
        if ( $this->repository->canUser( 'role', 'assign', $user, $role ) !== true )
            throw new UnauthorizedException( 'role', 'assign' );

        if ( $roleLimitation === null )
        {
            $limitation = null;
        }
        else
        {
            $limitationValidationErrors = $this->validateLimitation( $roleLimitation );
            if ( !empty( $limitationValidationErrors ) )
            {
                throw new LimitationValidationException( $limitationValidationErrors );
            }

            $limitation = array( $roleLimitation->getIdentifier() => $roleLimitation->limitationValues );
        }

        $loadedRole = $this->loadRole( $role->id );
        $loadedUser = $this->repository->getUserService()->loadUser( $user->id );

        $this->repository->beginTransaction();
        try
        {
            $this->userHandler->assignRole(
                $loadedUser->id,
                $loadedRole->id,
                $limitation
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
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
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    public function getRoleAssignments( APIRole $role )
    {
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
                    if ( $spiRoleAssignment->roleId == $role->id )
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
                        if ( $spiRoleAssignment->roleId == $role->id )
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
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param boolean $inherited
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\UnauthorizedException If the current user is not allowed to read a role
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue On invalid User object
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[]
     */
    public function getRoleAssignmentsForUser( User $user, $inherited = false )
    {
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
     * Instantiates a role create class
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
     * Instantiates a policy create class
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
     * Instantiates a policy update class
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
     * Instantiates a policy update class
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
                'id' => $role->id,
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
                'id' => $policy->id,
                'roleId' => $role !== null ? $role->id : $policy->roleId,
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
        $role = $role ?: $this->loadRole( $spiRoleAssignment->roleId );

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
        $role = $role ?: $this->loadRole( $spiRoleAssignment->roleId );

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
        foreach ( array_keys( $this->settings['limitationMap'][$module][$function] ) as $identifier )
        {
            if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
            {
                throw new BadStateException(
                    '$identifier',
                    "limitationType[{$identifier}] is not configured but was configured on limitationMap[{$module}][{$function}]"
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
        $limitationsToCreate = "*";
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

    /**
     * Validates Policies and Limitations in Role create struct.
     *
     * @uses validatePolicy()
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][][]
     */
    protected function validateRoleCreateStruct( APIRoleCreateStruct $roleCreateStruct )
    {
        $allErrors = array();
        foreach ( $roleCreateStruct->getPolicies() as $key => $policyCreateStruct )
        {
            $errors = $this->validatePolicy(
                $policyCreateStruct->module,
                $policyCreateStruct->function,
                $policyCreateStruct->getLimitations()
            );

            if ( !empty( $errors ) )
            {
                $allErrors[$key] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Validates Policy context: Limitations on a module and function.
     *
     * @uses validateLimitations()
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the same limitation is repeated or if
     *                                                                   limitation is not allowed on module/function
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][]
     */
    protected function validatePolicy( $module, $function, array $limitations )
    {
        if ( $module !== '*' && $function !== '*' && !empty( $limitations ) )
        {
            $limitationSet = array();
            foreach ( $limitations as $limitation )
            {
                if ( isset( $limitationSet[$limitation->getIdentifier()] ) )
                {
                    throw new InvalidArgumentException(
                        "limitations",
                        "'{$limitation->getIdentifier()}' was found several times among the limitations"
                    );
                }

                if ( !isset( $this->settings['limitationMap'][$module][$function][$limitation->getIdentifier()] ) )
                {
                    throw new InvalidArgumentException(
                        "policy",
                        "The limitation '{$limitation->getIdentifier()}' is not applicable on '{$module}/{$function}'"
                    );
                }

                $limitationSet[$limitation->getIdentifier()] = true;
            }
        }

        return $this->validateLimitations( $limitations );
    }

    /**
     * Validates an array of Limitations.
     *
     * @uses validateLimitation()
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[][]
     */
    protected function validateLimitations( array $limitations )
    {
        $allErrors = array();
        foreach ( $limitations as $limitation )
        {
            $errors = $this->validateLimitation( $limitation );
            if ( !empty( $errors ) )
            {
                $allErrors[$limitation->getIdentifier()] = $errors;
            }
        }

        return $allErrors;
    }

    /**
     * Validates single Limitation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException If the Role settings is in a bad state
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     *
     * @return \eZ\Publish\Core\FieldType\ValidationError[]
     */
    protected function validateLimitation( Limitation $limitation )
    {
        $identifier = $limitation->getIdentifier();
        if ( !isset( $this->settings['limitationTypes'][$identifier] ) )
        {
            throw new BadStateException(
                '$identifier',
                "limitationType[{$identifier}] is not configured"
            );
        }

        /**
         * @var $type \eZ\Publish\SPI\Limitation\Type
         */
        $type = $this->settings['limitationTypes'][$identifier];

        // This will throw if it does not pass
        $type->acceptValue( $limitation );

        // This return array of validation errors
        return $type->validate( $limitation );
    }
}
