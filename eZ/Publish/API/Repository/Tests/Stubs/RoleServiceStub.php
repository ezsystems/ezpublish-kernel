<?php
/**
 * File containing the RoleServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\RoleService;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use \eZ\Publish\API\Repository\Values\User\Policy;
use \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\Role;
use \eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use \eZ\Publish\API\Repository\Values\User\User;
use \eZ\Publish\API\Repository\Values\User\UserGroup;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyCreateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyUpdateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\RoleCreateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserRoleAssignmentStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserGroupRoleAssignmentStub;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\RoleService}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\RoleService
 */
class RoleServiceStub implements RoleService
{
    /**
     * @var integer
     */
    private $nextRoleId = 0;

    /**
     * @var array
     */
    private $nameToRoleId = array();

    /**
     * @var \eZ\Publish\API\Repository\Values\User\Role[]
     */
    private $roles;

    /**
     * Temporary solution to emulate user policies.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    private $policies = array();

    /**
     * @var integer
     */
    private $policyNextId = 0;

    /**
     * @var integer
     */
    private $limitationId = 10000;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\UserServiceStub
     */
    private $userService;

    /**
     * @var integer[integer[]]
     */
    private $content2roles;

    /**
     * @var integer[]
     */
    private $role2policy;

    /**
     * @var array[]
     */
    private $roleLimitations;

    /**
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     * @param \eZ\Publish\API\Repository\Tests\Stubs\UserServiceStub $userService
     */
    public function __construct( RepositoryStub $repository, UserServiceStub $userService )
    {
        $this->repository = $repository;
        $this->userService = $userService;

        $this->initFromFixture();
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
    public function createRole( RoleCreateStruct $roleCreateStruct )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( isset( $this->nameToRoleId[$roleCreateStruct->identifier] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $role = new RoleStub(
            array(
                'id' => ++$this->nextRoleId,
                'identifier' => $roleCreateStruct->identifier,
                'names' => $roleCreateStruct->names,
                'descriptions' => $roleCreateStruct->descriptions
            )
        );

        $this->roles[$role->id] = $role;
        $this->nameToRoleId[$role->identifier] = $role->id;

        foreach ( $roleCreateStruct->getPolicies() as $policy )
        {
            $role = $this->addPolicy( $role, $policy );
        }
        return $role;
    }

    /**
     * Updates the name and (5.x) description of the role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( Role $role, RoleUpdateStruct $roleUpdateStruct )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $roleName = $roleUpdateStruct->identifier ?: $role->identifier;

        if ( isset( $this->nameToRoleId[$roleName] ) && $this->nameToRoleId[$roleName] !== $role->id )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $updatedRole = new RoleStub(
            array(
                'id' => $role->id,
                'identifier' => $roleName,
                'names' => $roleUpdateStruct->names ?: $role->getNames(),
                'descriptions' => $roleUpdateStruct->descriptions ?: $role->getDescriptions()
            )
        );

        unset( $this->roles[$role->id], $this->nameToRoleId[$role->identifier] );

        $this->roles[$updatedRole->id] = $updatedRole;
        $this->nameToRoleId[$updatedRole->identifier] = $updatedRole->id;

        return $this->roles[$updatedRole->id];
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
    public function addPolicy( Role $role, PolicyCreateStruct $policyCreateStruct )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $this->policies[++$this->policyNextId] = new PolicyStub(
            array(
                'id' => $this->policyNextId,
                'roleId' => $role->id,
                'module' => $policyCreateStruct->module,
                'function' => $policyCreateStruct->function
            )
        );

        $policies = $role->getPolicies();
        $policies[] = $this->policies[$this->policyNextId];

        $this->roles[$role->id] = new RoleStub(
            array(
                'id' => $role->id,
                'identifier' => $role->identifier,
                'names' => $role->getNames(),
                'descriptions' => $role->getDescriptions()
            ),
            $policies
        );

        $this->role2policy[$role->id][$this->policyNextId] = $this->policyNextId;

        return $this->roles[$role->id];
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
    public function removePolicy( Role $role, Policy $policy )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $policies = array();
        foreach ( $role->getPolicies() as $rolePolicy )
        {
            if ( $rolePolicy->id !== $policy->id )
            {
                $policies[] = $rolePolicy;
            }
        }

        unset(
            $this->policies[$policy->id],
            $this->role2policy[$role->id][$policy->id]
        );

        $this->roles[$role->id] = new RoleStub(
            array(
                'id' => $role->id,
                'identifier' => $role->identifier,
                'names' => $role->getNames(),
                'descriptions' => $role->getDescriptions()
            ),
            $policies
        );

        return $this->roles[$role->id];
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitaions are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to u�date a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( Policy $policy, PolicyUpdateStruct $policyUpdateStruct )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $newPolicy = new PolicyStub(
            array(
                'id' => $policy->id,
                'roleId' => $policy->roleId,
                'module' => $policy->module,
                'function' => $policy->function,
                'limitations' => $policyUpdateStruct->getLimitations()
            )
        );

        $this->policies[$newPolicy->id] = $newPolicy;

        $policies = $this->roles[$policy->roleId]->getPolicies();
        foreach ( $policies as $i => $rolePolicy )
        {
            if ( $rolePolicy->id !== $policy->id )
            {
                continue;
            }

            $policies[$i] = $newPolicy;
            break;
        }

        $this->roles[$policy->roleId] = new RoleStub(
            array(
                'id' => $this->roles[$policy->roleId]->id,
                'identifier' => $this->roles[$policy->roleId]->identifier,
                'names' => $this->roles[$policy->roleId]->getNames(),
                'descriptions' => $this->roles[$policy->roleId]->getDescriptions()
            ),
            $policies
        );

        return $newPolicy;
    }

    /**
     * loads a role for the given id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param mixed $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( $id )
    {
        if ( false === isset( $this->roles[$id] ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->roles[$id];
    }

    /**
     * loads a role for the given name
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read this role
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a role with the given name was not found
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRoleByIdentifier( $name )
    {
        if ( false === isset( $this->nameToRoleId[$name] ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->roles[$this->nameToRoleId[$name]];
    }

    /**
     * loads all roles
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read the roles
     *
     * @return array an array of {@link \eZ\Publish\API\Repository\Values\User\Role}
     */
    public function loadRoles()
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return array_values( $this->roles );
    }

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( Role $role )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        unset(
            $this->roles[$role->id],
            $this->role2policy[$role->id],
            $this->nameToRoleId[$role->identifier]
        );
    }

