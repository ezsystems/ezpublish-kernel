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
    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testNewUserGroupCreateStruct()
    {
        $this->markTestIncomplete( "Test for UserService::newUserGroupCreateStruct() is not implemented." );
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct($mainLanguageCode, $contentType)
     * @eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for UserService::newUserGroupCreateStruct() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * 
     */
    public function testCreateUserGroup()
    {
        $this->markTestIncomplete( "Test for UserService::createUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateUserGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for UserService::createUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUserGroup() is not implemented." );
    }

    /**
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * 
     */
    public function testLoadUserGroup()
    {
        $this->markTestIncomplete( "Test for UserService::loadUserGroup() is not implemented." );
    }

    /**
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for UserService::loadUserGroup() is not implemented." );
    }

    /**
     * Test for the loadUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadUserGroupThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for UserService::loadUserGroup() is not implemented." );
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * 
     */
    public function testLoadSubUserGroups()
    {
        $this->markTestIncomplete( "Test for UserService::loadSubUserGroups() is not implemented." );
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadSubUserGroupsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for UserService::loadSubUserGroups() is not implemented." );
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSubUserGroupsThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for UserService::loadSubUserGroups() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::deleteUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::deleteUserGroup() is not implemented." );
    }

    /**
     * Test for the deleteUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteUserGroupThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for UserService::deleteUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::moveUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::moveUserGroup() is not implemented." );
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testMoveUserGroupThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for UserService::moveUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::createUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::loadUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::loadUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::loadUserByCredentials() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::loadUserByCredentials() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::deleteUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::deleteUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::updateUser() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::assignUserToUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::assignUserToUserGroup() is not implemented." );
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * 
     */
    public function testUnAssignUssrFromUserGroup()
    {
        $this->markTestIncomplete( "Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnAssignUssrFromUserGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUnAssignUssrFromUserGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::newUserCreateStruct() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::newUserCreateStruct() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::newUserUpdateStruct() is not implemented." );
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
        $this->markTestIncomplete( "Test for UserService::newUserGroupUpdateStruct() is not implemented." );
    }

}
