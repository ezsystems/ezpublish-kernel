<?php

/**
 * RoleService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\PolicyCreateStruct;
use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\API\Repository\Values\User\RoleAssignment;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Decorator\RoleServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicyByRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AddPolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\AssignRoleToUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\CreateRoleSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\DeleteRoleSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\PublishRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicyByRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\RemovePolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\RemoveRoleAssignmentSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UnassignRoleFromUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdatePolicySignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\RoleService\UpdateRoleSignal;

/**
 * RoleService class.
 */
class RoleService extends RoleServiceDecorator
{
    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\RoleService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(RoleServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        parent::__construct($service);

        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates a new RoleDraft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *         if the name of the role already exists or if limitation of the same type
     *         is repeated in the policy create struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRole(RoleCreateStruct $roleCreateStruct)
    {
        $returnValue = $this->service->createRole($roleCreateStruct);
        $this->signalDispatcher->emit(
            new CreateRoleSignal(
                array(
                    'roleId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Creates a new RoleDraft for existing Role.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the Role already has a Role Draft that will need to be removed first
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a policy limitation in the $roleCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function createRoleDraft(Role $role)
    {
        $returnValue = $this->service->createRoleDraft($role);
        $this->signalDispatcher->emit(
            new CreateRoleDraftSignal(
                array(
                    'roleId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates the properties of a role draft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the identifier of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function updateRoleDraft(RoleDraft $roleDraft, RoleUpdateStruct $roleUpdateStruct)
    {
        $returnValue = $this->service->updateRoleDraft($roleDraft, $roleUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateRoleDraftSignal(
                array(
                    'roleId' => $roleDraft->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Adds a new policy to the role draft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to add  a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy create
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyCreateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyCreateStruct $policyCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\RoleDraft
     */
    public function addPolicyByRoleDraft(RoleDraft $roleDraft, PolicyCreateStruct $policyCreateStruct)
    {
        $returnValue = $this->service->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
        $this->signalDispatcher->emit(
            new AddPolicyByRoleDraftSignal(
                array(
                    'roleId' => $roleDraft->id,
                    'policyId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Removes a policy from a role draft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if policy does not belong to the given RoleDraft
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param PolicyDraft $policyDraft the policy to remove from the role
     * @return RoleDraft if the authenticated user is not allowed to remove a policy
     */
    public function removePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policyDraft)
    {
        $returnValue = $this->service->removePolicyByRoleDraft($roleDraft, $policyDraft);
        $this->signalDispatcher->emit(
            new RemovePolicyByRoleDraftSignal(
                array(
                    'roleId' => $roleDraft->id,
                    'policyId' => $policyDraft->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a policy
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if limitation of the same type is repeated in policy update
     *                                                                        struct or if limitation is not allowed on module/function
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if a limitation in the $policyUpdateStruct is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     * @param \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy
     * @param \eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct $policyUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyDraft
     */
    public function updatePolicyByRoleDraft(RoleDraft $roleDraft, PolicyDraft $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $returnValue = $this->service->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdatePolicySignal(
                array(
                    'policyId' => $policy->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Deletes the given role draft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function deleteRoleDraft(RoleDraft $roleDraft)
    {
        $returnValue = $this->service->deleteRoleDraft($roleDraft);
        $this->signalDispatcher->emit(
            new DeleteRoleDraftSignal(
                array(
                    'roleId' => $roleDraft->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Publishes a given Role draft.
     *
     * @since 6.0
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to publish this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleDraft $roleDraft
     */
    public function publishRoleDraft(RoleDraft $roleDraft)
    {
        $returnValue = $this->service->publishRoleDraft($roleDraft);
        $this->signalDispatcher->emit(
            new PublishRoleDraftSignal(
                array(
                    'roleId' => $roleDraft->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates the name of the role.
     *
     * @deprecated since 6.0, use {@see updateRoleDraft}
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the name of the role already exists
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct $roleUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole(Role $role, RoleUpdateStruct $roleUpdateStruct)
    {
        $returnValue = $this->service->updateRole($role, $roleUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateRoleSignal(
                array(
                    'roleId' => $role->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Adds a new policy to the role.
     *
     * @deprecated since 6.0, use {@see addPolicyByRoleDraft}
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
    public function addPolicy(Role $role, PolicyCreateStruct $policyCreateStruct)
    {
        $returnValue = $this->service->addPolicy($role, $policyCreateStruct);
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

    /**
     * Delete a policy.
     *
     * @deprecated since 6.0, use {@link removePolicyByRoleDraft()} instead.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a policy
     *
     * @param \eZ\Publish\API\Repository\Values\User\Policy $policy the policy to delete
     */
    public function deletePolicy(Policy $policy)
    {
        $returnValue = $this->service->deletePolicy($policy);
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

    /**
     * Updates the limitations of a policy. The module and function cannot be changed and
     * the limitations are replaced by the ones in $roleUpdateStruct.
     *
     * @deprecated since 6.0, use {@link updatePolicyByRoleDraft()} instead.
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
    public function updatePolicy(Policy $policy, PolicyUpdateStruct $policyUpdateStruct)
    {
        $returnValue = $this->service->updatePolicy($policy, $policyUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdatePolicySignal(
                array(
                    'policyId' => $policy->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Deletes the given role.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete this role
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function deleteRole(Role $role)
    {
        $returnValue = $this->service->deleteRole($role);
        $this->signalDispatcher->emit(
            new DeleteRoleSignal(
                array(
                    'roleId' => $role->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Assigns a role to the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUserGroup(Role $role, UserGroup $userGroup, RoleLimitation $roleLimitation = null)
    {
        $returnValue = $this->service->assignRoleToUserGroup($role, $userGroup, $roleLimitation);
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

    /**
     * removes a role from the given user group.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException  If the role is not assigned to the given user group
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     */
    public function unassignRoleFromUserGroup(Role $role, UserGroup $userGroup)
    {
        $returnValue = $this->service->unassignRoleFromUserGroup($role, $userGroup);
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

    /**
     * Assigns a role to the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign a role
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException if $roleLimitation is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation $roleLimitation an optional role limitation (which is either a subtree limitation or section limitation)
     */
    public function assignRoleToUser(Role $role, User $user, RoleLimitation $roleLimitation = null)
    {
        $returnValue = $this->service->assignRoleToUser($role, $user, $roleLimitation);
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

    /**
     * removes a role from the given user.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the role is not assigned to the user
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function unassignRoleFromUser(Role $role, User $user)
    {
        $returnValue = $this->service->unassignRoleFromUser($role, $user);
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

    /**
     * Removes the given role assignment.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove a role assignment
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleAssignment $roleAssignment
     */
    public function removeRoleAssignment(RoleAssignment $roleAssignment)
    {
        $returnValue = $this->service->removeRoleAssignment($roleAssignment);
        $this->signalDispatcher->emit(
            new RemoveRoleAssignmentSignal([
                'roleAssignmentId' => $roleAssignment->id,
            ])
        );

        return $returnValue;
    }
}
