<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\UserService as APIService;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\UserService;
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

        $userCreateStruct = new UserCreateStruct();
        $userUpdateStruct = new UserUpdateStruct();
        $user = new User();

        // string $method, array $arguments, bool $return = true
        return [
            ['createUserGroup', [$userGroupCreateStruct, $userGroup]],
            ['deleteUserGroup', [$userGroup]],
            ['moveUserGroup', [$userGroup, $userGroup]],
            ['updateUserGroup', [$userGroup, $userGroupUpdateStruct]],

            ['createUser', [$userCreateStruct, [$userGroup]]],
            ['loadAnonymousUser', []],
            ['deleteUser', [$user]],
            ['updateUser', [$user, $userUpdateStruct]],

            ['assignUserToUserGroup', [$user, $userGroup]],
            ['unAssignUserFromUserGroup', [$user, $userGroup]],

            ['newUserCreateStruct', ['adam', 'adam@gmail.com', 'Eve', 'eng-AU', 4]],
            ['newUserGroupCreateStruct', ['eng-AU', 7]],
            ['newUserUpdateStruct', []],
            ['newUserGroupUpdateStruct', []],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $userGroup = new UserGroup();
        $user = new User();

        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [
            ['loadUserGroup', [4, self::LANG_ARG], true, 1],
            ['loadSubUserGroups', [$userGroup, 50, 50, self::LANG_ARG], true, 3],
            ['loadUser', [14, self::LANG_ARG], true, 1],
            ['loadUserByCredentials', ['admin', 'Best passPhrase EvA!!', self::LANG_ARG], true, 2],
            ['loadUserByLogin', ['admin', self::LANG_ARG], true, 1],
            ['loadUsersByEmail', ['nospam@ez.no', self::LANG_ARG], true, 1],
            ['loadUserGroupsOfUser', [$user, 50, 50, self::LANG_ARG], true, 3],
            ['loadUsersOfUserGroup', [$userGroup, 50, 50, self::LANG_ARG], true, 3],
        ];
    }
}
