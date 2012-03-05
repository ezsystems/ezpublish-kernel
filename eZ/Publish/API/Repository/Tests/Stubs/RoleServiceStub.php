<?php
/**
 * File containing the RoleServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\Repository;
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
        $this->repository  = $repository;
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
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }
        if ( isset( $this->nameToRoleId[$roleCreateStruct->identifier] ) )
        {
            throw new InvalidArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $role = new RoleStub(
            array(
                'id'            =>  ++$this->nextRoleId,
                'identifier'    =>  $roleCreateStruct->identifier,
                'names'         =>  $roleCreateStruct->names,
                'descriptions'  =>  $roleCreateStruct->descriptions
            )
        );

        $this->roles[$role->id]                = $role;
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
        if ( false === $this->repository->canUser( 'role', '*', $role ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }

        $roleName = $roleUpdateStruct->identifier ?: $role->identifier;

        if ( isset( $this->nameToRoleId[$roleName] ) && $this->nameToRoleId[$roleName] !== $role->id )
        {
            throw new InvalidArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $updatedRole = new RoleStub(
            array(
                'id'            =>  $role->id,
                'identifier'    =>  $roleName,
                'names'         =>  $roleUpdateStruct->names ?: $role->getNames(),
                'descriptions'  =>  $roleUpdateStruct->descriptions ?: $role->getDescriptions()
            )
        );

        unset( $this->roles[$role->id], $this->nameToRoleId[$role->identifier] );

        $this->roles[$updatedRole->id]                = $updatedRole;
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
        if ( false === $this->repository->canUser( 'role', '*', $role ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }

        $this->policies[++$this->policyNextId] = new PolicyStub(
            array(
                'id'        =>  $this->policyNextId,
                'roleId'    =>  $role->id,
                'module'    =>  $policyCreateStruct->module,
                'function'  =>  $policyCreateStruct->function
            )
        );

        $policies   = $role->getPolicies();
        $policies[] = $this->policies[$this->policyNextId];

        $this->roles[$role->id] = new RoleStub(
            array(
                'id'            =>  $role->id,
                'identifier'    =>  $role->identifier,
                'names'         =>  $role->getNames(),
                'descriptions'  =>  $role->getDescriptions()
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
        if ( false === $this->repository->canUser( 'role', '*', $role, $policy ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
                'id'            =>  $role->id,
                'identifier'    =>  $role->identifier,
                'names'         =>  $role->getNames(),
                'descriptions'  =>  $role->getDescriptions()
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
        if ( false === $this->repository->canUser( 'role', '*', $policy ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }

        $newPolicy = new PolicyStub(
            array(
                'id'           =>  $policy->id,
                'roleId'       =>  $policy->roleId,
                'module'       =>  $policy->module,
                'function'     =>  $policy->function,
                'limitations'  =>  $policyUpdateStruct->getLimitations()
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
                'id'            =>  $this->roles[$policy->roleId]->id,
                'identifier'    =>  $this->roles[$policy->roleId]->identifier,
                'names'         =>  $this->roles[$policy->roleId]->getNames(),
                'descriptions'  =>  $this->roles[$policy->roleId]->getDescriptions()
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
            throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
        }
        if ( false === $this->repository->canUser( 'role', '*', $this->roles[$id] ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
            throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
        }
        if ( false === $this->repository->canUser( 'role', '*', $this->roles[$this->nameToRoleId[$name]] ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
        if ( false === $this->repository->canUser( 'role', '*', $role ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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

        $limits  = array();
        $roleIds = array();
        foreach ( $contentIds as $contentId )
        {
            if ( false === isset( $this->content2roles[$contentId] ) )
            {
                continue;
            }
            foreach ( $this->content2roles[$contentId] as $limitationId => $roleId)
            {
                $roleIds[] = $roleId;

                if ( $limitation = $this->getOptionalRoleLimitation( $limitationId ) )
                {
                    if ( false === isset( $limits[$roleId] ) )
                    {
                        $limits[$roleId] = array();
                    }
                    $limits[$roleId][] = $limitation;
                }
            }
        }

        if ( 0 === count( $roleIds ) )
        {
            throw new NotFoundExceptionStub( '@TODO: What error code should be used?' );
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

                $limitations = $policy->getLimitations();
                if ( isset( $limits[$policy->roleId] ) )
                {
                    foreach ( $limits[$policy->roleId] as $limit )
                    {
                        $limitations[] = $limit;
                    }
                }

                $this->policies[$policyId] = $policies[] = new PolicyStub(
                    array(
                        'id'           =>  $policy->id,
                        'roleId'       =>  $policy->roleId,
                        'module'       =>  $policy->module,
                        'function'     =>  $policy->function,
                        'limitations'  =>  $limitations
                    )
                );
            }
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
        if ( false === $this->repository->canUser( 'role', '*', $role, $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
        if ( false === $this->repository->canUser( 'role', '*', $role, $userGroup ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
        if ( false === $this->repository->canUser( 'role', '*', $role, $user ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
        if ( false === $this->repository->canUser( 'role', '*', $role, $user ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }

        $roleAssignments = array();
        foreach ( $this->content2roles as $contentId => $roleIds )
        {
            foreach ( $roleIds as $limitationId => $roleId )
            {
                if ( $roleId !== $role->id )
                {
                    continue;
                }

                $roleAssignments[] = $this->getRoleAssignment(
                    $role,
                    $contentId,
                    $limitationId
                );
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
    public function getRoleAssignmentsForUser( User $user )
    {
        if ( false === $this->repository->hasAccess( 'role', '*' ) )
        {
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
        }
        return $this->getRoleAssignmentsForContent( $user );
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
            throw new UnauthorizedExceptionStub( '@TODO: What error code should be used?' );
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
        if ( false === isset( $this->content2roles[$content->contentId] ) )
        {
            return array();
        }

        $roleAssignments = array();
        foreach ( $this->content2roles[$content->contentId] as $limitationId => $roleId )
        {
            $roleAssignments[] = $this->getRoleAssignment(
                $this->loadRole( $roleId ),
                $content->contentId,
                $limitationId
            );
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
        if ( false === isset( $this->content2roles[$content->contentId] ) )
        {
            $this->content2roles[$content->contentId] = array();
        }
        $this->content2roles[$content->contentId][++$this->limitationId] = $role->id;

        if ( $roleLimitation )
        {
            $this->roleLimitations[$this->limitationId] = array(
                'identifier'  =>  $roleLimitation->getIdentifier(),
                'value'       =>  $roleLimitation->limitationValues
            );
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
        if ( false === isset( $this->content2roles[$content->contentId] )
            || false === in_array( $role->id, $this->content2roles[$content->contentId] ) )
        {
            throw new InvalidArgumentExceptionStub( '@TODO: What error code should be used?' );
        }

        $index = array_search( $role->id, $this->content2roles[$content->contentId] );
        unset(
            $this->content2roles[$content->contentId][$index],
            $this->roleLimitations[$index]
        );
    }

    /**
     * Returns a role assignment instance.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param integer $contentId
     * @param integer $limitationId
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleAssignment
     */
    private function getRoleAssignment( Role $role, $contentId, $limitationId )
    {
        $contentService = $this->repository->getContentService();
        $userService    = $this->repository->getUserService();

        $contentType = $contentService->loadContent( $contentId )->contentType;
        if ( 'user_group' === $contentType->identifier )
        {
            return new UserGroupRoleAssignmentStub(
                array(
                    'role'        =>  $role,
                    'userGroup'   =>  $userService->loadUserGroup( $contentId ),
                    'limitation'  =>  $this->getOptionalRoleLimitation( $limitationId ),
                )
            );
        }
        else if ( 'user' === $contentType->identifier )
        {
            return new UserRoleAssignmentStub(
                array(
                    'role'        =>  $role,
                    'user'        =>  $userService->loadUser( $contentId ),
                    'limitation'  =>  $this->getOptionalRoleLimitation( $limitationId ),
                )
            );
        }
        throw new \ErrorException(
            "Implementation error, unknown contentType '{$contentType->identifier}'."
        );
    }

    /**
     * Returns the associated limitation or <b>NULL</b>.
     *
     * @param string $limitationId
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation
     */
    private function getOptionalRoleLimitation( $limitationId )
    {
        if ( false === isset( $this->roleLimitations[$limitationId] ) )
        {
            return null;
        }

        switch ( $this->roleLimitations[$limitationId]['identifier'] )
        {
            case 'Subtree':
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation(
                    array(
                        'limitationValues' => $this->roleLimitations[$limitationId]['value']
                    )
                );

            case 'Section':
                return new \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation(
                    array(
                        'limitationValues' => $this->roleLimitations[$limitationId]['value']
                    )
                );
        }

        throw new \ErrorException(
            "Implementation error, unknown limitation identifier '" .
            $this->roleLimitations[$limitationId]['identifier'] . "'."
        );
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
