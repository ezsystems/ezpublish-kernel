<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Event\Tests;

use eZ\Publish\API\Repository\Events\User\AssignUserToUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeAssignUserToUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeCreateUserEvent;
use eZ\Publish\API\Repository\Events\User\BeforeCreateUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeDeleteUserEvent;
use eZ\Publish\API\Repository\Events\User\BeforeDeleteUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeMoveUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeUnAssignUserFromUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserEvent;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserTokenEvent;
use eZ\Publish\API\Repository\Events\User\CreateUserEvent;
use eZ\Publish\API\Repository\Events\User\CreateUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\DeleteUserEvent;
use eZ\Publish\API\Repository\Events\User\DeleteUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\MoveUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\UnAssignUserFromUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\UpdateUserEvent;
use eZ\Publish\API\Repository\Events\User\UpdateUserGroupEvent;
use eZ\Publish\API\Repository\Events\User\UpdateUserTokenEvent;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Event\UserService;

class UserServiceTest extends AbstractServiceTest
{
    public function testUpdateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEvent::class,
            UpdateUserGroupEvent::class
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
            [BeforeUpdateUserGroupEvent::class, 0],
            [UpdateUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEvent::class,
            UpdateUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateUserGroupEvent::class, function (BeforeUpdateUserGroupEvent $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserGroupEvent::class, 10],
            [BeforeUpdateUserGroupEvent::class, 0],
            [UpdateUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEvent::class,
            UpdateUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateUserGroupEvent::class, function (BeforeUpdateUserGroupEvent $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserGroupEvent::class, 0],
            [UpdateUserGroupEvent::class, 0],
        ]);
    }

    public function testUpdateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEvent::class,
            UpdateUserEvent::class
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
            [BeforeUpdateUserEvent::class, 0],
            [UpdateUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEvent::class,
            UpdateUserEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserEvent::class, function (BeforeUpdateUserEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserEvent::class, 10],
            [BeforeUpdateUserEvent::class, 0],
            [UpdateUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEvent::class,
            UpdateUserEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserEvent::class, function (BeforeUpdateUserEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserEvent::class, 0],
            [UpdateUserEvent::class, 0],
        ]);
    }

    public function testUnAssignUserFromUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnAssignUserFromUserGroupEvent::class,
            UnAssignUserFromUserGroupEvent::class
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
            [BeforeUnAssignUserFromUserGroupEvent::class, 0],
            [UnAssignUserFromUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnAssignUserFromUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnAssignUserFromUserGroupEvent::class,
            UnAssignUserFromUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnAssignUserFromUserGroupEvent::class, function (BeforeUnAssignUserFromUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->unAssignUserFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnAssignUserFromUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnAssignUserFromUserGroupEvent::class, 0],
            [UnAssignUserFromUserGroupEvent::class, 0],
        ]);
    }

    public function testDeleteUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEvent::class,
            DeleteUserGroupEvent::class
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
            [BeforeDeleteUserGroupEvent::class, 0],
            [DeleteUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEvent::class,
            DeleteUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserGroupEvent::class, function (BeforeDeleteUserGroupEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserGroupEvent::class, 10],
            [BeforeDeleteUserGroupEvent::class, 0],
            [DeleteUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEvent::class,
            DeleteUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserGroupEvent::class, function (BeforeDeleteUserGroupEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteUserGroupEvent::class, 0],
            [DeleteUserGroupEvent::class, 0],
        ]);
    }

    public function testAssignUserToUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignUserToUserGroupEvent::class,
            AssignUserToUserGroupEvent::class
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
            [BeforeAssignUserToUserGroupEvent::class, 0],
            [AssignUserToUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignUserToUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignUserToUserGroupEvent::class,
            AssignUserToUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignUserToUserGroupEvent::class, function (BeforeAssignUserToUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->assignUserToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignUserToUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignUserToUserGroupEvent::class, 0],
            [BeforeAssignUserToUserGroupEvent::class, 0],
        ]);
    }

    public function testDeleteUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEvent::class,
            DeleteUserEvent::class
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
            [BeforeDeleteUserEvent::class, 0],
            [DeleteUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEvent::class,
            DeleteUserEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserEvent::class, function (BeforeDeleteUserEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserEvent::class, 10],
            [BeforeDeleteUserEvent::class, 0],
            [DeleteUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEvent::class,
            DeleteUserEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserEvent::class, function (BeforeDeleteUserEvent $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteUserEvent::class, 0],
            [DeleteUserEvent::class, 0],
        ]);
    }

    public function testMoveUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveUserGroupEvent::class,
            MoveUserGroupEvent::class
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
            [BeforeMoveUserGroupEvent::class, 0],
            [MoveUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveUserGroupEvent::class,
            MoveUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMoveUserGroupEvent::class, function (BeforeMoveUserGroupEvent $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->moveUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMoveUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMoveUserGroupEvent::class, 0],
            [MoveUserGroupEvent::class, 0],
        ]);
    }

    public function testCreateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEvent::class,
            CreateUserEvent::class
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
            [BeforeCreateUserEvent::class, 0],
            [CreateUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEvent::class,
            CreateUserEvent::class
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(BeforeCreateUserEvent::class, function (BeforeCreateUserEvent $event) use ($eventUser) {
            $event->setUser($eventUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserEvent::class, 10],
            [BeforeCreateUserEvent::class, 0],
            [CreateUserEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEvent::class,
            CreateUserEvent::class
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(BeforeCreateUserEvent::class, function (BeforeCreateUserEvent $event) use ($eventUser) {
            $event->setUser($eventUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateUserEvent::class, 0],
            [CreateUserEvent::class, 0],
        ]);
    }

    public function testCreateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEvent::class,
            CreateUserGroupEvent::class
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
            [BeforeCreateUserGroupEvent::class, 0],
            [CreateUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEvent::class,
            CreateUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(BeforeCreateUserGroupEvent::class, function (BeforeCreateUserGroupEvent $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserGroupEvent::class, 10],
            [BeforeCreateUserGroupEvent::class, 0],
            [CreateUserGroupEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEvent::class,
            CreateUserGroupEvent::class
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(BeforeCreateUserGroupEvent::class, function (BeforeCreateUserGroupEvent $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserGroupEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateUserGroupEvent::class, 0],
            [CreateUserGroupEvent::class, 0],
        ]);
    }

    public function testUpdateUserTokenEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEvent::class,
            UpdateUserTokenEvent::class
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
            [BeforeUpdateUserTokenEvent::class, 0],
            [UpdateUserTokenEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserTokenResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEvent::class,
            UpdateUserTokenEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserTokenEvent::class, function (BeforeUpdateUserTokenEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserTokenEvent::class, 10],
            [BeforeUpdateUserTokenEvent::class, 0],
            [UpdateUserTokenEvent::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserTokenStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEvent::class,
            UpdateUserTokenEvent::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserTokenEvent::class, function (BeforeUpdateUserTokenEvent $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserTokenEvent::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserTokenEvent::class, 0],
            [UpdateUserTokenEvent::class, 0],
        ]);
    }
}
