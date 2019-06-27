<?php /** @noinspection OverridingDeprecatedMethodInspection */

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
    /** @var \eZ\Publish\API\Repository\UserService */
    protected $innerService;

    public function __construct(UserService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createUserGroup(
        UserGroupCreateStruct $userGroupCreateStruct,
        UserGroup $parentGroup
    ) {
        return $this->innerService->createUserGroup($userGroupCreateStruct, $parentGroup);
    }

    public function loadUserGroup(
        $id,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUserGroup($id, $prioritizedLanguages);
    }

    public function loadSubUserGroups(
        UserGroup $userGroup,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadSubUserGroups($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function deleteUserGroup(UserGroup $userGroup)
    {
        return $this->innerService->deleteUserGroup($userGroup);
    }

    public function moveUserGroup(
        UserGroup $userGroup,
        UserGroup $newParent
    ) {
        return $this->innerService->moveUserGroup($userGroup, $newParent);
    }

    public function updateUserGroup(
        UserGroup $userGroup,
        UserGroupUpdateStruct $userGroupUpdateStruct
    ) {
        return $this->innerService->updateUserGroup($userGroup, $userGroupUpdateStruct);
    }

    public function createUser(
        UserCreateStruct $userCreateStruct,
        array $parentGroups
    ) {
        return $this->innerService->createUser($userCreateStruct, $parentGroups);
    }

    public function loadUser(
        $userId,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUser($userId, $prioritizedLanguages);
    }

    public function loadAnonymousUser()
    {
        return $this->innerService->loadAnonymousUser();
    }

    public function loadUserByCredentials(
        $login,
        $password,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUserByCredentials($login, $password, $prioritizedLanguages);
    }

    public function loadUserByLogin(
        $login,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUserByLogin($login, $prioritizedLanguages);
    }

    public function loadUsersByEmail(
        $email,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUsersByEmail($email, $prioritizedLanguages);
    }

    public function loadUserByToken(
        $hash,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUserByToken($hash, $prioritizedLanguages);
    }

    public function deleteUser(User $user)
    {
        return $this->innerService->deleteUser($user);
    }

    public function updateUser(
        User $user,
        UserUpdateStruct $userUpdateStruct
    ) {
        return $this->innerService->updateUser($user, $userUpdateStruct);
    }

    public function updateUserToken(
        User $user,
        UserTokenUpdateStruct $userTokenUpdateStruct
    ) {
        return $this->innerService->updateUserToken($user, $userTokenUpdateStruct);
    }

    public function expireUserToken($hash)
    {
        return $this->innerService->expireUserToken($hash);
    }

    public function assignUserToUserGroup(
        User $user,
        UserGroup $userGroup
    ) {
        return $this->innerService->assignUserToUserGroup($user, $userGroup);
    }

    public function unAssignUserFromUserGroup(
        User $user,
        UserGroup $userGroup
    ) {
        return $this->innerService->unAssignUserFromUserGroup($user, $userGroup);
    }

    public function loadUserGroupsOfUser(
        User $user,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUserGroupsOfUser($user, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUsersOfUserGroup(
        UserGroup $userGroup,
        $offset = 0,
        $limit = 25,
        array $prioritizedLanguages = []
    ) {
        return $this->innerService->loadUsersOfUserGroup($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function isUser(Content $content): bool
    {
        return $this->innerService->isUser($content);
    }

    public function isUserGroup(Content $content): bool
    {
        return $this->innerService->isUserGroup($content);
    }

    public function newUserCreateStruct(
        $login,
        $email,
        $password,
        $mainLanguageCode,
        $contentType = null
    ) {
        return $this->innerService->newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType);
    }

    public function newUserGroupCreateStruct(
        $mainLanguageCode,
        $contentType = null
    ) {
        return $this->innerService->newUserGroupCreateStruct($mainLanguageCode, $contentType);
    }

    public function newUserUpdateStruct()
    {
        return $this->innerService->newUserUpdateStruct();
    }

    public function newUserGroupUpdateStruct()
    {
        return $this->innerService->newUserGroupUpdateStruct();
    }

    public function validatePassword(
        string $password,
        PasswordValidationContext $context = null
    ): array {
        return $this->innerService->validatePassword($password, $context);
    }
}
