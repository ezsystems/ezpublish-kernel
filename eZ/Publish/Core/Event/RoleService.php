<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Events\Role\AddPolicyByRoleDraftEvent as AddPolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\AddPolicyEvent as AddPolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserEvent as AssignRoleToUserEventInterface;
use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserGroupEvent as AssignRoleToUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeAddPolicyByRoleDraftEvent as BeforeAddPolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeAddPolicyEvent as BeforeAddPolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeAssignRoleToUserEvent as BeforeAssignRoleToUserEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeAssignRoleToUserGroupEvent as BeforeAssignRoleToUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeCreateRoleDraftEvent as BeforeCreateRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeCreateRoleEvent as BeforeCreateRoleEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeDeletePolicyEvent as BeforeDeletePolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleDraftEvent as BeforeDeleteRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleEvent as BeforeDeleteRoleEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforePublishRoleDraftEvent as BeforePublishRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeRemovePolicyByRoleDraftEvent as BeforeRemovePolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeRemoveRoleAssignmentEvent as BeforeRemoveRoleAssignmentEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUnassignRoleFromUserEvent as BeforeUnassignRoleFromUserEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUnassignRoleFromUserGroupEvent as BeforeUnassignRoleFromUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdatePolicyByRoleDraftEvent as BeforeUpdatePolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdatePolicyEvent as BeforeUpdatePolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdateRoleDraftEvent as BeforeUpdateRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdateRoleEvent as BeforeUpdateRoleEventInterface;
use eZ\Publish\API\Repository\Events\Role\CreateRoleDraftEvent as CreateRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\CreateRoleEvent as CreateRoleEventInterface;
use eZ\Publish\API\Repository\Events\Role\DeletePolicyEvent as DeletePolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\DeleteRoleDraftEvent as DeleteRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\DeleteRoleEvent as DeleteRoleEventInterface;
use eZ\Publish\API\Repository\Events\Role\PublishRoleDraftEvent as PublishRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\RemovePolicyByRoleDraftEvent as RemovePolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\RemoveRoleAssignmentEvent as RemoveRoleAssignmentEventInterface;
use eZ\Publish\API\Repository\Events\Role\UnassignRoleFromUserEvent as UnassignRoleFromUserEventInterface;
use eZ\Publish\API\Repository\Events\Role\UnassignRoleFromUserGroupEvent as UnassignRoleFromUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\Role\UpdatePolicyByRoleDraftEvent as UpdatePolicyByRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\UpdatePolicyEvent as UpdatePolicyEventInterface;
use eZ\Publish\API\Repository\Events\Role\UpdateRoleDraftEvent as UpdateRoleDraftEventInterface;
use eZ\Publish\API\Repository\Events\Role\UpdateRoleEvent as UpdateRoleEventInterface;
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
use eZ\Publish\Core\Event\Role\AddPolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\AddPolicyEvent;
use eZ\Publish\Core\Event\Role\AssignRoleToUserEvent;
use eZ\Publish\Core\Event\Role\AssignRoleToUserGroupEvent;
use eZ\Publish\Core\Event\Role\BeforeAddPolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeAddPolicyEvent;
use eZ\Publish\Core\Event\Role\BeforeAssignRoleToUserEvent;
use eZ\Publish\Core\Event\Role\BeforeAssignRoleToUserGroupEvent;
use eZ\Publish\Core\Event\Role\BeforeCreateRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeCreateRoleEvent;
use eZ\Publish\Core\Event\Role\BeforeDeletePolicyEvent;
use eZ\Publish\Core\Event\Role\BeforeDeleteRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeDeleteRoleEvent;
use eZ\Publish\Core\Event\Role\BeforePublishRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeRemovePolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeRemoveRoleAssignmentEvent;
use eZ\Publish\Core\Event\Role\BeforeUnassignRoleFromUserEvent;
use eZ\Publish\Core\Event\Role\BeforeUnassignRoleFromUserGroupEvent;
use eZ\Publish\Core\Event\Role\BeforeUpdatePolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeUpdatePolicyEvent;
use eZ\Publish\Core\Event\Role\BeforeUpdateRoleDraftEvent;
use eZ\Publish\Core\Event\Role\BeforeUpdateRoleEvent;
use eZ\Publish\Core\Event\Role\CreateRoleDraftEvent;
use eZ\Publish\Core\Event\Role\CreateRoleEvent;
use eZ\Publish\Core\Event\Role\DeletePolicyEvent;
use eZ\Publish\Core\Event\Role\DeleteRoleDraftEvent;
use eZ\Publish\Core\Event\Role\DeleteRoleEvent;
use eZ\Publish\Core\Event\Role\PublishRoleDraftEvent;
use eZ\Publish\Core\Event\Role\RemovePolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\RemoveRoleAssignmentEvent;
use eZ\Publish\Core\Event\Role\UnassignRoleFromUserEvent;
use eZ\Publish\Core\Event\Role\UnassignRoleFromUserGroupEvent;
use eZ\Publish\Core\Event\Role\UpdatePolicyByRoleDraftEvent;
use eZ\Publish\Core\Event\Role\UpdatePolicyEvent;
use eZ\Publish\Core\Event\Role\UpdateRoleDraftEvent;
use eZ\Publish\Core\Event\Role\UpdateRoleEvent;
use eZ\Publish\SPI\Repository\Decorator\RoleServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RoleService extends RoleServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        RoleServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createRole(RoleCreateStruct $roleCreateStruct): RoleDraft
    {
        $eventData = [$roleCreateStruct];

        $beforeEvent = new BeforeCreateRoleEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateRoleEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRoleDraft();
        }

        $roleDraft = $beforeEvent->hasRoleDraft()
            ? $beforeEvent->getRoleDraft()
            : $this->innerService->createRole($roleCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateRoleEvent($roleDraft, ...$eventData),
            CreateRoleEventInterface::class
        );

        return $roleDraft;
    }

    public function createRoleDraft(Role $role)
    {
        $eventData = [$role];

        $beforeEvent = new BeforeCreateRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRoleDraft();
        }

        $roleDraft = $beforeEvent->hasRoleDraft()
            ? $beforeEvent->getRoleDraft()
            : $this->innerService->createRoleDraft($role);

        $this->eventDispatcher->dispatch(
            new CreateRoleDraftEvent($roleDraft, ...$eventData),
            CreateRoleDraftEventInterface::class
        );

        return $roleDraft;
    }

    public function updateRoleDraft(
        RoleDraft $roleDraft,
        RoleUpdateStruct $roleUpdateStruct
    ) {
        $eventData = [
            $roleDraft,
            $roleUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->updateRoleDraft($roleDraft, $roleUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateRoleDraftEvent($updatedRoleDraft, ...$eventData),
            UpdateRoleDraftEventInterface::class
        );

        return $updatedRoleDraft;
    }

    public function addPolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyCreateStruct $policyCreateStruct
    ) {
        $eventData = [
            $roleDraft,
            $policyCreateStruct,
        ];

        $beforeEvent = new BeforeAddPolicyByRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAddPolicyByRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);

        $this->eventDispatcher->dispatch(
            new AddPolicyByRoleDraftEvent($updatedRoleDraft, ...$eventData),
            AddPolicyByRoleDraftEventInterface::class
        );

        return $updatedRoleDraft;
    }

    public function removePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policyDraft
    ) {
        $eventData = [
            $roleDraft,
            $policyDraft,
        ];

        $beforeEvent = new BeforeRemovePolicyByRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemovePolicyByRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->removePolicyByRoleDraft($roleDraft, $policyDraft);

        $this->eventDispatcher->dispatch(
            new RemovePolicyByRoleDraftEvent($updatedRoleDraft, ...$eventData),
            RemovePolicyByRoleDraftEventInterface::class
        );

        return $updatedRoleDraft;
    }

    public function updatePolicyByRoleDraft(
        RoleDraft $roleDraft,
        PolicyDraft $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ) {
        $eventData = [
            $roleDraft,
            $policy,
            $policyUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdatePolicyByRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdatePolicyByRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedPolicyDraft();
        }

        $updatedPolicyDraft = $beforeEvent->hasUpdatedPolicyDraft()
            ? $beforeEvent->getUpdatedPolicyDraft()
            : $this->innerService->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdatePolicyByRoleDraftEvent($updatedPolicyDraft, ...$eventData),
            UpdatePolicyByRoleDraftEventInterface::class
        );

        return $updatedPolicyDraft;
    }

    public function deleteRoleDraft(RoleDraft $roleDraft): void
    {
        $eventData = [$roleDraft];

        $beforeEvent = new BeforeDeleteRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteRoleDraft($roleDraft);

        $this->eventDispatcher->dispatch(
            new DeleteRoleDraftEvent(...$eventData),
            DeleteRoleDraftEventInterface::class
        );
    }

    public function publishRoleDraft(RoleDraft $roleDraft): void
    {
        $eventData = [$roleDraft];

        $beforeEvent = new BeforePublishRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforePublishRoleDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->publishRoleDraft($roleDraft);

        $this->eventDispatcher->dispatch(
            new PublishRoleDraftEvent(...$eventData),
            PublishRoleDraftEventInterface::class
        );
    }

    public function updateRole(
        Role $role,
        RoleUpdateStruct $roleUpdateStruct
    ) {
        $eventData = [
            $role,
            $roleUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateRoleEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateRoleEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRole();
        }

        $updatedRole = $beforeEvent->hasUpdatedRole()
            ? $beforeEvent->getUpdatedRole()
            : $this->innerService->updateRole($role, $roleUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateRoleEvent($updatedRole, ...$eventData),
            UpdateRoleEventInterface::class
        );

        return $updatedRole;
    }

    public function addPolicy(
        Role $role,
        PolicyCreateStruct $policyCreateStruct
    ) {
        $eventData = [
            $role,
            $policyCreateStruct,
        ];

        $beforeEvent = new BeforeAddPolicyEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAddPolicyEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRole();
        }

        $updatedRole = $beforeEvent->hasUpdatedRole()
            ? $beforeEvent->getUpdatedRole()
            : $this->innerService->addPolicy($role, $policyCreateStruct);

        $this->eventDispatcher->dispatch(
            new AddPolicyEvent($updatedRole, ...$eventData),
            AddPolicyEventInterface::class
        );

        return $updatedRole;
    }

    public function deletePolicy(Policy $policy): void
    {
        $eventData = [$policy];

        $beforeEvent = new BeforeDeletePolicyEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeletePolicyEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deletePolicy($policy);

        $this->eventDispatcher->dispatch(
            new DeletePolicyEvent(...$eventData),
            DeletePolicyEventInterface::class
        );
    }

    public function updatePolicy(
        Policy $policy,
        PolicyUpdateStruct $policyUpdateStruct
    ) {
        $eventData = [
            $policy,
            $policyUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdatePolicyEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdatePolicyEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedPolicy();
        }

        $updatedPolicy = $beforeEvent->hasUpdatedPolicy()
            ? $beforeEvent->getUpdatedPolicy()
            : $this->innerService->updatePolicy($policy, $policyUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdatePolicyEvent($updatedPolicy, ...$eventData),
            UpdatePolicyEventInterface::class
        );

        return $updatedPolicy;
    }

    public function deleteRole(Role $role): void
    {
        $eventData = [$role];

        $beforeEvent = new BeforeDeleteRoleEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteRoleEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteRole($role);

        $this->eventDispatcher->dispatch(
            new DeleteRoleEvent(...$eventData),
            DeleteRoleEventInterface::class
        );
    }

    public function assignRoleToUserGroup(
        Role $role,
        UserGroup $userGroup,
        RoleLimitation $roleLimitation = null
    ): void {
        $eventData = [
            $role,
            $userGroup,
            $roleLimitation,
        ];

        $beforeEvent = new BeforeAssignRoleToUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAssignRoleToUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignRoleToUserGroup($role, $userGroup, $roleLimitation);

        $this->eventDispatcher->dispatch(
            new AssignRoleToUserGroupEvent(...$eventData),
            AssignRoleToUserGroupEventInterface::class
        );
    }

    public function unassignRoleFromUserGroup(
        Role $role,
        UserGroup $userGroup
    ): void {
        $eventData = [
            $role,
            $userGroup,
        ];

        $beforeEvent = new BeforeUnassignRoleFromUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUnassignRoleFromUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unassignRoleFromUserGroup($role, $userGroup);

        $this->eventDispatcher->dispatch(
            new UnassignRoleFromUserGroupEvent(...$eventData),
            UnassignRoleFromUserGroupEventInterface::class
        );
    }

    public function assignRoleToUser(
        Role $role,
        User $user,
        RoleLimitation $roleLimitation = null
    ): void {
        $eventData = [
            $role,
            $user,
            $roleLimitation,
        ];

        $beforeEvent = new BeforeAssignRoleToUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAssignRoleToUserEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignRoleToUser($role, $user, $roleLimitation);

        $this->eventDispatcher->dispatch(
            new AssignRoleToUserEvent(...$eventData),
            AssignRoleToUserEventInterface::class
        );
    }

    public function unassignRoleFromUser(
        Role $role,
        User $user
    ): void {
        $eventData = [
            $role,
            $user,
        ];

        $beforeEvent = new BeforeUnassignRoleFromUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUnassignRoleFromUserEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unassignRoleFromUser($role, $user);

        $this->eventDispatcher->dispatch(
            new UnassignRoleFromUserEvent(...$eventData),
            UnassignRoleFromUserEventInterface::class
        );
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment): void
    {
        $eventData = [$roleAssignment];

        $beforeEvent = new BeforeRemoveRoleAssignmentEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemoveRoleAssignmentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->removeRoleAssignment($roleAssignment);

        $this->eventDispatcher->dispatch(
            new RemoveRoleAssignmentEvent(...$eventData),
            RemoveRoleAssignmentEventInterface::class
        );
    }
}
