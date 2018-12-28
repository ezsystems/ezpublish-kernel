<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;

abstract class UserServiceDecorator implements UserService
{
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\UserService $innerService
     */
    public function __construct(UserService $innerService)
    {
        $this->service = $innerService;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup)
    {
        return $this->service->createUserGroup($userGroupCreateStruct, $parentGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserGroup($id, array $prioritizedLanguages = [])
    {
        return $this->service->loadUserGroup($id, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadSubUserGroups(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        return $this->service->loadSubUserGroups($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUserGroup(UserGroup $userGroup)
    {
        return $this->service->deleteUserGroup($userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent)
    {
        return $this->service->moveUserGroup($userGroup, $newParent);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        return $this->service->updateUserGroup($userGroup, $userGroupUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        return $this->service->createUser($userCreateStruct, $parentGroups);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUser($userId, array $prioritizedLanguages = [])
    {
        return $this->service->loadUser($userId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadAnonymousUser()
    {
        return $this->service->loadAnonymousUser();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = [])
    {
        return $this->service->loadUserByCredentials($login, $password, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByLogin($login, array $prioritizedLanguages = [])
    {
        return $this->service->loadUserByLogin($login, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUsersByEmail($email, array $prioritizedLanguages = [])
    {
        return $this->service->loadUsersByEmail($email, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByToken($hash, array $prioritizedLanguages = [])
    {
        return $this->service->loadUserByToken($hash, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUser(User $user)
    {
        return $this->service->deleteUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct)
    {
        return $this->service->updateUser($user, $userUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        return $this->service->updateUserToken($user, $userTokenUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function expireUserToken($hash)
    {
        return $this->service->expireUserToken($hash);
    }

    /**
     * {@inheritdoc}
     */
    public function assignUserToUserGroup(User $user, UserGroup $userGroup)
    {
        return $this->service->assignUserToUserGroup($user, $userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup)
    {
        return $this->service->unAssignUserFromUserGroup($user, $userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserGroupsOfUser(User $user, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        return $this->service->loadUserGroupsOfUser($user, $offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUsersOfUserGroup(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        return $this->service->loadUsersOfUserGroup($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function isUser(Content $content): bool
    {
        return $this->service->isUser($content);
    }

    /**
     * {@inheritdoc}
     */
    public function isUserGroup(Content $content): bool
    {
        return $this->service->isUserGroup($content);
    }

    /**
     * {@inheritdoc}
     */
    public function newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType = null)
    {
        return $this->service->newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function newUserGroupCreateStruct($mainLanguageCode, $contentType = null)
    {
        return $this->service->newUserGroupCreateStruct($mainLanguageCode, $contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function newUserUpdateStruct()
    {
        return $this->service->newUserUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newUserGroupUpdateStruct()
    {
        return $this->service->newUserGroupUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function validatePassword(string $password, PasswordValidationContext $context = null): array
    {
        return $this->service->validatePassword($password, $context);
    }
}
