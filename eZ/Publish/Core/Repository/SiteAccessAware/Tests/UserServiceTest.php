<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use DateInterval;
use DateTime;
use eZ\Publish\API\Repository\UserService as APIService;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\API\Repository\Values\User\PasswordValidationContext;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserTokenUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\UserService;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct;

class UserServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return UserService::class;
    }

    public function providerForPassTroughMethods()
    {
        $userGroupCreateStruct = new UserGroupCreateStruct();
        $userGroupUpdateStruct = new UserGroupUpdateStruct();
        $userGroup = new UserGroup();
        $userGroupId = 1;

        $userCreateStruct = new UserCreateStruct();
        $userUpdateStruct = new UserUpdateStruct();
        $userTokenUpdateStruct = new UserTokenUpdateStruct();
        $user = new User();
        $userId = 14;
        $contentType = $this->createMock(ContentType::class);

        $passwordValidationContext = new PasswordValidationContext();
        $passwordExpirationDate = (new DateTime())->add(new DateInterval('P30D'));
        $passwordExpirationWarningDate = (new DateTime())->add(new DateInterval('P16D'));

        // string $method, array $arguments, bool $return = true
        return [
            ['createUserGroup', [$userGroupCreateStruct, $userGroup], $userGroup],
            ['deleteUserGroup', [$userGroup], [$userGroupId]],
            ['moveUserGroup', [$userGroup, $userGroup], null],
            ['updateUserGroup', [$userGroup, $userGroupUpdateStruct], $userGroup],

            ['createUser', [$userCreateStruct, [$userGroup]], $user],
            ['deleteUser', [$user], [$userId]],
            ['updateUser', [$user, $userUpdateStruct], $user],
            ['updateUserPassword', [$user, 'H@xi0r!'], $user],

            ['assignUserToUserGroup', [$user, $userGroup], null],
            ['unAssignUserFromUserGroup', [$user, $userGroup], null],

            ['updateUserToken', [$user, $userTokenUpdateStruct], $user],
            ['expireUserToken', ['43ir43jrt43'], null],

            ['newUserCreateStruct', ['adam', 'adam@gmail.com', 'Eve', 'eng-AU', $contentType], $userCreateStruct],
            ['newUserGroupCreateStruct', ['eng-AU', $contentType], $userGroupCreateStruct],
            ['newUserUpdateStruct', [], $userUpdateStruct],
            ['newUserGroupUpdateStruct', [], $userGroupUpdateStruct],

            ['isUser', [$userGroup]],
            ['isUserGroup', [$userGroup]],

            ['checkUserCredentials', [$user, 'H@xi0r!']],
            ['validatePassword', ['H@xi0r!', $passwordValidationContext], []],
            ['getPasswordInfo', [$user], new PasswordInfo()],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $userGroup = new UserGroup();
        $user = new User();

        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [
            ['loadUserGroup', [4, self::LANG_ARG], $userGroup, 1],
            ['loadSubUserGroups', [$userGroup, 50, 50, self::LANG_ARG], [$userGroup], 3],
            ['loadUser', [14, self::LANG_ARG], $user, 1],
            ['loadUserByLogin', ['admin', self::LANG_ARG], $user, 1],
            ['loadUserByEmail', ['nospam@ez.no', self::LANG_ARG], $user, 1],
            ['loadUsersByEmail', ['nospam@ez.no', self::LANG_ARG], [$user], 1],
            ['loadUserGroupsOfUser', [$user, 50, 50, self::LANG_ARG], [$userGroup], 3],
            ['loadUsersOfUserGroup', [$userGroup, 50, 50, self::LANG_ARG], [$user], 3],
            ['loadUserByToken', ['43ir43jrt43', self::LANG_ARG], $user, 1],
        ];
    }
}
