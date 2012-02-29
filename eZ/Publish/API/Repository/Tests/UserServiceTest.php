<?php
/**
 * File containing the UserServiceTest class
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
class UserServiceTest extends BaseTest
{
    protected $fixtureUserGroupUsers = 4;

    /**
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testLoadUserGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup( $this->fixtureUserGroupUsers );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $userGroup );
    }

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
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testLoadUserGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a NotFoundException
        $userService->loadUserGroup( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testLoadSubUserGroups()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup( $this->fixtureUserGroupUsers );

        $subUserGroups = $userService->loadSubUserGroups( $userGroup );
        foreach ( $subUserGroups as $subUserGroup )
        {
            // Do something with the $subUserGroup
        }
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $subUserGroup );
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
     * Test for the newUserGroupCreateStruct() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserGroupCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $groupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct',
            $groupCreate
        );

        return $groupCreate;
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $groupCreate
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructSetsMainLanguageCode( $groupCreate )
    {
        $this->assertEquals( 'eng-US', $groupCreate->mainLanguageCode );
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $groupCreate
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructSetsContentType( $groupCreate )
    {
        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentType',
            $groupCreate->contentType
        );
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct($mainLanguageCode, $contentType)
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::newUserGroupCreateStruct() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     *
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testCreateUserGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $parentUserGroup = $userService->loadUserGroup( $this->fixtureUserGroupUsers );

        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );

        $userGroup = $userService->createUserGroup( $userGroupCreate, $parentUserGroup );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $userGroup );
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
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateUserGroupThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateUserGroupThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateUserGroupThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the deleteUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUserGroup()
     * 
     */
    public function testDeleteUserGroup()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::deleteUserGroup() is not implemented." );
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
     * 
     */
    public function testMoveUserGroup()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::moveUserGroup() is not implemented." );
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
     * 
     */
    public function testUpdateUserGroup()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUserGroup() is not implemented." );
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
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateUserGroupThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUserGroup() is not implemented." );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateUserGroupThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUserGroup() is not implemented." );
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * 
     */
    public function testCreateUser()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUser() is not implemented." );
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
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testCreateUserThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUser() is not implemented." );
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateUserThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUser() is not implemented." );
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateUserThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::createUser() is not implemented." );
    }

    /**
     * Test for the loadUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUser()
     * 
     */
    public function testLoadUser()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::loadUser() is not implemented." );
    }

    /**
     * Test for the loadUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadUserThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::loadUser() is not implemented." );
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * 
     */
    public function testLoadUserByCredentials()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::loadUserByCredentials() is not implemented." );
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadUserByCredentialsThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::loadUserByCredentials() is not implemented." );
    }

    /**
     * Test for the deleteUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUser()
     * 
     */
    public function testDeleteUser()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::deleteUser() is not implemented." );
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
     * 
     */
    public function testUpdateUser()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUser() is not implemented." );
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
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateUserThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUser() is not implemented." );
    }

    /**
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateUserThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::updateUser() is not implemented." );
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * 
     */
    public function testAssignUserToUserGroup()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::assignUserToUserGroup() is not implemented." );
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
     * 
     */
    public function testUnAssignUserFromUserGroup()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
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
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUnAssignUserFromUserGroupThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct()
     * 
     */
    public function testNewUserCreateStruct()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::newUserCreateStruct() is not implemented." );
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType)
     * 
     */
    public function testNewUserCreateStructWithFifthParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::newUserCreateStruct() is not implemented." );
    }

    /**
     * Test for the newUserUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserUpdateStruct()
     * 
     */
    public function testNewUserUpdateStruct()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::newUserUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newUserGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupUpdateStruct()
     * 
     */
    public function testNewUserGroupUpdateStruct()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::newUserGroupUpdateStruct() is not implemented." );
    }

}
