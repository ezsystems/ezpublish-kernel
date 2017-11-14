<?php

/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\UserService as APIUserService;
use eZ\Publish\Core\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\UserService;
use eZ\Publish\Core\SignalSlot\Signal\UserService as UserServiceSignals;

class UserServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIUserService::class);
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
            array(
                'id' => 42,
                'identifier' => 'rabbit',
                'fieldDefinitions' => array(),
            )
        );
        $groupContentType = new ContentType(
            array(
                'id' => 43,
                'identifier' => 'characters',
                'fieldDefinitions' => array(),
            )
        );

        $userCreateStruct = new UserCreateStruct(
            array(
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login' => $login,
                'password' => $password,
                'enabled' => true,
                'fields' => array(),
            )
        );
        $userUpdateStruct = new UserUpdateStruct();
        $userGroupCreateStruct = new UserGroupCreateStruct(
            array(
                'mainLanguageCode' => $mainLanguageCode,
                'contentType' => $contentType,
            )
        );
        $userGroupUpdateStruct = new UserGroupUpdateStruct();

        return array(
            array(
                'createUserGroup',
                array($userGroupCreateStruct, $parentGroup),
                $userGroup,
                1,
                UserServiceSignals\CreateUserGroupSignal::class,
                array('userGroupId' => $userGroupId),
            ),
            array(
                'loadUserGroup',
                array($userGroupId, array()),
                $userGroup,
                0,
            ),
            array(
                'loadUserGroup',
                array($userGroupId, array('eng-GB', 'eng-US')),
                $userGroup,
                0,
            ),
            array(
                'loadSubUserGroups',
                array($parentGroup, 1, 1, array()),
                array($userGroup),
                0,
            ),
            array(
                'loadSubUserGroups',
                array($parentGroup, 1, 1, array('eng-GB', 'eng-US')),
                array($userGroup),
                0,
            ),
            array(
                'deleteUserGroup',
                array($userGroup),
                null,
                1,
                UserServiceSignals\DeleteUserGroupSignal::class,
                array('userGroupId' => $userGroupId),
            ),
            array(
                'moveUserGroup',
                array($userGroup, $parentGroup2),
                null,
                1,
                UserServiceSignals\MoveUserGroupSignal::class,
                array(
                    'userGroupId' => $userGroupId,
                    'newParentId' => $parentGroup2Id,
                ),
            ),
            array(
                'updateUserGroup',
                array($userGroup, $userGroupUpdateStruct),
                $userGroup,
                1,
                UserServiceSignals\UpdateUserGroupSignal::class,
                array('userGroupId' => $userGroupId),
            ),
            array(
                'createUser',
                array($userCreateStruct, array($userGroup)),
                $user,
                1,
                UserServiceSignals\CreateUserSignal::class,
                array('userId' => $userId),
            ),
            array(
                'loadUser',
                array($userId, array()),
                $user,
                0,
            ),
            array(
                'loadUser',
                array($userId, array('eng-GB', 'eng-US')),
                $user,
                0,
            ),
            array(
                'loadAnonymousUser',
                array(),
                $user,
                0,
            ),
            array(
                'loadUserByCredentials',
                array('admin', 'with great power comes great responsibility', array()),
                $user,
                0,
            ),
            array(
                'loadUserByCredentials',
                array('admin', 'with great power comes great responsibility', array('eng-GB', 'eng-US')),
                $user,
                0,
            ),
            array(
                'loadUserByLogin',
                array('admin', array()),
                $user,
                0,
            ),
            array(
                'loadUserByLogin',
                array('admin', array('eng-GB', 'eng-US')),
                $user,
                0,
            ),
            array(
                'loadUsersByEmail',
                array('admin@ez.no', array()),
                array($user),
                0,
            ),
            array(
                'loadUsersByEmail',
                array('admin@ez.no', array('eng-GB', 'eng-US')),
                array($user),
                0,
            ),
            array(
                'deleteUser',
                array($user),
                null,
                1,
                UserServiceSignals\DeleteUserSignal::class,
                array('userId' => $userId),
            ),
            array(
                'updateUser',
                array($user, $userUpdateStruct),
                null,
                1,
                UserServiceSignals\UpdateUserSignal::class,
                array('userId' => $userId),
            ),
            array(
                'assignUserToUserGroup',
                array($user, $parentGroup2),
                null,
                1,
                UserServiceSignals\AssignUserToUserGroupSignal::class,
                array(
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id,
                ),
            ),
            array(
                'unassignUserFromUserGroup',
                array($user, $parentGroup2),
                null,
                1,
                UserServiceSignals\UnAssignUserFromUserGroupSignal::class,
                array(
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id,
                ),
            ),
            array(
                'loadUserGroupsOfUser',
                array($user, 1, 1, array()),
                array($userGroup),
                0,
            ),
            array(
                'loadUserGroupsOfUser',
                array($user, 1, 1, array('eng-GB', 'eng-US')),
                array($userGroup),
                0,
            ),
            array(
                'loadUsersOfUserGroup',
                array($userGroup, 1, 1, array()),
                array($user),
                0,
            ),
            array(
                'loadUsersOfUserGroup',
                array($userGroup, 1, 1, array('eng-GB', 'eng-US')),
                array($user),
                0,
            ),
            array(
                'newUserCreateStruct',
                array(
                    $login, $email, $password, $mainLanguageCode, $contentType,
                ),
                $userCreateStruct,
                0,
            ),
            array(
                'newUserGroupCreateStruct',
                array($mainLanguageCode, $groupContentType),
                $userGroupCreateStruct,
                0,
            ),
            array(
                'newUserUpdateStruct',
                array(),
                $userUpdateStruct,
                0,
            ),
            array(
                'newUserGroupUpdateStruct',
                array(),
                $userGroupUpdateStruct,
                0,
            ),
        );
    }
}
