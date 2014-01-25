<?php
/**
 * File containing the UserTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentType;

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

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new UserService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $userGroupId = 12;
        $parentGroup2Id = 13;
        $userId = 14;

        $parentGroup = $this->getUserGroup( 11, md5( 'parent' ), 1 );
        $userGroup = $this->getUserGroup( $userGroupId, md5( 'user group' ), 2 );
        $parentGroup2 = $this->getUserGroup( $parentGroup2Id, md5( 'parent2' ), 1 );

        $user = $this->getUser( $userId, md5( "I'm the boss" ), 4 );
        $anonymous = $this->getUser( 10, md5( "invisible man" ), 1 );
        $login = 'bugsbunny';
        $password = "what's up doc?";
        $email = "bugs@warnerbros.com";
        $mainLanguageCode = 'eng-US';
        $contentType = new ContentType(
            array(
                'id' => 42,
                'identifier' => 'rabbit',
                'fieldDefinitions' => array()
            )
        );
        $groupContentType = new ContentType(
            array(
                'id' => 43,
                'identifier' => 'characters',
                'fieldDefinitions' => array()
            )
        );

        $userCreateStruct = new UserCreateStruct(
            array(
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'login' => $login,
                'password' => $password,
                'enabled' => true,
                'fields' => array()
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
                array( $userGroupCreateStruct, $parentGroup ),
                $userGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserGroupSignal',
                array( 'userGroupId' => $userGroupId )
            ),
            array(
                'loadUserGroup',
                array( $userGroupId ),
                $userGroup,
                0
            ),
            array(
                'loadSubUserGroups',
                array( $parentGroup ),
                array( $userGroup ),
                0
            ),
            array(
                'deleteUserGroup',
                array( $userGroup ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserGroupSignal',
                array( 'userGroupId' => $userGroupId )
            ),
            array(
                'moveUserGroup',
                array( $userGroup, $parentGroup2 ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\MoveUserGroupSignal',
                array(
                    'userGroupId' => $userGroupId,
                    'newParentId' => $parentGroup2Id
                )
            ),
            array(
                'updateUserGroup',
                array( $userGroup, $userGroupUpdateStruct ),
                $userGroup,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserGroupSignal',
                array( 'userGroupId' => $userGroupId )
            ),
            array(
                'createUser',
                array( $userCreateStruct, array( $userGroup ) ),
                $user,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\CreateUserSignal',
                array( 'userId' => $userId )
            ),
            array(
                'loadUser',
                array( $userId ),
                $user,
                0
            ),
            array(
                'loadAnonymousUser',
                array(),
                $user,
                0
            ),
            array(
                'loadUserByCredentials',
                array( "admin", "with great power comes great responsibility" ),
                $user,
                0
            ),
            array(
                'loadUserByLogin',
                array( "admin" ),
                $user,
                0
            ),
            array(
                'loadUsersByEmail',
                array( "admin@ez.no" ),
                array( $user ),
                0
            ),
            array(
                'deleteUser',
                array( $user ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\DeleteUserSignal',
                array( 'userId' => $userId )
            ),
            array(
                'updateUser',
                array( $user, $userUpdateStruct ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UpdateUserSignal',
                array( 'userId' => $userId )
            ),
            array(
                'assignUserToUserGroup',
                array( $user, $parentGroup2 ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\AssignUserToUserGroupSignal',
                array(
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id
                )
            ),
            array(
                'unassignUserFromUserGroup',
                array( $user, $parentGroup2 ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\UserService\UnAssignUserFromUserGroupSignal',
                array(
                    'userId' => $userId,
                    'userGroupId' => $parentGroup2Id
                )
            ),
            array(
                'loadUserGroupsOfUser',
                array( $user ),
                array( $userGroup ),
                0
            ),
            array(
                'loadUsersOfUserGroup',
                array( $userGroup, 1, 1 ),
                array( $user ),
                0
            ),
            array(
                'newUserCreateStruct',
                array(
                    $login, $email, $password, $mainLanguageCode, $contentType
                ),
                $userCreateStruct,
                0
            ),
            array(
                'newUserGroupCreateStruct',
                array( $mainLanguageCode, $groupContentType ),
                $userGroupCreateStruct,
                0
            ),
            array(
                'newUserUpdateStruct',
                array(),
                $userUpdateStruct,
                0
            ),
            array(
                'newUserGroupUpdateStruct',
                array(),
                $userGroupUpdateStruct,
                0
            ),
        );
    }
}
