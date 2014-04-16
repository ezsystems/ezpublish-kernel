<?php
/**
 * RoleService class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdatePolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal;

/**
 * RoleService class
 * @package eZ\Publish\Core\SignalSlot
 */
class RoleService implements RoleServiceInterface
{
    /**
     * Aggregated service
     *
     * @var RoleServiceInterface
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param RoleServiceInterface $service
     * @param SignalDispatcher $signalDispatcher
     */
    public function __construct( RoleServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    public function createRole( RoleCreateStruct $roleCreateStruct )
    {
        $returnValue = $this->service->createRole( $roleCreateStruct );
        $this->signalDispatcher->emit(
            new CreateRoleSignal(
                array(
                    'roleId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
    }

    public function updateRole( Role $role, RoleUpdateStruct $roleUpdateStruct )
    {
        $returnValue = $this->service->updateRole( $role, $roleUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdateRoleSignal(
                array(
                    'roleId' => $role->id,
                )
            )
        );
        return $returnValue;
    }

    public function addPolicy( Role $role, PolicyCreateStruct $policyCreateStruct )
    {
        $returnValue = $this->service->addPolicy( $role, $policyCreateStruct );
        $this->signalDispatcher->emit(
            new AddPolicySignal(
                array(
                    'roleId' => $role->id,
                    'policyId' => $returnValue->id,
                )
            )
        );
        return $returnValue;
    }

    public function removePolicy( Role $role, Policy $policy )
    {
        $returnValue = $this->service->removePolicy( $role, $policy );
        $this->signalDispatcher->emit(
            new RemovePolicySignal(
                array(
                    'roleId' => $role->id,
                    'policyId' => $policy->id,
                )
            )
        );
        return $returnValue;
    }

    public function deletePolicy( Policy $policy )
    {
        $returnValue = $this->service->deletePolicy( $policy );
        $this->signalDispatcher->emit(
            new RemovePolicySignal(
                array(
                    'roleId' => $policy->roleId,
                    'policyId' => $policy->id,
                )
            )
        );
        return $returnValue;
    }

    public function updatePolicy( Policy $policy, PolicyUpdateStruct $policyUpdateStruct )
    {
        $returnValue = $this->service->updatePolicy( $policy, $policyUpdateStruct );
        $this->signalDispatcher->emit(
            new UpdatePolicySignal(
                array(
                    'policyId' => $policy->id,
                )
            )
        );
        return $returnValue;
    }

    public function loadRole( $id )
    {
        return $this->service->loadRole( $id );
    }

    public function loadRoleByIdentifier( $identifier )
    {
        return $this->service->loadRoleByIdentifier( $identifier );
    }

    public function loadRoles()
    {
        return $this->service->loadRoles();
    }

    public function deleteRole( Role $role )
    {
        $returnValue = $this->service->deleteRole( $role );
        $this->signalDispatcher->emit(
            new DeleteRoleSignal(
                array(
                    'roleId' => $role->id,
                )
            )
        );
        return $returnValue;
    }

    public function loadPoliciesByUserId( $userId )
    {
        return $this->service->loadPoliciesByUserId( $userId );
    }

    public function assignRoleToUserGroup( Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null )
    {
        $returnValue = $this->service->assignRoleToUserGroup( $role, $userGroup, $roleLimitation );
        $this->signalDispatcher->emit(
            new AssignRoleToUserGroupSignal(
                array(
                    'roleId' => $role->id,
                    'userGroupId' => $userGroup->id,
                    'roleLimitation' => $roleLimitation,
                )
            )
        );
        return $returnValue;
    }

    public function unassignRoleFromUserGroup( Role $role, UserGroup $userGroup )
    {
        $returnValue = $this->service->unassignRoleFromUserGroup( $role, $userGroup );
        $this->signalDispatcher->emit(
            new UnassignRoleFromUserGroupSignal(
                array(
                    'roleId' => $role->id,
                    'userGroupId' => $userGroup->id,
                )
            )
        );
        return $returnValue;
    }

    public function assignRoleToUser( Role $role, User $user, RoleLimitation $roleLimitation = null )
    {
        $returnValue = $this->service->assignRoleToUser( $role, $user, $roleLimitation );
        $this->signalDispatcher->emit(
            new AssignRoleToUserSignal(
                array(
                    'roleId' => $role->id,
                    'userId' => $user->id,
                    'roleLimitation' => $roleLimitation,
                )
            )
        );
        return $returnValue;
    }

    public function unassignRoleFromUser( Role $role, User $user )
    {
        $returnValue = $this->service->unassignRoleFromUser( $role, $user );
        $this->signalDispatcher->emit(
            new UnassignRoleFromUserSignal(
                array(
                    'roleId' => $role->id,
                    'userId' => $user->id,
                )
            )
        );
        return $returnValue;
    }

    public function getRoleAssignments( Role $role )
    {
        return $this->service->getRoleAssignments( $role );
    }

    public function getRoleAssignmentsForUser( User $user )
    {
        return $this->service->getRoleAssignmentsForUser( $user );
    }

    public function getRoleAssignmentsForUserGroup( UserGroup $userGroup )
    {
        return $this->service->getRoleAssignmentsForUserGroup( $userGroup );
    }

    public function newRoleCreateStruct( $name )
    {
        return $this->service->newRoleCreateStruct( $name );
    }

    public function newPolicyCreateStruct( $module, $function )
    {
        return $this->service->newPolicyCreateStruct( $module, $function );
    }

    public function newPolicyUpdateStruct()
    {
        return $this->service->newPolicyUpdateStruct();
    }

    public function newRoleUpdateStruct()
    {
        return $this->service->newRoleUpdateStruct();
    }

    public function getLimitationType( $identifier )
    {
        return $this->service->getLimitationType( $identifier );
    }

    public function getLimitationTypesByModuleFunction( $module, $function )
    {
        return $this->service->getLimitationTypesByModuleFunction( $module, $function );
    }
}
