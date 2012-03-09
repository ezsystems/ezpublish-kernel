<?php
/**
 * File containing the UserServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the UserService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\UserService
 * @group integration
 */
class UserServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testLoadUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->loadUserGroup( $userGroup->id );
        /* END: Use Case */
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadSubUserGroups
     */
    public function testLoadSubUserGroupsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->loadSubUserGroups( $userGroup );
        /* END: Use Case */
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the parent group
        $parentUserGroup = $userService->loadUserGroup( $editorsGroupId );

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // Instantiate a new group create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $userGroupCreate->setField( 'name', 'Example Group' );

        // This call will fail with an "UnauthorizedException"
        $userService->createUserGroup( $userGroupCreate, $parentUserGroup );
        /* END: Use Case */
    }

    /**
     * Test for the deleteUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testDeleteUserGroup
     */
    public function testDeleteUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->deleteUserGroup( $userGroup );
        /* END: Use Case */
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testMoveUserGroup
     */
    public function testMoveUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // ID of the "Members" group in an eZ Publish demo installation
        $memberGroupId = 11;

        // Load new parent user group
        $newParentUserGroup = $userService->loadUserGroup( $memberGroupId );

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->moveUserGroup( $userGroup, $newParentUserGroup );
        /* END: Use Case */
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUserGroup
     */
    public function testUpdateUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // Load content service
        $contentService = $repository->getContentService();

        // Instantiate a content update struct
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'name', 'New group name' );

        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $contentUpdateStruct;

        // This call will fail with an "UnauthorizedException"
        $userService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // Instantiate a user create struct
        $userCreateStruct = $userService->newUserCreateStruct(
            'test',
            'test@example.com',
            'password',
            'eng-US'
        );

        $parentUserGroup = $userService->loadUserGroup( $editorsGroupId );

        // This call will fail with an "UnauthorizedException"
        $userService->createUser(
            $userCreateStruct,
            array( $parentUserGroup )
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testDeleteUser
     */
    public function testDeleteUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->deleteUser( $user );
        /* END: Use Case */
    }

    /**
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // Instantiate a user update struct
        $userUpdateStruct           = $userService->newUserUpdateStruct();
        $userUpdateStruct->maxLogin = 42;

        // This call will fail with an "UnauthorizedException"
        $userService->updateUser( $user, $userUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testAssignUserToUserGroup
     */
    public function testAssignUserToUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // ID of the "Administrator" group in an eZ Publish demo installation
        $administratorGroupId = 12;

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup( $administratorGroupId )
        );
        /* END: Use Case */
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUnAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = 13;

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // ID of the "Members" group in an eZ Publish demo installation
        $memberGroupId = 11;

        // Assign group to newly created user
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup( $memberGroupId )
        );

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup( $editorsGroupId )
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadUserGroupsOfUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroupsOfUser
     */
    public function testLoadUserGroupsOfUserThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->loadUserGroupsOfUser( $user );
        /* END: Use Case */
    }

    /**
     * Test for the loadUsersOfUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUsersOfUserGroup
     */
    public function testLoadUsersOfUserGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->createUserGroupVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->loadUsersOfUserGroup( $userGroup );
        /* END: Use Case */
    }

    /**
     * Create a user group fixture in a variable named <b>$userGroup</b>,
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private function createUserGroupVersion1()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // ID of the main "Users" group in an eZ Publish demo installation
        $mainGroupId = 4;

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Example Group' );

        // Create the new user group
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Inline */

        return $userGroup;
    }
}