    /**
     * loads all policies from roles which are assigned to a user or to user groups to which the user belongs
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if a user with the given id was not found
     *
     * @param mixed $userId
     *
     * @return array an array of {@link Policy}
     */
    public function loadPoliciesByUserId( $userId )
    {
        $contentIds = array( $userId );
        foreach ( $this->userService->__loadUserGroupsByUserId( $userId ) as $group )
        {
            $contentIds[] = $group->id;
        }

        $roleIds = array();
        foreach ( $contentIds as $contentId )
        {
            if ( false === isset( $this->content2roles[$contentId] ) )
            {
                continue;
            }
            foreach ( $this->content2roles[$contentId] as $roleId )
            {
                $roleIds[] = $roleId;
            }
        }

        if ( 0 === count( $roleIds ) )
        {
            throw new NotFoundExceptionStub( 'What error code should be used?' );
        }

        $policies = array();
        foreach ( $roleIds as $roleId )
        {
            if ( false === isset( $this->role2policy[$roleId] ) )
            {
                continue;
            }

            foreach ( $this->role2policy[$roleId] as $policyId )
            {
                $policy = $this->policies[$policyId];

                $this->policies[$policyId] = $policies[] = new PolicyStub(
                    array(
                        'id' => $policy->id,
                        'roleId' => $policy->roleId,
                        'module' => $policy->module,
                        'function' => $policy->function,
                        'limitations' => $policy->getLimitations()
                    )
                );
            }
        }
        return $policies;
    }

