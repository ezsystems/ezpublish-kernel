<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

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
use eZ\Publish\SPI\Repository\Decorator\UserServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserService extends UserServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        UserServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createUserGroup(
        UserGroupCreateStruct $userGroupCreateStruct,
        UserGroup $parentGroup
    ) {
        $eventData = [
            $userGroupCreateStruct,
            $parentGroup,
        ];

        $beforeEvent = new BeforeCreateUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUserGroup();
        }

        $userGroup = $beforeEvent->hasUserGroup()
            ? $beforeEvent->getUserGroup()
            : $this->innerService->createUserGroup($userGroupCreateStruct, $parentGroup);

        $this->eventDispatcher->dispatch(
            new CreateUserGroupEvent($userGroup, ...$eventData),
            CreateUserGroupEventInterface::class
        );

        return $userGroup;
    }

    public function deleteUserGroup(UserGroup $userGroup)
    {
        $eventData = [$userGroup];

        $beforeEvent = new BeforeDeleteUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : $this->innerService->deleteUserGroup($userGroup);

        $this->eventDispatcher->dispatch(
            new DeleteUserGroupEvent($locations, ...$eventData),
            DeleteUserGroupEventInterface::class
        );

        return $locations;
    }

    public function moveUserGroup(
        UserGroup $userGroup,
        UserGroup $newParent
    ): void {
        $eventData = [
            $userGroup,
            $newParent,
        ];

        $beforeEvent = new BeforeMoveUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeMoveUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->moveUserGroup($userGroup, $newParent);

        $this->eventDispatcher->dispatch(
            new MoveUserGroupEvent(...$eventData),
            MoveUserGroupEventInterface::class
        );
    }

    public function updateUserGroup(
        UserGroup $userGroup,
        UserGroupUpdateStruct $userGroupUpdateStruct
    ) {
        $eventData = [
            $userGroup,
            $userGroupUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUserGroup();
        }

        $updatedUserGroup = $beforeEvent->hasUpdatedUserGroup()
            ? $beforeEvent->getUpdatedUserGroup()
            : $this->innerService->updateUserGroup($userGroup, $userGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserGroupEvent($updatedUserGroup, ...$eventData),
            UpdateUserGroupEventInterface::class
        );

        return $updatedUserGroup;
    }

    public function createUser(
        UserCreateStruct $userCreateStruct,
        array $parentGroups
    ) {
        $eventData = [
            $userCreateStruct,
            $parentGroups,
        ];

        $beforeEvent = new BeforeCreateUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateUserEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUser();
        }

        $user = $beforeEvent->hasUser()
            ? $beforeEvent->getUser()
            : $this->innerService->createUser($userCreateStruct, $parentGroups);

        $this->eventDispatcher->dispatch(
            new CreateUserEvent($user, ...$eventData),
            CreateUserEventInterface::class
        );

        return $user;
    }

    public function deleteUser(User $user)
    {
        $eventData = [$user];

        $beforeEvent = new BeforeDeleteUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteUserEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : $this->innerService->deleteUser($user);

        $this->eventDispatcher->dispatch(
            new DeleteUserEvent($locations, ...$eventData),
            DeleteUserEventInterface::class
        );

        return $locations;
    }

    public function updateUser(
        User $user,
        UserUpdateStruct $userUpdateStruct
    ) {
        $eventData = [
            $user,
            $userUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateUserEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUser();
        }

        $updatedUser = $beforeEvent->hasUpdatedUser()
            ? $beforeEvent->getUpdatedUser()
            : $this->innerService->updateUser($user, $userUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserEvent($updatedUser, ...$eventData),
            UpdateUserEventInterface::class
        );

        return $updatedUser;
    }

    public function updateUserToken(
        User $user,
        UserTokenUpdateStruct $userTokenUpdateStruct
    ) {
        $eventData = [
            $user,
            $userTokenUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserTokenEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateUserTokenEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUser();
        }

        $updatedUser = $beforeEvent->hasUpdatedUser()
            ? $beforeEvent->getUpdatedUser()
            : $this->innerService->updateUserToken($user, $userTokenUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserTokenEvent($updatedUser, ...$eventData),
            UpdateUserTokenEventInterface::class
        );

        return $updatedUser;
    }

    public function assignUserToUserGroup(
        User $user,
        UserGroup $userGroup
    ): void {
        $eventData = [
            $user,
            $userGroup,
        ];

        $beforeEvent = new BeforeAssignUserToUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAssignUserToUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignUserToUserGroup($user, $userGroup);

        $this->eventDispatcher->dispatch(
            new AssignUserToUserGroupEvent(...$eventData),
            AssignUserToUserGroupEventInterface::class
        );
    }

    public function unAssignUserFromUserGroup(
        User $user,
        UserGroup $userGroup
    ): void {
        $eventData = [
            $user,
            $userGroup,
        ];

        $beforeEvent = new BeforeUnAssignUserFromUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUnAssignUserFromUserGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unAssignUserFromUserGroup($user, $userGroup);

        $this->eventDispatcher->dispatch(
            new UnAssignUserFromUserGroupEvent(...$eventData),
            UnAssignUserFromUserGroupEventInterface::class
        );
    }
}
