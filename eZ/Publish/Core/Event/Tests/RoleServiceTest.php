<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

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
use eZ\Publish\Core\Event\RoleService;

class RoleServiceTest extends AbstractServiceTest
{
    public function testDeletePolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeletePolicyEventInterface::class,
            DeletePolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Policy::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deletePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeletePolicyEventInterface::class, 0],
            [DeletePolicyEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeletePolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeletePolicyEventInterface::class,
            DeletePolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Policy::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeletePolicyEventInterface::class, function (BeforeDeletePolicyEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deletePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeletePolicyEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeletePolicyEventInterface::class, 0],
            [DeletePolicyEventInterface::class, 0],
        ]);
    }

    public function testUpdateRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleEventInterface::class,
            UpdateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRole')->willReturn($updatedRole);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleEventInterface::class, 0],
            [UpdateRoleEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateRoleResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleEventInterface::class,
            UpdateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRole')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(BeforeUpdateRoleEventInterface::class, function (BeforeUpdateRoleEventInterface $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleEventInterface::class, 10],
            [BeforeUpdateRoleEventInterface::class, 0],
            [UpdateRoleEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleEventInterface::class,
            UpdateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRole')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(BeforeUpdateRoleEventInterface::class, function (BeforeUpdateRoleEventInterface $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateRoleEventInterface::class, 0],
            [UpdateRoleEventInterface::class, 0],
        ]);
    }

    public function testPublishRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishRoleDraftEventInterface::class,
            PublishRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->publishRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishRoleDraftEventInterface::class, 0],
            [PublishRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforePublishRoleDraftEventInterface::class,
            PublishRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforePublishRoleDraftEventInterface::class, function (BeforePublishRoleDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->publishRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforePublishRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforePublishRoleDraftEventInterface::class, 0],
            [PublishRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testAssignRoleToUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignRoleToUserEventInterface::class,
            AssignRoleToUserEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignRoleToUserEventInterface::class, 0],
            [AssignRoleToUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignRoleToUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignRoleToUserEventInterface::class,
            AssignRoleToUserEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignRoleToUserEventInterface::class, function (BeforeAssignRoleToUserEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignRoleToUserEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignRoleToUserEventInterface::class, 0],
            [BeforeAssignRoleToUserEventInterface::class, 0],
        ]);
    }

    public function testAddPolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyEventInterface::class,
            AddPolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicy')->willReturn($updatedRole);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyEventInterface::class, 0],
            [AddPolicyEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddPolicyResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyEventInterface::class,
            AddPolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicy')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(BeforeAddPolicyEventInterface::class, function (BeforeAddPolicyEventInterface $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyEventInterface::class, 10],
            [BeforeAddPolicyEventInterface::class, 0],
            [AddPolicyEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddPolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyEventInterface::class,
            AddPolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicy')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(BeforeAddPolicyEventInterface::class, function (BeforeAddPolicyEventInterface $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddPolicyEventInterface::class, 0],
            [BeforeAddPolicyEventInterface::class, 0],
        ]);
    }

    public function testUpdateRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleDraftEventInterface::class,
            UpdateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRoleDraft')->willReturn($updatedRoleDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleDraftEventInterface::class, 0],
            [UpdateRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleDraftEventInterface::class,
            UpdateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeUpdateRoleDraftEventInterface::class, function (BeforeUpdateRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleDraftEventInterface::class, 10],
            [BeforeUpdateRoleDraftEventInterface::class, 0],
            [UpdateRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateRoleDraftEventInterface::class,
            UpdateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeUpdateRoleDraftEventInterface::class, function (BeforeUpdateRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateRoleDraftEventInterface::class, 0],
            [UpdateRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testAssignRoleToUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignRoleToUserGroupEventInterface::class,
            AssignRoleToUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignRoleToUserGroupEventInterface::class, 0],
            [AssignRoleToUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignRoleToUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignRoleToUserGroupEventInterface::class,
            AssignRoleToUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignRoleToUserGroupEventInterface::class, function (BeforeAssignRoleToUserGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignRoleToUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignRoleToUserGroupEventInterface::class, 0],
            [BeforeAssignRoleToUserGroupEventInterface::class, 0],
        ]);
    }

    public function testUnassignRoleFromUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignRoleFromUserEventInterface::class,
            UnassignRoleFromUserEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignRoleFromUserEventInterface::class, 0],
            [UnassignRoleFromUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignRoleFromUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignRoleFromUserEventInterface::class,
            UnassignRoleFromUserEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnassignRoleFromUserEventInterface::class, function (BeforeUnassignRoleFromUserEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignRoleFromUserEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnassignRoleFromUserEventInterface::class, 0],
            [UnassignRoleFromUserEventInterface::class, 0],
        ]);
    }

    public function testUpdatePolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyByRoleDraftEventInterface::class,
            UpdatePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicyDraft = $this->createMock(PolicyDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicyByRoleDraft')->willReturn($updatedPolicyDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedPolicyDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyByRoleDraftEventInterface::class, 0],
            [UpdatePolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdatePolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyByRoleDraftEventInterface::class,
            UpdatePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicyDraft = $this->createMock(PolicyDraft::class);
        $eventUpdatedPolicyDraft = $this->createMock(PolicyDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicyByRoleDraft')->willReturn($updatedPolicyDraft);

        $traceableEventDispatcher->addListener(BeforeUpdatePolicyByRoleDraftEventInterface::class, function (BeforeUpdatePolicyByRoleDraftEventInterface $event) use ($eventUpdatedPolicyDraft) {
            $event->setUpdatedPolicyDraft($eventUpdatedPolicyDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedPolicyDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyByRoleDraftEventInterface::class, 10],
            [BeforeUpdatePolicyByRoleDraftEventInterface::class, 0],
            [UpdatePolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdatePolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyByRoleDraftEventInterface::class,
            UpdatePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicyDraft = $this->createMock(PolicyDraft::class);
        $eventUpdatedPolicyDraft = $this->createMock(PolicyDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicyByRoleDraft')->willReturn($updatedPolicyDraft);

        $traceableEventDispatcher->addListener(BeforeUpdatePolicyByRoleDraftEventInterface::class, function (BeforeUpdatePolicyByRoleDraftEventInterface $event) use ($eventUpdatedPolicyDraft) {
            $event->setUpdatedPolicyDraft($eventUpdatedPolicyDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedPolicyDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyByRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdatePolicyByRoleDraftEventInterface::class, 0],
            [UpdatePolicyByRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testCreateRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleEventInterface::class,
            CreateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleCreateStruct::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRole')->willReturn($roleDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($roleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleEventInterface::class, 0],
            [CreateRoleEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateRoleResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleEventInterface::class,
            CreateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleCreateStruct::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRole')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(BeforeCreateRoleEventInterface::class, function (BeforeCreateRoleEventInterface $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleEventInterface::class, 10],
            [BeforeCreateRoleEventInterface::class, 0],
            [CreateRoleEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleEventInterface::class,
            CreateRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleCreateStruct::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRole')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(BeforeCreateRoleEventInterface::class, function (BeforeCreateRoleEventInterface $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateRoleEventInterface::class, 0],
            [CreateRoleEventInterface::class, 0],
        ]);
    }

    public function testRemovePolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemovePolicyByRoleDraftEventInterface::class,
            RemovePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('removePolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemovePolicyByRoleDraftEventInterface::class, 0],
            [RemovePolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRemovePolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemovePolicyByRoleDraftEventInterface::class,
            RemovePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('removePolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeRemovePolicyByRoleDraftEventInterface::class, function (BeforeRemovePolicyByRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemovePolicyByRoleDraftEventInterface::class, 10],
            [BeforeRemovePolicyByRoleDraftEventInterface::class, 0],
            [RemovePolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemovePolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemovePolicyByRoleDraftEventInterface::class,
            RemovePolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('removePolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeRemovePolicyByRoleDraftEventInterface::class, function (BeforeRemovePolicyByRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeRemovePolicyByRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemovePolicyByRoleDraftEventInterface::class, 0],
            [RemovePolicyByRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testAddPolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyByRoleDraftEventInterface::class,
            AddPolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyByRoleDraftEventInterface::class, 0],
            [AddPolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddPolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyByRoleDraftEventInterface::class,
            AddPolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeAddPolicyByRoleDraftEventInterface::class, function (BeforeAddPolicyByRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyByRoleDraftEventInterface::class, 10],
            [BeforeAddPolicyByRoleDraftEventInterface::class, 0],
            [AddPolicyByRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddPolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAddPolicyByRoleDraftEventInterface::class,
            AddPolicyByRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(BeforeAddPolicyByRoleDraftEventInterface::class, function (BeforeAddPolicyByRoleDraftEventInterface $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeAddPolicyByRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AddPolicyByRoleDraftEventInterface::class, 0],
            [BeforeAddPolicyByRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testDeleteRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRoleEventInterface::class,
            DeleteRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRoleEventInterface::class, 0],
            [DeleteRoleEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRoleEventInterface::class,
            DeleteRoleEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteRoleEventInterface::class, function (BeforeDeleteRoleEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRoleEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteRoleEventInterface::class, 0],
            [DeleteRoleEventInterface::class, 0],
        ]);
    }

    public function testDeleteRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRoleDraftEventInterface::class,
            DeleteRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRoleDraftEventInterface::class, 0],
            [DeleteRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteRoleDraftEventInterface::class,
            DeleteRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeDeleteRoleDraftEventInterface::class, function (BeforeDeleteRoleDraftEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeDeleteRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteRoleDraftEventInterface::class, 0],
            [DeleteRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testRemoveRoleAssignmentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveRoleAssignmentEventInterface::class,
            RemoveRoleAssignmentEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleAssignment::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->removeRoleAssignment(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveRoleAssignmentEventInterface::class, 0],
            [RemoveRoleAssignmentEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveRoleAssignmentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeRemoveRoleAssignmentEventInterface::class,
            RemoveRoleAssignmentEventInterface::class
        );

        $parameters = [
            $this->createMock(RoleAssignment::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeRemoveRoleAssignmentEventInterface::class, function (BeforeRemoveRoleAssignmentEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->removeRoleAssignment(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeRemoveRoleAssignmentEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeRemoveRoleAssignmentEventInterface::class, 0],
            [RemoveRoleAssignmentEventInterface::class, 0],
        ]);
    }

    public function testCreateRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleDraftEventInterface::class,
            CreateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRoleDraft')->willReturn($roleDraft);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($roleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleDraftEventInterface::class, 0],
            [CreateRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleDraftEventInterface::class,
            CreateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRoleDraft')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(BeforeCreateRoleDraftEventInterface::class, function (BeforeCreateRoleDraftEventInterface $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleDraftEventInterface::class, 10],
            [BeforeCreateRoleDraftEventInterface::class, 0],
            [CreateRoleDraftEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateRoleDraftEventInterface::class,
            CreateRoleDraftEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRoleDraft')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(BeforeCreateRoleDraftEventInterface::class, function (BeforeCreateRoleDraftEventInterface $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateRoleDraftEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateRoleDraftEventInterface::class, 0],
            [CreateRoleDraftEventInterface::class, 0],
        ]);
    }

    public function testUpdatePolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyEventInterface::class,
            UpdatePolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicy = $this->createMock(Policy::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicy')->willReturn($updatedPolicy);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedPolicy, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyEventInterface::class, 0],
            [UpdatePolicyEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdatePolicyResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyEventInterface::class,
            UpdatePolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicy = $this->createMock(Policy::class);
        $eventUpdatedPolicy = $this->createMock(Policy::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicy')->willReturn($updatedPolicy);

        $traceableEventDispatcher->addListener(BeforeUpdatePolicyEventInterface::class, function (BeforeUpdatePolicyEventInterface $event) use ($eventUpdatedPolicy) {
            $event->setUpdatedPolicy($eventUpdatedPolicy);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedPolicy, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyEventInterface::class, 10],
            [BeforeUpdatePolicyEventInterface::class, 0],
            [UpdatePolicyEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdatePolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdatePolicyEventInterface::class,
            UpdatePolicyEventInterface::class
        );

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicy = $this->createMock(Policy::class);
        $eventUpdatedPolicy = $this->createMock(Policy::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicy')->willReturn($updatedPolicy);

        $traceableEventDispatcher->addListener(BeforeUpdatePolicyEventInterface::class, function (BeforeUpdatePolicyEventInterface $event) use ($eventUpdatedPolicy) {
            $event->setUpdatedPolicy($eventUpdatedPolicy);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedPolicy, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdatePolicyEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdatePolicyEventInterface::class, 0],
            [UpdatePolicyEventInterface::class, 0],
        ]);
    }

    public function testUnassignRoleFromUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignRoleFromUserGroupEventInterface::class,
            UnassignRoleFromUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignRoleFromUserGroupEventInterface::class, 0],
            [UnassignRoleFromUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignRoleFromUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnassignRoleFromUserGroupEventInterface::class,
            UnassignRoleFromUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnassignRoleFromUserGroupEventInterface::class, function (BeforeUnassignRoleFromUserGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnassignRoleFromUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnassignRoleFromUserGroupEventInterface::class, 0],
            [UnassignRoleFromUserGroupEventInterface::class, 0],
        ]);
    }
}
