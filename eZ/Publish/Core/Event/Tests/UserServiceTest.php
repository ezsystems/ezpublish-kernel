<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Events\Tests;

use eZ\Publish\API\Repository\Events\User\AssignUserToUserGroupEvent as AssignUserToUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeAssignUserToUserGroupEvent as BeforeAssignUserToUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeCreateUserEvent as BeforeCreateUserEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeCreateUserGroupEvent as BeforeCreateUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeDeleteUserEvent as BeforeDeleteUserEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeDeleteUserGroupEvent as BeforeDeleteUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeMoveUserGroupEvent as BeforeMoveUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeUnAssignUserFromUserGroupEvent as BeforeUnAssignUserFromUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserEvent as BeforeUpdateUserEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserGroupEvent as BeforeUpdateUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\BeforeUpdateUserTokenEvent as BeforeUpdateUserTokenEventInterface;
use eZ\Publish\API\Repository\Events\User\CreateUserEvent as CreateUserEventInterface;
use eZ\Publish\API\Repository\Events\User\CreateUserGroupEvent as CreateUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\DeleteUserEvent as DeleteUserEventInterface;
use eZ\Publish\API\Repository\Events\User\DeleteUserGroupEvent as DeleteUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\MoveUserGroupEvent as MoveUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\UnAssignUserFromUserGroupEvent as UnAssignUserFromUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\UpdateUserEvent as UpdateUserEventInterface;
use eZ\Publish\API\Repository\Events\User\UpdateUserGroupEvent as UpdateUserGroupEventInterface;
use eZ\Publish\API\Repository\Events\User\UpdateUserTokenEvent as UpdateUserTokenEventInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Events\UserService;

class UserServiceTest extends AbstractServiceTest
{
    public function testUpdateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEventInterface::class,
            UpdateUserGroupEventInterface::class
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
            [BeforeUpdateUserGroupEventInterface::class, 0],
            [UpdateUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEventInterface::class,
            UpdateUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateUserGroupEventInterface::class, function (BeforeUpdateUserGroupEventInterface $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserGroupEventInterface::class, 10],
            [BeforeUpdateUserGroupEventInterface::class, 0],
            [UpdateUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserGroupEventInterface::class,
            UpdateUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroupUpdateStruct::class),
        ];

        $updatedUserGroup = $this->createMock(UserGroup::class);
        $eventUpdatedUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserGroup')->willReturn($updatedUserGroup);

