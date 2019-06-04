<?php

/**
 * UserService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\LanguageResolver;

/**
 * UserService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class UserService implements UserServiceInterface
{
    /** @var \eZ\Publish\API\Repository\UserService */
    protected $service;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\UserService $service
     * @param \eZ\Publish\API\Repository\LanguageResolver $languageResolver
     */
    public function __construct(
        UserServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function createUserGroup(UserGroupCreateStruct $userGroupCreateStruct, UserGroup $parentGroup)
    {
        return $this->service->createUserGroup($userGroupCreateStruct, $parentGroup);
    }

    public function loadUserGroup($id, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserGroup($id, $prioritizedLanguages);
    }

    public function loadSubUserGroups(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadSubUserGroups($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function deleteUserGroup(UserGroup $userGroup)
    {
        return $this->service->deleteUserGroup($userGroup);
    }

    public function moveUserGroup(UserGroup $userGroup, UserGroup $newParent)
    {
        return $this->service->moveUserGroup($userGroup, $newParent);
    }

    public function updateUserGroup(UserGroup $userGroup, UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        return $this->service->updateUserGroup($userGroup, $userGroupUpdateStruct);
    }

    public function createUser(UserCreateStruct $userCreateStruct, array $parentGroups)
    {
        return $this->service->createUser($userCreateStruct, $parentGroups);
    }

    public function loadUser($userId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUser($userId, $prioritizedLanguages);
    }

    public function loadAnonymousUser()
    {
        return $this->service->loadAnonymousUser();
    }

    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserByCredentials($login, $password, $prioritizedLanguages);
    }

    public function loadUserByLogin($login, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserByLogin($login, $prioritizedLanguages);
    }

    public function loadUsersByEmail($email, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUsersByEmail($email, $prioritizedLanguages);
    }

    public function deleteUser(User $user)
    {
        return $this->service->deleteUser($user);
    }

    public function updateUser(User $user, UserUpdateStruct $userUpdateStruct)
    {
        return $this->service->updateUser($user, $userUpdateStruct);
    }

    public function assignUserToUserGroup(User $user, UserGroup $userGroup)
    {
        return $this->service->assignUserToUserGroup($user, $userGroup);
    }

    public function unAssignUserFromUserGroup(User $user, UserGroup $userGroup)
    {
        return $this->service->unAssignUserFromUserGroup($user, $userGroup);
    }

    public function loadUserGroupsOfUser(User $user, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserGroupsOfUser($user, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUsersOfUserGroup(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUsersOfUserGroup($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUserByToken($hash, array $prioritizedLanguages = null)
    {
        return $this->service->loadUserByToken(
            $hash,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }

    public function updateUserToken(User $user, UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        return $this->service->updateUserToken($user, $userTokenUpdateStruct);
    }

    public function expireUserToken($hash)
    {
        return $this->service->expireUserToken($hash);
    }

    public function isUser(Content $content): bool
    {
        return $this->service->isUser($content);
    }

    public function isUserGroup(Content $content): bool
    {
        return $this->service->isUserGroup($content);
    }

    public function newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType = null)
    {
        return $this->service->newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType);
    }

    public function newUserGroupCreateStruct($mainLanguageCode, $contentType = null)
    {
        return $this->service->newUserGroupCreateStruct($mainLanguageCode, $contentType);
    }

    public function newUserUpdateStruct()
    {
        return $this->service->newUserUpdateStruct();
    }

    public function newUserGroupUpdateStruct()
    {
        return $this->service->newUserGroupUpdateStruct();
    }

    public function validatePassword(string $password, PasswordValidationContext $context = null): array
    {
        return $this->service->validatePassword($password, $context);
    }
}
