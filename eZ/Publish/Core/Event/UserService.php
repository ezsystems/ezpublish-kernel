<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

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
    ): UserGroup {
        $eventData = [
            $userGroupCreateStruct,
            $parentGroup,
        ];

        $beforeEvent = new BeforeCreateUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUserGroup();
        }

        $userGroup = $beforeEvent->hasUserGroup()
            ? $beforeEvent->getUserGroup()
            : $this->innerService->createUserGroup($userGroupCreateStruct, $parentGroup);

        $this->eventDispatcher->dispatch(
            new CreateUserGroupEvent($userGroup, ...$eventData)
        );

        return $userGroup;
    }

    public function deleteUserGroup(UserGroup $userGroup): iterable
    {
        $eventData = [$userGroup];

        $beforeEvent = new BeforeDeleteUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : $this->innerService->deleteUserGroup($userGroup);

        $this->eventDispatcher->dispatch(
            new DeleteUserGroupEvent($locations, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->moveUserGroup($userGroup, $newParent);

        $this->eventDispatcher->dispatch(
            new MoveUserGroupEvent(...$eventData)
        );
    }

    public function updateUserGroup(
        UserGroup $userGroup,
        UserGroupUpdateStruct $userGroupUpdateStruct
    ): UserGroup {
        $eventData = [
            $userGroup,
            $userGroupUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUserGroup();
        }

        $updatedUserGroup = $beforeEvent->hasUpdatedUserGroup()
            ? $beforeEvent->getUpdatedUserGroup()
            : $this->innerService->updateUserGroup($userGroup, $userGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserGroupEvent($updatedUserGroup, ...$eventData)
        );

        return $updatedUserGroup;
    }

    public function createUser(
        UserCreateStruct $userCreateStruct,
        array $parentGroups
    ): User {
        $eventData = [
            $userCreateStruct,
            $parentGroups,
        ];

        $beforeEvent = new BeforeCreateUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUser();
        }

        $user = $beforeEvent->hasUser()
            ? $beforeEvent->getUser()
            : $this->innerService->createUser($userCreateStruct, $parentGroups);

        $this->eventDispatcher->dispatch(
            new CreateUserEvent($user, ...$eventData)
        );

        return $user;
    }

    public function deleteUser(User $user): iterable
    {
        $eventData = [$user];

        $beforeEvent = new BeforeDeleteUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : $this->innerService->deleteUser($user);

        $this->eventDispatcher->dispatch(
            new DeleteUserEvent($locations, ...$eventData)
        );

        return $locations;
    }

    public function updateUser(
        User $user,
        UserUpdateStruct $userUpdateStruct
    ): User {
        $eventData = [
            $user,
            $userUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUser();
        }

        $updatedUser = $beforeEvent->hasUpdatedUser()
            ? $beforeEvent->getUpdatedUser()
            : $this->innerService->updateUser($user, $userUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserEvent($updatedUser, ...$eventData)
        );

        return $updatedUser;
    }

    public function updateUserToken(
        User $user,
        UserTokenUpdateStruct $userTokenUpdateStruct
    ): User {
        $eventData = [
            $user,
            $userTokenUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateUserTokenEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getUpdatedUser();
        }

        $updatedUser = $beforeEvent->hasUpdatedUser()
            ? $beforeEvent->getUpdatedUser()
            : $this->innerService->updateUserToken($user, $userTokenUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateUserTokenEvent($updatedUser, ...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignUserToUserGroup($user, $userGroup);

        $this->eventDispatcher->dispatch(
            new AssignUserToUserGroupEvent(...$eventData)
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

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unAssignUserFromUserGroup($user, $userGroup);

        $this->eventDispatcher->dispatch(
            new UnAssignUserFromUserGroupEvent(...$eventData)
        );
    }
}