    /**
     * Loads all policies from a role
     *
     * @private
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy[]
     */
    public function __getRolePolicies( Role $role )
    {
        $policies = array();
        foreach ( $this->role2policy[$role->id] as $policyId )
        {
            $policies[] = $this->policies[$policyId];
        }
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
    public function assignRoleToUserGroup( Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        $this->assignRoleToContent( $role, $userGroup, $roleLimitation );
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
    public function unassignRoleFromUserGroup( Role $role, UserGroup $userGroup )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        $this->unassignRoleFromContent( $role, $userGroup );
    }

    /**
     * assigns a role to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     *
     * @todo add limitations
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser( Role $role, User $user, RoleLimitation $roleLimitation = null )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        $this->assignRoleToContent( $role, $user, $roleLimitation );
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
    public function unassignRoleFromUser( Role $role, User $user )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        $this->unassignRoleFromContent( $role, $user );
    }

    /**
     * returns the assigned user and user groups to this role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[] an array of {@link RoleAssignment}
     */
    public function getRoleAssignments( Role $role )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $roleAssignments = array();
        $processedContentIds = array();
        foreach ( $this->content2roles as $contentId => $roleIds )
        {
            foreach ( $roleIds as $roleId )
            {
                if ( $roleId !== $role->id )
                {
                    continue;
                }

                if ( in_array( $contentId, $processedContentIds ) )
                {
                    continue;
                }

                $roleAssignments = array_merge(
                    $roleAssignments,
                    $this->getRoleAssignmentsForRoleAndContent(
                        $role,
                        $contentId
                    )
                );

                $processedContentIds[] = $contentId;
            }
        }

        return $roleAssignments;
    }

    /**
     * returns the roles assigned to the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserRoleAssignment[] an array of {@link UserRoleAssignment}
     */
    public function getRoleAssignmentsForUser( User $user, $inherited = false )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $roleAssignments =  $this->getRoleAssignmentsForContent( $user );

        if ( $inherited )
        {
            $userGroups = $this->repository->getUserService()->__loadUserGroupsByUserId( $user->id );
            foreach ( $userGroups as $userGroup )
            {
                $roleAssignments = array_merge(
                    $roleAssignments,
                    $this->getRoleAssignmentsForContent( $userGroup )
                );
            }
        }

