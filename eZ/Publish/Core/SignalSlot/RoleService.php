<?php
/**
 * RoleService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\RoleService as RoleServiceInterface,

/**
 * RoleService class
 * @package eZ\Publish\Core\SignalSlot
 */
class RoleService implements RoleServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\RoleService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( RoleServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
    public function createRole( eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct )
    {
        $returnValue = $this->service->createRole( $roleCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\CreateRoleSignal( array(
                'roleCreateStruct' => $roleCreateStruct,
            ) )
        );
        return $returnValue;
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
    public function updateRole( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct )
    {
        $returnValue = $this->service->updateRole( $role, $roleUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\UpdateRoleSignal( array(
                'role' => $role,
                'roleUpdateStruct' => $roleUpdateStruct,
            ) )
        );
        return $returnValue;
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
    public function addPolicy( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct )
    {
        $returnValue = $this->service->addPolicy( $role, $policyCreateStruct );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\AddPolicySignal( array(
                'role' => $role,
                'policyCreateStruct' => $policyCreateStruct,
            ) )
        );
        return $returnValue;
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
    public function removePolicy( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\Policy $policy )
    {
        $returnValue = $this->service->removePolicy( $role, $policy );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\RemovePolicySignal( array(
                'role' => $role,
                'policy' => $policy,
            ) )
        );
        return $returnValue;
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to u�date a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy
     *
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( eZ\Publish\API\Repository\Values\User\Policy $policy, eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct )
    {
        $returnValue = $this->service->updatePolicy( $policy, $policyUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\UpdatePolicySignal( array(
                'policy' => $policy,
                'policyUpdateStruct' => $policyUpdateStruct,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadRole( $id );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\LoadRoleSignal( array(
                'id' => $id,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadRoleByIdentifier( $identifier );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\LoadRoleByIdentifierSignal( array(
                'identifier' => $identifier,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadRoles();
        $this->signalDispatcher()->emit(
            new Signal\RoleService\LoadRolesSignal( array(
            ) )
        );
        return $returnValue;
    }

    /**
     * deletes the given role
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole( eZ\Publish\API\Repository\Values\User\Role $role )
    {
        $returnValue = $this->service->deleteRole( $role );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\DeleteRoleSignal( array(
                'role' => $role,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadPoliciesByUserId( $userId );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\LoadPoliciesByUserIdSignal( array(
                'userId' => $userId,
            ) )
        );
        return $returnValue;
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
    public function assignRoleToUserGroup( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\UserGroup $userGroup, eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation = null )
    {
        $returnValue = $this->service->assignRoleToUserGroup( $role, $userGroup, $roleLimitation );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\AssignRoleToUserGroupSignal( array(
                'role' => $role,
                'userGroup' => $userGroup,
                'roleLimitation' => $roleLimitation,
            ) )
        );
        return $returnValue;
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
    public function unassignRoleFromUserGroup( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\UserGroup $userGroup )
    {
        $returnValue = $this->service->unassignRoleFromUserGroup( $role, $userGroup );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\UnassignRoleFromUserGroupSignal( array(
                'role' => $role,
                'userGroup' => $userGroup,
            ) )
        );
        return $returnValue;
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
    public function assignRoleToUser( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\User $user, eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation = null )
    {
        $returnValue = $this->service->assignRoleToUser( $role, $user, $roleLimitation );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\AssignRoleToUserSignal( array(
                'role' => $role,
                'user' => $user,
                'roleLimitation' => $roleLimitation,
            ) )
        );
        return $returnValue;
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
    public function unassignRoleFromUser( eZ\Publish\API\Repository\Values\User\Role $role, eZ\Publish\API\Repository\Values\User\User $user )
    {
        $returnValue = $this->service->unassignRoleFromUser( $role, $user );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\UnassignRoleFromUserSignal( array(
                'role' => $role,
                'user' => $user,
            ) )
        );
        return $returnValue;
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
    public function getRoleAssignments( eZ\Publish\API\Repository\Values\User\Role $role )
    {
        $returnValue = $this->service->getRoleAssignments( $role );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\GetRoleAssignmentsSignal( array(
                'role' => $role,
            ) )
        );
        return $returnValue;
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
    public function getRoleAssignmentsForUser( eZ\Publish\API\Repository\Values\User\User $user )
    {
        $returnValue = $this->service->getRoleAssignmentsForUser( $user );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\GetRoleAssignmentsForUserSignal( array(
                'user' => $user,
            ) )
        );
        return $returnValue;
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
    public function getRoleAssignmentsForUserGroup( eZ\Publish\API\Repository\Values\User\UserGroup $userGroup )
    {
        $returnValue = $this->service->getRoleAssignmentsForUserGroup( $userGroup );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\GetRoleAssignmentsForUserGroupSignal( array(
                'userGroup' => $userGroup,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->newRoleCreateStruct( $name );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\NewRoleCreateStructSignal( array(
                'name' => $name,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->newPolicyCreateStruct( $module, $function );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\NewPolicyCreateStructSignal( array(
                'module' => $module,
                'function' => $function,
            ) )
        );
        return $returnValue;
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct
     */
    public function newPolicyUpdateStruct()
    {
        $returnValue = $this->service->newPolicyUpdateStruct();
        $this->signalDispatcher()->emit(
            new Signal\RoleService\NewPolicyUpdateStructSignal( array(
            ) )
        );
        return $returnValue;
    }

    /**
     * instantiates a policy update class
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    public function newRoleUpdateStruct()
    {
        $returnValue = $this->service->newRoleUpdateStruct();
        $this->signalDispatcher()->emit(
            new Signal\RoleService\NewRoleUpdateStructSignal( array(
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->getLimitationType( $identifier );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\GetLimitationTypeSignal( array(
                'identifier' => $identifier,
            ) )
        );
        return $returnValue;
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
        $returnValue = $this->service->getLimitationTypesByModuleFunction( $module, $function );
        $this->signalDispatcher()->emit(
            new Signal\RoleService\GetLimitationTypesByModuleFunctionSignal( array(
                'module' => $module,
                'function' => $function,
            ) )
        );
        return $returnValue;
    }

}

