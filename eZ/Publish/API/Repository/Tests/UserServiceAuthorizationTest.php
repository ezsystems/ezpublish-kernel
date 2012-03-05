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
        $this->markTestIncomplete( "@TODO: Test for UserService::loadUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "@TODO: Test for UserService::loadSubUserGroups() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the deleteUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::deleteUserGroup() is not implemented." );
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testMoveUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::moveUserGroup() is not implemented." );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUserGroup() is not implemented." );
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUser() is not implemented." );
    }

    /**
     * Test for the deleteUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteUserThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::deleteUser() is not implemented." );
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
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUser() is not implemented." );
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignUserToUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::assignUserToUserGroup() is not implemented." );
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnAssignUserFromUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
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
    public function testLoadUsersOfUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $group = $userService->loadUserGroup( 13 );

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Now set the currently created "Editor" as current user
        $repository->setCurrentUser( $user );

        // This call will fail with an "UnauthorizedException"
        $userService->loadUsersOfUserGroup( $group );
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
        // ID of the main "Users" group
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

    /**
     * Create a user fixture in a variable named <b>$user</b>,
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    private function createUserVersion1()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // ID of the "Editors" user group in an eZ Publish demo installation
        $editorsGroupId = 13;

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;

        // Set some fields required by the user ContentType
        $userCreate->setField( 'first_name', 'Example' );
        $userCreate->setField( 'last_name', 'User' );

        // Load parent group for the user
        $group = $userService->loadUserGroup( $editorsGroupId );

        // Create a new user instance.
        $user = $userService->createUser( $userCreate, array( $group ) );
        /* END: Inline */

        return $user;
    }
}
