<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

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
use eZ\Publish\Core\Event\Role\RoleEvents;

class RoleServiceTest extends AbstractServiceTest
{
    public function testDeletePolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_POLICY,
            RoleEvents::DELETE_POLICY
        );

        $parameters = [
            $this->createMock(Policy::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deletePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_POLICY, 0],
            [RoleEvents::DELETE_POLICY, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeletePolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_POLICY,
            RoleEvents::DELETE_POLICY
        );

        $parameters = [
            $this->createMock(Policy::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_DELETE_POLICY, function (BeforeDeletePolicyEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deletePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_POLICY, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::DELETE_POLICY, 0],
            [RoleEvents::BEFORE_DELETE_POLICY, 0],
        ]);
    }

    public function testUpdateRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE,
            RoleEvents::UPDATE_ROLE
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
            [RoleEvents::BEFORE_UPDATE_ROLE, 0],
            [RoleEvents::UPDATE_ROLE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateRoleResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE,
            RoleEvents::UPDATE_ROLE
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRole')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_ROLE, function (BeforeUpdateRoleEvent $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_ROLE, 10],
            [RoleEvents::BEFORE_UPDATE_ROLE, 0],
            [RoleEvents::UPDATE_ROLE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE,
            RoleEvents::UPDATE_ROLE
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRole')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_ROLE, function (BeforeUpdateRoleEvent $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_ROLE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UPDATE_ROLE, 0],
            [RoleEvents::BEFORE_UPDATE_ROLE, 0],
        ]);
    }

    public function testPublishRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT,
            RoleEvents::PUBLISH_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->publishRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT, 0],
            [RoleEvents::PUBLISH_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testPublishRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT,
            RoleEvents::PUBLISH_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT, function (BeforePublishRoleDraftEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->publishRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::PUBLISH_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_PUBLISH_ROLE_DRAFT, 0],
        ]);
    }

    public function testAssignRoleToUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER,
            RoleEvents::ASSIGN_ROLE_TO_USER
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
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER, 0],
            [RoleEvents::ASSIGN_ROLE_TO_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignRoleToUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER,
            RoleEvents::ASSIGN_ROLE_TO_USER
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER, function (BeforeAssignRoleToUserEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::ASSIGN_ROLE_TO_USER, 0],
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER, 0],
        ]);
    }

    public function testAddPolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY,
            RoleEvents::ADD_POLICY
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
            [RoleEvents::BEFORE_ADD_POLICY, 0],
            [RoleEvents::ADD_POLICY, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddPolicyResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY,
            RoleEvents::ADD_POLICY
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicy')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ADD_POLICY, function (BeforeAddPolicyEvent $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ADD_POLICY, 10],
            [RoleEvents::BEFORE_ADD_POLICY, 0],
            [RoleEvents::ADD_POLICY, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddPolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY,
            RoleEvents::ADD_POLICY
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRole = $this->createMock(Role::class);
        $eventUpdatedRole = $this->createMock(Role::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicy')->willReturn($updatedRole);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ADD_POLICY, function (BeforeAddPolicyEvent $event) use ($eventUpdatedRole) {
            $event->setUpdatedRole($eventUpdatedRole);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRole, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ADD_POLICY, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::ADD_POLICY, 0],
            [RoleEvents::BEFORE_ADD_POLICY, 0],
        ]);
    }

    public function testUpdateRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE_DRAFT,
            RoleEvents::UPDATE_ROLE_DRAFT
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
            [RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, 0],
            [RoleEvents::UPDATE_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE_DRAFT,
            RoleEvents::UPDATE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, function (BeforeUpdateRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, 10],
            [RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, 0],
            [RoleEvents::UPDATE_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_ROLE_DRAFT,
            RoleEvents::UPDATE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(RoleUpdateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updateRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, function (BeforeUpdateRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UPDATE_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_UPDATE_ROLE_DRAFT, 0],
        ]);
    }

    public function testAssignRoleToUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP,
            RoleEvents::ASSIGN_ROLE_TO_USER_GROUP
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
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP, 0],
            [RoleEvents::ASSIGN_ROLE_TO_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignRoleToUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP,
            RoleEvents::ASSIGN_ROLE_TO_USER_GROUP
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
            $this->createMock(RoleLimitation::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP, function (BeforeAssignRoleToUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->assignRoleToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::ASSIGN_ROLE_TO_USER_GROUP, 0],
            [RoleEvents::BEFORE_ASSIGN_ROLE_TO_USER_GROUP, 0],
        ]);
    }

    public function testUnassignRoleFromUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER,
            RoleEvents::UNASSIGN_ROLE_FROM_USER
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
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER, 0],
            [RoleEvents::UNASSIGN_ROLE_FROM_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignRoleFromUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER,
            RoleEvents::UNASSIGN_ROLE_FROM_USER
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(User::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER, function (BeforeUnassignRoleFromUserEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UNASSIGN_ROLE_FROM_USER, 0],
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER, 0],
        ]);
    }

    public function testUpdatePolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT
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
            [RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdatePolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT
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

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, function (BeforeUpdatePolicyByRoleDraftEvent $event) use ($eventUpdatedPolicyDraft) {
            $event->setUpdatedPolicyDraft($eventUpdatedPolicyDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedPolicyDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, 10],
            [RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdatePolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT
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

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, function (BeforeUpdatePolicyByRoleDraftEvent $event) use ($eventUpdatedPolicyDraft) {
            $event->setUpdatedPolicyDraft($eventUpdatedPolicyDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedPolicyDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UPDATE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_UPDATE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
    }

    public function testCreateRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE,
            RoleEvents::CREATE_ROLE
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
            [RoleEvents::BEFORE_CREATE_ROLE, 0],
            [RoleEvents::CREATE_ROLE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateRoleResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE,
            RoleEvents::CREATE_ROLE
        );

        $parameters = [
            $this->createMock(RoleCreateStruct::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRole')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_CREATE_ROLE, function (BeforeCreateRoleEvent $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_CREATE_ROLE, 10],
            [RoleEvents::BEFORE_CREATE_ROLE, 0],
            [RoleEvents::CREATE_ROLE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE,
            RoleEvents::CREATE_ROLE
        );

        $parameters = [
            $this->createMock(RoleCreateStruct::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRole')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_CREATE_ROLE, function (BeforeCreateRoleEvent $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_CREATE_ROLE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::CREATE_ROLE, 0],
            [RoleEvents::BEFORE_CREATE_ROLE, 0],
        ]);
    }

    public function testRemovePolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT
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
            [RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnRemovePolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('removePolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, function (BeforeRemovePolicyByRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, 10],
            [RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemovePolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT,
            RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyDraft::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('removePolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, function (BeforeRemovePolicyByRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->removePolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::REMOVE_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_REMOVE_POLICY_BY_ROLE_DRAFT, 0],
        ]);
    }

    public function testAddPolicyByRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT,
            RoleEvents::ADD_POLICY_BY_ROLE_DRAFT
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
            [RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::ADD_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnAddPolicyByRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT,
            RoleEvents::ADD_POLICY_BY_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, function (BeforeAddPolicyByRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, 10],
            [RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::ADD_POLICY_BY_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAddPolicyByRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT,
            RoleEvents::ADD_POLICY_BY_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
            $this->createMock(PolicyCreateStruct::class),
        ];

        $updatedRoleDraft = $this->createMock(RoleDraft::class);
        $eventUpdatedRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('addPolicyByRoleDraft')->willReturn($updatedRoleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, function (BeforeAddPolicyByRoleDraftEvent $event) use ($eventUpdatedRoleDraft) {
            $event->setUpdatedRoleDraft($eventUpdatedRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->addPolicyByRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::ADD_POLICY_BY_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_ADD_POLICY_BY_ROLE_DRAFT, 0],
        ]);
    }

    public function testDeleteRoleEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_ROLE,
            RoleEvents::DELETE_ROLE
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_ROLE, 0],
            [RoleEvents::DELETE_ROLE, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRoleStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_ROLE,
            RoleEvents::DELETE_ROLE
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_DELETE_ROLE, function (BeforeDeleteRoleEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRole(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_ROLE, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::DELETE_ROLE, 0],
            [RoleEvents::BEFORE_DELETE_ROLE, 0],
        ]);
    }

    public function testDeleteRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_ROLE_DRAFT,
            RoleEvents::DELETE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_ROLE_DRAFT, 0],
            [RoleEvents::DELETE_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_DELETE_ROLE_DRAFT,
            RoleEvents::DELETE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(RoleDraft::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_DELETE_ROLE_DRAFT, function (BeforeDeleteRoleDraftEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->deleteRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_DELETE_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::DELETE_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_DELETE_ROLE_DRAFT, 0],
        ]);
    }

    public function testRemoveRoleAssignmentEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT,
            RoleEvents::REMOVE_ROLE_ASSIGNMENT
        );

        $parameters = [
            $this->createMock(RoleAssignment::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->removeRoleAssignment(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT, 0],
            [RoleEvents::REMOVE_ROLE_ASSIGNMENT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testRemoveRoleAssignmentStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT,
            RoleEvents::REMOVE_ROLE_ASSIGNMENT
        );

        $parameters = [
            $this->createMock(RoleAssignment::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT, function (BeforeRemoveRoleAssignmentEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->removeRoleAssignment(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::REMOVE_ROLE_ASSIGNMENT, 0],
            [RoleEvents::BEFORE_REMOVE_ROLE_ASSIGNMENT, 0],
        ]);
    }

    public function testCreateRoleDraftEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE_DRAFT,
            RoleEvents::CREATE_ROLE_DRAFT
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
            [RoleEvents::BEFORE_CREATE_ROLE_DRAFT, 0],
            [RoleEvents::CREATE_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateRoleDraftResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE_DRAFT,
            RoleEvents::CREATE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRoleDraft')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_CREATE_ROLE_DRAFT, function (BeforeCreateRoleDraftEvent $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_CREATE_ROLE_DRAFT, 10],
            [RoleEvents::BEFORE_CREATE_ROLE_DRAFT, 0],
            [RoleEvents::CREATE_ROLE_DRAFT, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateRoleDraftStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_CREATE_ROLE_DRAFT,
            RoleEvents::CREATE_ROLE_DRAFT
        );

        $parameters = [
            $this->createMock(Role::class),
        ];

        $roleDraft = $this->createMock(RoleDraft::class);
        $eventRoleDraft = $this->createMock(RoleDraft::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('createRoleDraft')->willReturn($roleDraft);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_CREATE_ROLE_DRAFT, function (BeforeCreateRoleDraftEvent $event) use ($eventRoleDraft) {
            $event->setRoleDraft($eventRoleDraft);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createRoleDraft(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventRoleDraft, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_CREATE_ROLE_DRAFT, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::CREATE_ROLE_DRAFT, 0],
            [RoleEvents::BEFORE_CREATE_ROLE_DRAFT, 0],
        ]);
    }

    public function testUpdatePolicyEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY,
            RoleEvents::UPDATE_POLICY
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
            [RoleEvents::BEFORE_UPDATE_POLICY, 0],
            [RoleEvents::UPDATE_POLICY, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdatePolicyResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY,
            RoleEvents::UPDATE_POLICY
        );

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicy = $this->createMock(Policy::class);
        $eventUpdatedPolicy = $this->createMock(Policy::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicy')->willReturn($updatedPolicy);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_POLICY, function (BeforeUpdatePolicyEvent $event) use ($eventUpdatedPolicy) {
            $event->setUpdatedPolicy($eventUpdatedPolicy);
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedPolicy, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_POLICY, 10],
            [RoleEvents::BEFORE_UPDATE_POLICY, 0],
            [RoleEvents::UPDATE_POLICY, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdatePolicyStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UPDATE_POLICY,
            RoleEvents::UPDATE_POLICY
        );

        $parameters = [
            $this->createMock(Policy::class),
            $this->createMock(PolicyUpdateStruct::class),
        ];

        $updatedPolicy = $this->createMock(Policy::class);
        $eventUpdatedPolicy = $this->createMock(Policy::class);
        $innerServiceMock = $this->createMock(RoleServiceInterface::class);
        $innerServiceMock->method('updatePolicy')->willReturn($updatedPolicy);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UPDATE_POLICY, function (BeforeUpdatePolicyEvent $event) use ($eventUpdatedPolicy) {
            $event->setUpdatedPolicy($eventUpdatedPolicy);
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updatePolicy(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedPolicy, $result);
        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UPDATE_POLICY, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UPDATE_POLICY, 0],
            [RoleEvents::BEFORE_UPDATE_POLICY, 0],
        ]);
    }

    public function testUnassignRoleFromUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP,
            RoleEvents::UNASSIGN_ROLE_FROM_USER_GROUP
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
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP, 0],
            [RoleEvents::UNASSIGN_ROLE_FROM_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnassignRoleFromUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP,
            RoleEvents::UNASSIGN_ROLE_FROM_USER_GROUP
        );

        $parameters = [
            $this->createMock(Role::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(RoleServiceInterface::class);

        $traceableEventDispatcher->addListener(RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP, function (BeforeUnassignRoleFromUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new RoleService($innerServiceMock, $traceableEventDispatcher);
        $service->unassignRoleFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [RoleEvents::UNASSIGN_ROLE_FROM_USER_GROUP, 0],
            [RoleEvents::BEFORE_UNASSIGN_ROLE_FROM_USER_GROUP, 0],
        ]);
    }
}