        return  $roleAssignments;
    }

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment[] an array of {@link UserGroupRoleAssignment}
     */
    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->getRoleAssignmentsForContent( $userGroup );
    }

    /**
     * instanciates a role create class
     *
     * @param string $name
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleCreateStruct
     */
    public function newRoleCreateStruct( $name )
    {
        return new RoleCreateStructStub( $name );
    }

    /**
     * instanciates a policy create class
     *
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct
     */
    public function newPolicyCreateStruct( $module, $function )
    {
        return new PolicyCreateStructStub( $module, $function );
    }

    /**
     * instanciates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        return new PolicyUpdateStructStub();
    }

    /**
     * instanciates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        return new RoleUpdateStruct();
    }

    /**
     * Returns the LimitationType registered with the given identifier
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\SPI\Limitation\Type
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if there is no LimitationType with $identifier
     */
    public function getLimitationType( $identifier )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }

    /**
     * returns the roles assigned to the given user group
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to read a user group
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[] an array of {@link UserGroupRoleAssignment}
     */
    private function getRoleAssignmentsForContent( Content $content )
    {
        if ( false === isset( $this->content2roles[$content->id] ) )
        {
            return array();
        }

        $roleAssignments = array();
        $processedRoleIds = array();
        foreach ( $this->content2roles[$content->id] as $roleId )
        {
            if ( in_array( $roleId, $processedRoleIds ) )
            {
                continue;
            }

            $roleAssignments = array_merge(
                $roleAssignments,
                    $this->getRoleAssignmentsForRoleAndContent(
                    $this->loadRole( $roleId ),
                    $content->id
                )
            );

            $processedRoleIds[] = $roleId;
        }

        return $roleAssignments;
    }

    /**
     * Assigns a role to the given content object.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation
     *
     * return void
     */
    private function assignRoleToContent(Role $role, Content $content, RoleLimitation $roleLimitation = null )
    {
        if ( false === isset( $this->content2roles[$content->id] ) )
        {
            $this->content2roles[$content->id] = array();
        }

        if ( $roleLimitation )
        {
            foreach ( $roleLimitation->limitationValues as $value )
            {
                $this->content2roles[$content->id][++$this->limitationId] = $role->id;
                $this->roleLimitations[$this->limitationId] = array(
                    'id' => $this->limitationId,
                    'roleId' => $role->id,
                    'contentId' => $content->id,
                    'identifier' => $roleLimitation->getIdentifier(),
                    'value' => array( $value )
                );
            }
        }
        else
        {
            $this->content2roles[$content->id][++$this->limitationId] = $role->id;
        }
    }

    /**
     * removes a role from the given content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     */
    private function unassignRoleFromContent( Role $role, Content $content )
    {
        if ( false === isset( $this->content2roles[$content->id] )
            || false === in_array( $role->id, $this->content2roles[$content->id] ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $index = array_search( $role->id, $this->content2roles[$content->id] );
        unset(
            $this->content2roles[$content->id][$index],
            $this->roleLimitations[$index]
        );
    }

    /**
     * Returns list of role assignments for specific role and content.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param integer $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment[]
     */
    private function getRoleAssignmentsForRoleAndContent( Role $role, $contentId )
    {
        $contentService = $this->repository->getContentService();
        $userService = $this->repository->getUserService();

        $contentType = $contentService->loadContent( $contentId )->contentType;
        if ( $contentType->identifier != 'user_group' && $contentType->identifier != 'user' )
        {
            throw new \ErrorException(
                "Implementation error, unknown contentType '{$contentType->identifier}'."
            );
        }

        $roleAssignments = array();
        foreach ( $this->roleLimitations as $limit )
        {
            if ( $limit['roleId'] == $role->id && $limit['contentId'] == $contentId )
            {
                $limitIdentifier = $limit['identifier'];
                if ( !isset( $roleAssignments[$limitIdentifier] ) )
                {
                    if ( 'user_group' === $contentType->identifier )
                    {
                        $roleAssignments[$limitIdentifier] = new UserGroupRoleAssignmentStub(
                            array(
                                'role' => $role,
                                'userGroup' => $userService->loadUserGroup( $contentId ),
                                'limitation' => $this->getOptionalRoleLimitation( $role->id, $contentId, $limitIdentifier ),
                            )
                        );
                    }
                    else if ( 'user' === $contentType->identifier )
                    {
                        $roleAssignments[$limitIdentifier] = new UserRoleAssignmentStub(
                            array(
                                'role' => $role,
                                'user' => $userService->loadUser( $contentId ),
                                'limitation' => $this->getOptionalRoleLimitation( $role->id, $contentId, $limitIdentifier ),
                            )
                        );
                    }
                }
            }
        }

        if ( empty( $roleAssignments ) )
        {
            if ( 'user_group' === $contentType->identifier )
            {
                $roleAssignments[] = new UserGroupRoleAssignmentStub(
                    array(
                        'role' => $role,
                        'userGroup' => $userService->loadUserGroup( $contentId ),
                        'limitation' => null,
                    )
                );
            }
            else if ( 'user' === $contentType->identifier )
            {
                $roleAssignments[] = new UserRoleAssignmentStub(
                    array(
                        'role' => $role,
                        'user' => $userService->loadUser( $contentId ),
                        'limitation' => null,
                    )
                );
            }
        }

        return array_values( $roleAssignments );
    }

    /**
     * Returns the associated limitation or <b>NULL</b>.
     *
     * @param integer $roleId
     * @param integer $contentId
     * @param string $limitationIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    private function getOptionalRoleLimitation( $roleId, $contentId, $limitationIdentifier )
    {
        $limitationValues = array();
        foreach ( $this->roleLimitations as $limit )
        {
            if ( $roleId == $limit['roleId'] && $contentId == $limit['contentId'] && $limitationIdentifier == $limit['identifier'] )
            {
                $limitationValues = array_merge( $limitationValues, $limit['value'] );
            }
        }

        if ( empty( $limitationValues ) )
        {
            return null;
        }

        switch ( $limitationIdentifier )
        {
            case 'Subtree':
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation(
                    array(
                        'limitationValues' => $limitationValues
                    )
                );

            case 'Section':
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation(
                    array(
                        'limitationValues' => $limitationValues
                    )
                );
        }
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @return void
     */
    public function __rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list(
            $this->roles,
            $this->nameToRoleId,
            $this->nextRoleId,
            $this->content2roles,
            $this->policies,
            $this->policyNextId,
            $this->role2policy,
            $this->roleLimitations
        ) = $this->repository->loadFixture( 'Role' );
    }
}
