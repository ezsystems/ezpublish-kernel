<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

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
    /** @var eZ\Publish\API\Repository\UserService */
    protected $innerService;

    /**
     * @param eZ\Publish\API\Repository\UserService
     */
    public function __construct(UserService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup)
    {
        $this->innerService->createUserGroup($userGroupCreateStruct, $parentGroup);
    }

    public function loadUserGroup($id, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUserGroup($id, $prioritizedLanguages);
    }

    public function loadSubUserGroups(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        $this->innerService->loadSubUserGroups($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function deleteUserGroup(UserGroup $userGroup)
    {
        $this->innerService->deleteUserGroup($userGroup);
    }

    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent)
    {
        $this->innerService->moveUserGroup($userGroup, $newParent);
    }

    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        $this->innerService->updateUserGroup($userGroup, $userGroupUpdateStruct);
    }

    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        $this->innerService->createUser($userCreateStruct, $parentGroups);
    }

    public function loadUser($userId, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUser($userId, $prioritizedLanguages);
    }

    public function loadAnonymousUser()
    {
        $this->innerService->loadAnonymousUser();
    }

    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUserByCredentials($login, $password, $prioritizedLanguages);
    }

    public function loadUserByLogin($login, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUserByLogin($login, $prioritizedLanguages);
    }

    public function loadUsersByEmail($email, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUsersByEmail($email, $prioritizedLanguages);
    }

    public function loadUserByToken($hash, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUserByToken($hash, $prioritizedLanguages);
    }

    public function deleteUser(User $user)
    {
        $this->innerService->deleteUser($user);
    }

    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct)
    {
        $this->innerService->updateUser($user, $userUpdateStruct);
    }

    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $this->innerService->updateUserToken($user, $userTokenUpdateStruct);
    }

    public function expireUserToken($hash)
    {
        $this->innerService->expireUserToken($hash);
    }

    public function assignUserToUserGroup(User $user, UserGroup $userGroup)
    {
        $this->innerService->assignUserToUserGroup($user, $userGroup);
    }

    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup)
    {
        $this->innerService->unAssignUserFromUserGroup($user, $userGroup);
    }

    public function loadUserGroupsOfUser(User $user, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUserGroupsOfUser($user, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUsersOfUserGroup(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = [])
    {
        $this->innerService->loadUsersOfUserGroup($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function isUser(Content $content): bool
    {
        return $this->innerService->isUser($content);
    }

    public function isUserGroup(Content $content): bool
    {
        return $this->innerService->isUserGroup($content);
    }

    public function newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType = null)
    {
        $this->innerService->newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType);
    }

    public function newUserGroupCreateStruct($mainLanguageCode, $contentType = null)
    {
        $this->innerService->newUserGroupCreateStruct($mainLanguageCode, $contentType);
    }

    public function newUserUpdateStruct()
    {
        $this->innerService->newUserUpdateStruct();
    }

    public function newUserGroupUpdateStruct()
    {
        $this->innerService->newUserGroupUpdateStruct();
    }

    public function validatePassword(string $password, PasswordValidationContext $context = null): array
    {
        return $this->innerService->validatePassword($password, $context);
    }
}
