<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\Values\User\Limitation\RoleLimitation;
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
use eZ\Publish\API\Repository\Events\Role\AddPolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserEvent;
use eZ\Publish\API\Repository\Events\Role\AssignRoleToUserGroupEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeAddPolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeAssignRoleToUserEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeAssignRoleToUserGroupEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeCreateRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeCreateRoleEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeDeleteRoleEvent;
use eZ\Publish\API\Repository\Events\Role\BeforePublishRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeRemovePolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeRemoveRoleAssignmentEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeUnassignRoleFromUserGroupEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdatePolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\BeforeUpdateRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\CreateRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\CreateRoleEvent;
use eZ\Publish\API\Repository\Events\Role\DeleteRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\DeleteRoleEvent;
use eZ\Publish\API\Repository\Events\Role\PublishRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\RemovePolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\RemoveRoleAssignmentEvent;
use eZ\Publish\API\Repository\Events\Role\UnassignRoleFromUserGroupEvent;
use eZ\Publish\API\Repository\Events\Role\UpdatePolicyByRoleDraftEvent;
use eZ\Publish\API\Repository\Events\Role\UpdateRoleDraftEvent;
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRoleDraft();
        }

        $roleDraft = $beforeEvent->hasRoleDraft()
            ? $beforeEvent->getRoleDraft()
            : $this->innerService->createRole($roleCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateRoleEvent($roleDraft, ...$eventData)
        );

        return $roleDraft;
    }

    public function createRoleDraft(Role $role)
    {
        $eventData = [$role];

        $beforeEvent = new BeforeCreateRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRoleDraft();
        }

        $roleDraft = $beforeEvent->hasRoleDraft()
            ? $beforeEvent->getRoleDraft()
            : $this->innerService->createRoleDraft($role);

        $this->eventDispatcher->dispatch(
            new CreateRoleDraftEvent($roleDraft, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->updateRoleDraft($roleDraft, $roleUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateRoleDraftEvent($updatedRoleDraft, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);

        $this->eventDispatcher->dispatch(
            new AddPolicyByRoleDraftEvent($updatedRoleDraft, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedRoleDraft();
        }

        $updatedRoleDraft = $beforeEvent->hasUpdatedRoleDraft()
            ? $beforeEvent->getUpdatedRoleDraft()
            : $this->innerService->removePolicyByRoleDraft($roleDraft, $policyDraft);

        $this->eventDispatcher->dispatch(
            new RemovePolicyByRoleDraftEvent($updatedRoleDraft, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedPolicyDraft();
        }

        $updatedPolicyDraft = $beforeEvent->hasUpdatedPolicyDraft()
            ? $beforeEvent->getUpdatedPolicyDraft()
            : $this->innerService->updatePolicyByRoleDraft($roleDraft, $policy, $policyUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdatePolicyByRoleDraftEvent($updatedPolicyDraft, ...$eventData)
        );

        return $updatedPolicyDraft;
    }

    public function deleteRoleDraft(RoleDraft $roleDraft): void
    {
        $eventData = [$roleDraft];

        $beforeEvent = new BeforeDeleteRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteRoleDraft($roleDraft);

        $this->eventDispatcher->dispatch(
            new DeleteRoleDraftEvent(...$eventData)
        );
    }

    public function publishRoleDraft(RoleDraft $roleDraft): void
    {
        $eventData = [$roleDraft];

        $beforeEvent = new BeforePublishRoleDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->publishRoleDraft($roleDraft);

        $this->eventDispatcher->dispatch(
            new PublishRoleDraftEvent(...$eventData)
        );
    }

    public function deleteRole(Role $role): void
    {
        $eventData = [$role];

        $beforeEvent = new BeforeDeleteRoleEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteRole($role);

        $this->eventDispatcher->dispatch(
            new DeleteRoleEvent(...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignRoleToUserGroup($role, $userGroup, $roleLimitation);

        $this->eventDispatcher->dispatch(
            new AssignRoleToUserGroupEvent(...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unassignRoleFromUserGroup($role, $userGroup);

        $this->eventDispatcher->dispatch(
            new UnassignRoleFromUserGroupEvent(...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignRoleToUser($role, $user, $roleLimitation);

        $this->eventDispatcher->dispatch(
            new AssignRoleToUserEvent(...$eventData)
        );
    }

    public function removeRoleAssignment(RoleAssignment $roleAssignment): void
    {
        $eventData = [$roleAssignment];

        $beforeEvent = new BeforeRemoveRoleAssignmentEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->removeRoleAssignment($roleAssignment);

        $this->eventDispatcher->dispatch(
            new RemoveRoleAssignmentEvent(...$eventData)
        );
    }
}
