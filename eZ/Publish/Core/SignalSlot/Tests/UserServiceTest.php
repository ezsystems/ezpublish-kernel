<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\UserService;

class UserServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\UserService'
        );
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new UserService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $userGroupId = 12;
        $parentGroup2Id = 13;
        $userId = 14;

        $parentGroup = $this->getUserGroup(11, md5('parent'), 1);
        $userGroup = $this->getUserGroup($userGroupId, md5('user group'), 2);
        $parentGroup2 = $this->getUserGroup($parentGroup2Id, md5('parent2'), 1);

        $user = $this->getUser($userId, md5("I'm the boss"), 4);
        $anonymous = $this->getUser(10, md5('invisible man'), 1);
        $login = 'bugsbunny';
        $password = "what's up doc?";
        $email = 'bugs@warnerbros.com';
        $mainLanguageCode = 'eng-US';
        $contentType = new ContentType(
            [
                'id' => 42,
                'identifier' => 'rabbit',
                'fieldDefinitions' => [],
            ]
        );
        $groupContentType = new ContentType(
            [
                'id' => 43,
                'identifier' => 'characters',
                'fieldDefinitions' => [],
            ]
        );

        $userCreateStruct = new UserCreateStruct(
            [
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login' => $login,
                'password' => $password,
                'enabled' => true,
                'fields' => [],
            ]
        );
        $userUpdateStruct = new UserUpdateStruct();
        $userGroupCreateStruct = new UserGroupCreateStruct(
            [
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
            ]
        );
        $userGroupUpdateStruct = new UserGroupUpdateStruct();

        return [
            [
                'createUserGroup',
                [$userGroupCreateStruct, $parentGroup],
                $userGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserGroupSignal',
                ['userGroupId' => $userGroupId],
            ],
            [
                'loadUserGroup',
                [$userGroupId],
                $userGroup,
                0,
            ],
            [
                'loadSubUserGroups',
                [$parentGroup, 1, 1],
                [$userGroup],
                0,
            ],
            [
                'deleteUserGroup',
                [$userGroup],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserGroupSignal',
                ['userGroupId' => $userGroupId],
            ],
            [
                'moveUserGroup',
                [$userGroup, $parentGroup2],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\MoveUserGroupSignal',
                [
                    'userGroupId' => $userGroupId,
                    'newParentId' => $parentGroup2Id,
                ],
            ],
            [
                'updateUserGroup',
                [$userGroup, $userGroupUpdateStruct],
                $userGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserGroupSignal',
                ['userGroupId' => $userGroupId],
            ],
            [
                'createUser',
                [$userCreateStruct, [$userGroup]],
                $user,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserSignal',
                ['userId' => $userId],
            ],
            [
                'loadUser',
                [$userId],
                $user,
                0,
            ],
            [
                'loadAnonymousUser',
                [],
                $user,
                0,
            ],
            [
                'loadUserByCredentials',
                ['admin', 'with great power comes great responsibility'],
                $user,
                0,
            ],
            [
                'loadUserByLogin',
                ['admin'],
                $user,
                0,
            ],
            [
                'loadUsersByEmail',
                ['admin@ez.no'],
                [$user],
                0,
            ],
            [
                'deleteUser',
                [$user],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserSignal',
                ['userId' => $userId],
            ],
            [
                'updateUser',
                [$user, $userUpdateStruct],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserSignal',
                ['userId' => $userId],
            ],
            [
                'assignUserToUserGroup',
                [$user, $parentGroup2],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal',
                [
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id,
                ],
            ],
            [
                'unassignUserFromUserGroup',
                [$user, $parentGroup2],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal',
                [
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id,
                ],
            ],
            [
                'loadUserGroupsOfUser',
                [$user, 1, 1],
                [$userGroup],
                0,
            ],
            [
                'loadUsersOfUserGroup',
                [$userGroup, 1, 1],
                [$user],
                0,
            ],
            [
                'newUserCreateStruct',
                [
                    $login, $email, $password, $mainLanguageCode, $contentType,
                ],
                $userCreateStruct,
                0,
            ],
            [
                'newUserGroupCreateStruct',
                [$mainLanguageCode, $groupContentType],
                $userGroupCreateStruct,
                0,
            ],
            [
                'newUserUpdateStruct',
                [],
                $userUpdateStruct,
                0,
            ],
            [
                'newUserGroupUpdateStruct',
                [],
                $userGroupUpdateStruct,
                0,
            ],
        ];
    }
}
