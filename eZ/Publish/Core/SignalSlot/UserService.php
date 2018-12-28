<?php

/**
 * UserService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Decorator\UserServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\MoveUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserTokenSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal;
use eZ\Publish\Core\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal;

/**
 * UserService class.
 */
class UserService extends UserServiceDecorator
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
     * @param \eZ\Publish\API\Repository\UserService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(UserServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        parent::__construct($service);

        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Creates a new user group using the data provided in the ContentCreateStruct parameter.
     *
     * In 4.x in the content type parameter in the profile is ignored
     * - the content type is determined via configuration and can be set to null.
     * The returned version is published.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $userGroupCreateStruct a structure for setting all necessary data to create this user group
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $parentGroup
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the input structure has invalid data
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set to an empty value
     */
    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup)
    {
        $returnValue = $this->service->createUserGroup($userGroupCreateStruct, $parentGroup);
        $this->signalDispatcher->emit(
            new CreateUserGroupSignal(
                array(
                    'userGroupId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Removes a user group.
     *
     * the users which are not assigned to other groups will be deleted.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to create a user group
     */
    public function deleteUserGroup(UserGroup $userGroup)
    {
        $returnValue = $this->service->deleteUserGroup($userGroup);
        $this->signalDispatcher->emit(
            new DeleteUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                    'affectedLocationIds' => $returnValue,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Moves the user group to another parent.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $newParent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     */
    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent)
    {
        $returnValue = $this->service->moveUserGroup($userGroup, $newParent);
        $this->signalDispatcher->emit(
            new MoveUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                    'newParentId' => $newParent->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates the group profile with fields and meta data.
     *
     * 4.x: If the versionUpdateStruct is set in $userGroupUpdateStruct, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct $userGroupUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userGroupUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     */
    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        $returnValue = $this->service->updateUserGroup($userGroup, $userGroupUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateUserGroupSignal(
                array(
                    'userGroupId' => $userGroup->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Create a new user. The created user is published by this method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreateStruct the data used for creating the user
     * @param array $parentGroups the groups of type {@link \eZ\Publish\API\Repository\Values\User\UserGroup} which are assigned to the user after creation
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to move the user group
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or set  to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     *                                                                        if a user with provided login already exists
     */
    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        $returnValue = $this->service->createUser($userCreateStruct, $parentGroups);
        $this->signalDispatcher->emit(
            new CreateUserSignal(
                array(
                    'userId' => $returnValue->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * This method deletes a user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to delete the user
     */
    public function deleteUser(User $user)
    {
        $returnValue = $this->service->deleteUser($user);
        $this->signalDispatcher->emit(
            new DeleteUserSignal(
                array(
                    'userId' => $user->id,
                    'affectedLocationIds' => $returnValue,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Updates a user.
     *
     * 4.x: If the versionUpdateStruct is set in the user update structure, this method internally creates a content draft, updates ts with the provided data
     * and publishes the draft. If a draft is explicitly required, the user group can be updated via the content service methods.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserUpdateStruct $userUpdateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to update the user
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $userUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set empty
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct)
    {
        $returnValue = $this->service->updateUser($user, $userUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateUserSignal(
                array(
                    'userId' => $user->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Update the user account key information specified by the user account key struct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct $userTokenUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $returnValue = $this->service->updateUserToken($user, $userTokenUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateUserTokenSignal(
                ['userId' => $user->id]
            )
        );

        return $returnValue;
    }

    /**
     * Assigns a new user group to the user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to assign the user group to the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is already in the given user group
     */
    public function assignUserToUserGroup(User $user, UserGroup $userGroup)
    {
        $returnValue = $this->service->assignUserToUserGroup($user, $userGroup);
        $this->signalDispatcher->emit(
            new AssignUserToUserGroupSignal(
                array(
                    'userId' => $user->id,
                    'userGroupId' => $userGroup->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Removes a user group from the user.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the authenticated user is not allowed to remove the user group from the user
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the user is not in the given user group
     */
    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup)
    {
        $returnValue = $this->service->unAssignUserFromUserGroup($user, $userGroup);
        $this->signalDispatcher->emit(
            new UnAssignUserFromUserGroupSignal(
                array(
                    'userId' => $user->id,
                    'userGroupId' => $userGroup->id,
                )
            )
        );

        return $returnValue;
    }
}