        $traceableEventDispatcher->addListener(BeforeUpdateUserGroupEventInterface::class, function (BeforeUpdateUserGroupEventInterface $event) use ($eventUpdatedUserGroup) {
            $event->setUpdatedUserGroup($eventUpdatedUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserGroupEventInterface::class, 0],
            [UpdateUserGroupEventInterface::class, 0],
        ]);
    }

    public function testUpdateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEventInterface::class,
            UpdateUserEventInterface::class
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
            [BeforeUpdateUserEventInterface::class, 0],
            [UpdateUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEventInterface::class,
            UpdateUserEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserEventInterface::class, function (BeforeUpdateUserEventInterface $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserEventInterface::class, 10],
            [BeforeUpdateUserEventInterface::class, 0],
            [UpdateUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserEventInterface::class,
            UpdateUserEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUser')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserEventInterface::class, function (BeforeUpdateUserEventInterface $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserEventInterface::class, 0],
            [UpdateUserEventInterface::class, 0],
        ]);
    }

    public function testUnAssignUserFromUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnAssignUserFromUserGroupEventInterface::class,
            UnAssignUserFromUserGroupEventInterface::class
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
            [BeforeUnAssignUserFromUserGroupEventInterface::class, 0],
            [UnAssignUserFromUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUnAssignUserFromUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUnAssignUserFromUserGroupEventInterface::class,
            UnAssignUserFromUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeUnAssignUserFromUserGroupEventInterface::class, function (BeforeUnAssignUserFromUserGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->unAssignUserFromUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeUnAssignUserFromUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUnAssignUserFromUserGroupEventInterface::class, 0],
            [UnAssignUserFromUserGroupEventInterface::class, 0],
        ]);
    }

    public function testDeleteUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEventInterface::class,
            DeleteUserGroupEventInterface::class
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
            [BeforeDeleteUserGroupEventInterface::class, 0],
            [DeleteUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEventInterface::class,
            DeleteUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserGroupEventInterface::class, function (BeforeDeleteUserGroupEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserGroupEventInterface::class, 10],
            [BeforeDeleteUserGroupEventInterface::class, 0],
            [DeleteUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserGroupEventInterface::class,
            DeleteUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUserGroup')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserGroupEventInterface::class, function (BeforeDeleteUserGroupEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteUserGroupEventInterface::class, 0],
            [DeleteUserGroupEventInterface::class, 0],
        ]);
    }

    public function testAssignUserToUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignUserToUserGroupEventInterface::class,
            AssignUserToUserGroupEventInterface::class
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
            [BeforeAssignUserToUserGroupEventInterface::class, 0],
            [AssignUserToUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testAssignUserToUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeAssignUserToUserGroupEventInterface::class,
            AssignUserToUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeAssignUserToUserGroupEventInterface::class, function (BeforeAssignUserToUserGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->assignUserToUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeAssignUserToUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [AssignUserToUserGroupEventInterface::class, 0],
            [BeforeAssignUserToUserGroupEventInterface::class, 0],
        ]);
    }

    public function testDeleteUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEventInterface::class,
            DeleteUserEventInterface::class
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
            [BeforeDeleteUserEventInterface::class, 0],
            [DeleteUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnDeleteUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEventInterface::class,
            DeleteUserEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserEventInterface::class, function (BeforeDeleteUserEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserEventInterface::class, 10],
            [BeforeDeleteUserEventInterface::class, 0],
            [DeleteUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testDeleteUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeDeleteUserEventInterface::class,
            DeleteUserEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
        ];

        $locations = [];
        $eventLocations = [];
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('deleteUser')->willReturn($locations);

        $traceableEventDispatcher->addListener(BeforeDeleteUserEventInterface::class, function (BeforeDeleteUserEventInterface $event) use ($eventLocations) {
            $event->setLocations($eventLocations);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->deleteUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventLocations, $result);
        $this->assertSame($calledListeners, [
            [BeforeDeleteUserEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeDeleteUserEventInterface::class, 0],
            [DeleteUserEventInterface::class, 0],
        ]);
    }

    public function testMoveUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveUserGroupEventInterface::class,
            MoveUserGroupEventInterface::class
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
            [BeforeMoveUserGroupEventInterface::class, 0],
            [MoveUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testMoveUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeMoveUserGroupEventInterface::class,
            MoveUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroup::class),
            $this->createMock(UserGroup::class),
        ];

        $innerServiceMock = $this->createMock(UserServiceInterface::class);

        $traceableEventDispatcher->addListener(BeforeMoveUserGroupEventInterface::class, function (BeforeMoveUserGroupEventInterface $event) {
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $service->moveUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($calledListeners, [
            [BeforeMoveUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeMoveUserGroupEventInterface::class, 0],
            [MoveUserGroupEventInterface::class, 0],
        ]);
    }

    public function testCreateUserEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEventInterface::class,
            CreateUserEventInterface::class
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
            [BeforeCreateUserEventInterface::class, 0],
            [CreateUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEventInterface::class,
            CreateUserEventInterface::class
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(BeforeCreateUserEventInterface::class, function (BeforeCreateUserEventInterface $event) use ($eventUser) {
            $event->setUser($eventUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserEventInterface::class, 10],
            [BeforeCreateUserEventInterface::class, 0],
            [CreateUserEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserEventInterface::class,
            CreateUserEventInterface::class
        );

        $parameters = [
            $this->createMock(UserCreateStruct::class),
            [],
        ];

        $user = $this->createMock(User::class);
        $eventUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUser')->willReturn($user);

        $traceableEventDispatcher->addListener(BeforeCreateUserEventInterface::class, function (BeforeCreateUserEventInterface $event) use ($eventUser) {
            $event->setUser($eventUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUser(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateUserEventInterface::class, 0],
            [CreateUserEventInterface::class, 0],
        ]);
    }

    public function testCreateUserGroupEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEventInterface::class,
            CreateUserGroupEventInterface::class
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
            [BeforeCreateUserGroupEventInterface::class, 0],
            [CreateUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnCreateUserGroupResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEventInterface::class,
            CreateUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(BeforeCreateUserGroupEventInterface::class, function (BeforeCreateUserGroupEventInterface $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserGroupEventInterface::class, 10],
            [BeforeCreateUserGroupEventInterface::class, 0],
            [CreateUserGroupEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testCreateUserGroupStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeCreateUserGroupEventInterface::class,
            CreateUserGroupEventInterface::class
        );

        $parameters = [
            $this->createMock(UserGroupCreateStruct::class),
            $this->createMock(UserGroup::class),
        ];

        $userGroup = $this->createMock(UserGroup::class);
        $eventUserGroup = $this->createMock(UserGroup::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('createUserGroup')->willReturn($userGroup);

        $traceableEventDispatcher->addListener(BeforeCreateUserGroupEventInterface::class, function (BeforeCreateUserGroupEventInterface $event) use ($eventUserGroup) {
            $event->setUserGroup($eventUserGroup);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->createUserGroup(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUserGroup, $result);
        $this->assertSame($calledListeners, [
            [BeforeCreateUserGroupEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeCreateUserGroupEventInterface::class, 0],
            [CreateUserGroupEventInterface::class, 0],
        ]);
    }

    public function testUpdateUserTokenEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEventInterface::class,
            UpdateUserTokenEventInterface::class
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
            [BeforeUpdateUserTokenEventInterface::class, 0],
            [UpdateUserTokenEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testReturnUpdateUserTokenResultInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEventInterface::class,
            UpdateUserTokenEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserTokenEventInterface::class, function (BeforeUpdateUserTokenEventInterface $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserTokenEventInterface::class, 10],
            [BeforeUpdateUserTokenEventInterface::class, 0],
            [UpdateUserTokenEventInterface::class, 0],
        ]);
        $this->assertSame([], $traceableEventDispatcher->getNotCalledListeners());
    }

    public function testUpdateUserTokenStopPropagationInBeforeEvents()
    {
        $traceableEventDispatcher = $this->getEventDispatcher(
            BeforeUpdateUserTokenEventInterface::class,
            UpdateUserTokenEventInterface::class
        );

        $parameters = [
            $this->createMock(User::class),
            $this->createMock(UserTokenUpdateStruct::class),
        ];

        $updatedUser = $this->createMock(User::class);
        $eventUpdatedUser = $this->createMock(User::class);
        $innerServiceMock = $this->createMock(UserServiceInterface::class);
        $innerServiceMock->method('updateUserToken')->willReturn($updatedUser);

        $traceableEventDispatcher->addListener(BeforeUpdateUserTokenEventInterface::class, function (BeforeUpdateUserTokenEventInterface $event) use ($eventUpdatedUser) {
            $event->setUpdatedUser($eventUpdatedUser);
            $event->stopPropagation();
        }, 10);

        $service = new UserService($innerServiceMock, $traceableEventDispatcher);
        $result = $service->updateUserToken(...$parameters);

        $calledListeners = $this->getListenersStack($traceableEventDispatcher->getCalledListeners());
        $notCalledListeners = $this->getListenersStack($traceableEventDispatcher->getNotCalledListeners());

        $this->assertSame($eventUpdatedUser, $result);
        $this->assertSame($calledListeners, [
            [BeforeUpdateUserTokenEventInterface::class, 10],
        ]);
        $this->assertSame($notCalledListeners, [
            [BeforeUpdateUserTokenEventInterface::class, 0],
            [UpdateUserTokenEventInterface::class, 0],
        ]);
    }
}
