<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Event\UserService;
use eZ\Publish\Core\Event\User\BeforeAssignUserToUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeCreateUserEvent;
use eZ\Publish\Core\Event\User\BeforeCreateUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeDeleteUserEvent;
use eZ\Publish\Core\Event\User\BeforeDeleteUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeMoveUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeUnAssignUserFromUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeUpdateUserEvent;
use eZ\Publish\Core\Event\User\BeforeUpdateUserGroupEvent;
use eZ\Publish\Core\Event\User\BeforeUpdateUserTokenEvent;
use eZ\Publish\Core\Event\User\UserEvents;

class UserServiceTest extends AbstractServiceTest
{
    public function testUpdateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_GROUP,
            UserEvents::UPDATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_GROUP, 0],
            [UserEvents::UPDATE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_GROUP,
            UserEvents::UPDATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER_GROUP, function (BeforeUpdateUserGroupEvent $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_GROUP, 10],
            [UserEvents::BEFORE_UPDATE_USER_GROUP, 0],
            [UserEvents::UPDATE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_GROUP,
            UserEvents::UPDATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER_GROUP, function (BeforeUpdateUserGroupEvent $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::UPDATE_USER_GROUP, 0],
            [UserEvents::BEFORE_UPDATE_USER_GROUP, 0],
        ]);
    }

    public function testUpdateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER,
            UserEvents::UPDATE_USER
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER, 0],
            [UserEvents::UPDATE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER,
            UserEvents::UPDATE_USER
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER, function (BeforeUpdateUserEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER, 10],
            [UserEvents::BEFORE_UPDATE_USER, 0],
            [UserEvents::UPDATE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER,
            UserEvents::UPDATE_USER
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER, function (BeforeUpdateUserEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::UPDATE_USER, 0],
            [UserEvents::BEFORE_UPDATE_USER, 0],
        ]);
    }

    public function testUnAssignUserFromUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP,
            UserEvents::UN_ASSIGN_USER_FROM_USER_GROUP
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->unAssignUserFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP, 0],
            [UserEvents::UN_ASSIGN_USER_FROM_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnAssignUserFromUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP,
            UserEvents::UN_ASSIGN_USER_FROM_USER_GROUP
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP, function (BeforeUnAssignUserFromUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->unAssignUserFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::UN_ASSIGN_USER_FROM_USER_GROUP, 0],
            [UserEvents::BEFORE_UN_ASSIGN_USER_FROM_USER_GROUP, 0],
        ]);
    }

    public function testDeleteUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER_GROUP,
            UserEvents::DELETE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($locations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER_GROUP, 0],
            [UserEvents::DELETE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER_GROUP,
            UserEvents::DELETE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_DELETE_USER_GROUP, function (BeforeDeleteUserGroupEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER_GROUP, 10],
            [UserEvents::BEFORE_DELETE_USER_GROUP, 0],
            [UserEvents::DELETE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER_GROUP,
            UserEvents::DELETE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_DELETE_USER_GROUP, function (BeforeDeleteUserGroupEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::DELETE_USER_GROUP, 0],
            [UserEvents::BEFORE_DELETE_USER_GROUP, 0],
        ]);
    }

    public function testAssignUserToUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP,
            UserEvents::ASSIGN_USER_TO_USER_GROUP
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->assignUserToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP, 0],
            [UserEvents::ASSIGN_USER_TO_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignUserToUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP,
            UserEvents::ASSIGN_USER_TO_USER_GROUP
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP, function (BeforeAssignUserToUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->assignUserToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::ASSIGN_USER_TO_USER_GROUP, 0],
            [UserEvents::BEFORE_ASSIGN_USER_TO_USER_GROUP, 0],
        ]);
    }

    public function testDeleteUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER,
            UserEvents::DELETE_USER
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($locations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER, 0],
            [UserEvents::DELETE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER,
            UserEvents::DELETE_USER
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_DELETE_USER, function (BeforeDeleteUserEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER, 10],
            [UserEvents::BEFORE_DELETE_USER, 0],
            [UserEvents::DELETE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_DELETE_USER,
            UserEvents::DELETE_USER
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_DELETE_USER, function (BeforeDeleteUserEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_DELETE_USER, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::DELETE_USER, 0],
            [UserEvents::BEFORE_DELETE_USER, 0],
        ]);
    }

    public function testMoveUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_MOVE_USER_GROUP,
            UserEvents::MOVE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->moveUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_MOVE_USER_GROUP, 0],
            [UserEvents::MOVE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_MOVE_USER_GROUP,
            UserEvents::MOVE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_MOVE_USER_GROUP, function (BeforeMoveUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->moveUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_MOVE_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::MOVE_USER_GROUP, 0],
            [UserEvents::BEFORE_MOVE_USER_GROUP, 0],
        ]);
    }

    public function testCreateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER,
            UserEvents::CREATE_USER
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($user, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER, 0],
            [UserEvents::CREATE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER,
            UserEvents::CREATE_USER
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_CREATE_USER, function (BeforeCreateUserEvent $event) use ($eventUser) {
            $event->setUser($eventUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER, 10],
            [UserEvents::BEFORE_CREATE_USER, 0],
            [UserEvents::CREATE_USER, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER,
            UserEvents::CREATE_USER
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_CREATE_USER, function (BeforeCreateUserEvent $event) use ($eventUser) {
            $event->setUser($eventUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::CREATE_USER, 0],
            [UserEvents::BEFORE_CREATE_USER, 0],
        ]);
    }

    public function testCreateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER_GROUP,
            UserEvents::CREATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($userGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER_GROUP, 0],
            [UserEvents::CREATE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER_GROUP,
            UserEvents::CREATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_CREATE_USER_GROUP, function (BeforeCreateUserGroupEvent $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER_GROUP, 10],
            [UserEvents::BEFORE_CREATE_USER_GROUP, 0],
            [UserEvents::CREATE_USER_GROUP, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_CREATE_USER_GROUP,
            UserEvents::CREATE_USER_GROUP
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_CREATE_USER_GROUP, function (BeforeCreateUserGroupEvent $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_CREATE_USER_GROUP, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::CREATE_USER_GROUP, 0],
            [UserEvents::BEFORE_CREATE_USER_GROUP, 0],
        ]);
    }

    public function testUpdateUserTokenEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_TOKEN,
            UserEvents::UPDATE_USER_TOKEN
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($updatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_TOKEN, 0],
            [UserEvents::UPDATE_USER_TOKEN, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserTokenResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_TOKEN,
            UserEvents::UPDATE_USER_TOKEN
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER_TOKEN, function (BeforeUpdateUserTokenEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_TOKEN, 10],
            [UserEvents::BEFORE_UPDATE_USER_TOKEN, 0],
            [UserEvents::UPDATE_USER_TOKEN, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserTokenStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            UserEvents::BEFORE_UPDATE_USER_TOKEN,
            UserEvents::UPDATE_USER_TOKEN
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(UserEvents::BEFORE_UPDATE_USER_TOKEN, function (BeforeUpdateUserTokenEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [UserEvents::BEFORE_UPDATE_USER_TOKEN, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [UserEvents::UPDATE_USER_TOKEN, 0],
            [UserEvents::BEFORE_UPDATE_USER_TOKEN, 0],
        ]);
    }
}
