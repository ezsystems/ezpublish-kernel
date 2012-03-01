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

use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\User\User;

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
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserGroupCreateStructWithSecondParameter()
    {
        if ( $this->isVersion4() )
        {
            $this->markTestSkipped( 'This test is only relevant for eZ Publish versions > 4' );
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $userService        = $repository->getUserService();

        // Load the default ContentType for user groups
        $groupType = $contentTypeService->loadContentTypeByIdentifier( 'user_group' );

        // Instantiate a new group create struct
        $groupCreate = $userService->newUserGroupCreateStruct(
            'eng-US',
            $groupType
        );
        /* END: Use Case */

        $this->assertSame( $groupType, $groupCreate->contentType );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateUserGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $parentUserGroup = $userService->loadUserGroup( $this->fixtureUserGroupUsers );

        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Example Group' );

        $userGroup = $userService->createUserGroup( $userGroupCreate, $parentUserGroup );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroup',
            $userGroup
        );

        return $userGroup;
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupSetsExpectedProperties( $userGroup )
    {
        $this->assertEquals(
            array(
                'parentId'       =>  4,
                'subGroupCount'  =>  0
            ),
            array(
                'parentId'       =>  $userGroup->parentId,
                'subGroupCount'  =>  $userGroup->subGroupCount
            )
        );
    }


    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupIncrementsParentSubGroupCount()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // ID of the main "Users" group
        $mainGroupId = 4;

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Example Group' );

        // Create the new user group
        $userService->createUserGroup( $userGroupCreate, $parentUserGroup );

        // This should be one greater than before
        $subGroupCount = $userService->loadUserGroup( $mainGroupId )->subGroupCount;
        /* END: Use Case */

        $this->assertEquals( $parentUserGroup->subGroupCount + 1, $subGroupCount );
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsContentValidationException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // ID of the main "Users" group
        $mainGroupId = 4;

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );

        // This call will fail with a "ContentValidationException", because the
        // only mandatory field "name" is not set.
        $userService->createUserGroup( $userGroupCreate, $parentUserGroup );
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
     * Test for the newUserCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testNewUserCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserCreateStruct',
            $userCreate
        );

        return $userCreate;
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreate
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserCreateStruct
     */
    public function testNewUserCreateStructSetsExpectedProperties( $userCreate )
    {
        $this->assertEquals(
            array(
                'login'             =>  'user',
                'email'             =>  'user@example.com',
                'password'          =>  'secret',
                'mainLanguageCode'  =>  'eng-US',
            ),
            array(
                'login'             =>  $userCreate->login,
                'email'             =>  $userCreate->email,
                'password'          =>  $userCreate->password,
                'mainLanguageCode'  =>  $userCreate->mainLanguageCode,
            )
        );
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType)
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserCreateStructWithFifthParameter()
    {
        if ( $this->isVersion4() )
        {
            $this->markTestSkipped( 'This test is only relevant for eZ Publish versions > 4' );
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $userService        = $repository->getUserService();

        $userType = $contentTypeService->loadContentTypeByIdentifier( 'user' );

        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US',
            $userType
        );
        /* END: Use Case */

        $this->assertSame( $userType, $userCreate->contentType );
    }

    /**
     * Test for the createUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateUser()
    {
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\User',
            $user
        );

        return $user;
    }

    /**
     * Test for the createUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserSetsExpectedProperties( User $user )
    {
        $this->assertEquals(
            array(
                'login'             =>  'user',
                'email'             =>  'user@example.com',
                'passwordHash'      =>  $this->createHash(
                    'user',
                    'secret',
                    $user->hashAlgorithm
                ),
                'mainLanguageCode'  =>  'eng-US'
            ),
            array(
                'login'             =>  $user->login,
                'email'             =>  $user->email,
                'passwordHash'      =>  $user->passwordHash,
                'mainLanguageCode'  =>  $user->contentInfo->mainLanguageCode
            )
        );
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsContentValidationExceptionForMissingField()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
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

        // Do not set the mandatory fields "first_name" and "last_name"
        //$userCreate->setField( 'first_name', 'Example' );
        //$userCreate->setField( 'last_name', 'User' );

        // Load parent group for the user
        $group = $userService->loadUserGroup( $editorsGroupId );

        // This call will fail with a "ContentValidationException", because the
        // mandatory fields "first_name" and "last_name" are not set.
        $userService->createUser( $userCreate, array( $group ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUser()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the newly created user
        $userReloaded = $userService->loadUser( $user->id );
        /* END: Use Case */

        $this->assertEquals( $user, $userReloaded );
    }

    /**
     * Test for the loadUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testLoadUserThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a "NotFoundException", because no user with
        // an id equal to PHP_INT_MAX should exist.
        $userService->loadUser( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadAnonymousUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadAnonymousUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testLoadAnonymousUser()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // Load default anonymous user available in each eZ Publish installation
        $anonymousUser = $userService->loadAnonymousUser();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\User',
            $anonymousUser
        );

        $this->assertEquals( 'anonymous', $anonymousUser->login );
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUserByCredentials()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the newly created user
        $userReloaded = $userService->loadUserByCredentials( 'user', 'secret' );
        /* END: Use Case */

        $this->assertEquals( $user, $userReloaded );
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByCredentials
     */
    public function testLoadUserByCredentialsThrowsNotFoundExceptionForUnknownPassword()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will fail with a "NotFoundException", because the given
        // login/password combination does not exist.
        $userService->loadUserByCredentials( 'user', 'SeCrEt' );
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByCredentials
     */
    public function testLoadUserByCredentialsThrowsNotFoundExceptionForUnknownLogin()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will fail with a "NotFoundException", because the given
        // login/password combination does not exist.
        $userService->loadUserByCredentials( 'USER', 'secret' );
        /* END: Use Case */
    }

    /**
     * Test for the deleteUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testDeleteUser()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Delete the currently created user
        $userService->deleteUser( $user );
        /* END: Use Case */

        // We use the NotFoundException here to verify that the user not exists
        $userService->loadUser( $user->id );
    }

    /**
     * Test for the newUserUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testNewUserUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserUpdateStruct',
            $userUpdate
        );

        // TODO: Are the properties $contentMetaDataUpdateStruct and
        // $contentUpdateStruct pre initialized or not?
    }

    /**
     * Test for the updateUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateUser()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set new values for password and maxLogin
        $userUpdate->password  = 'my-new-password';
        $userUpdate->maxLogin  = 42;
        $userUpdate->isEnabled = false;

        // Updated the user record.
        $userVersion2 = $userService->updateUser( $user, $userUpdate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\User',
            $user
        );

        return $userVersion2;
    }

    /**
     * Test for the updateUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserUpdatesExpectedProperties( User $user )
    {
        $this->assertEquals(
            array(
                'login'         =>  'user',
                'email'         =>  'user@example.com',
                'passwordHash'  =>  $this->createHash(
                    'user',
                    'my-new-password',
                    $user->hashAlgorithm
                ),
                'maxLogin'      =>  42,
                'isEnabled'     =>  false
            ),
            array(
                'login'         =>  $user->login,
                'email'         =>  $user->email,
                'passwordHash'  =>  $user->passwordHash,
                'maxLogin'      =>  $user->maxLogin,
                'isEnabled'     =>  $user->isEnabled
            )
        );
    }

    /**
     * Test for the updateUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserIncrementsVersionNumber( $user )
    {
        $this->assertEquals( 2, $user->getVersionInfo()->versionNo );
    }

    /**
     * Test for the updateUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserReturnsPublishedVersion( $user )
    {
        $this->assertEquals(
            VersionInfo::STATUS_PUBLISHED,
            $user->getVersionInfo()->status
        );
    }

    /**
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserWithContentMetadataUpdateStruct()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get the ContentService implementation
        $contentService = $repository->getContentService();

        // Create a metadata update struct and change the remote id.
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->remoteId = '85e10037d1ac0a00aa75443ced483e08';

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the metadata update struct.
        $userUpdate->contentMetaDataUpdateStruct = $metadataUpdate;

        // Updated the user record.
        $userVersion2 = $userService->updateUser( $user, $userUpdate );

        // The contentInfo->remoteId will be changed now.
        $remoteId = $userVersion2->contentInfo->remoteId;
        /* END: Use Case */

        $this->assertEquals( '85e10037d1ac0a00aa75443ced483e08', $remoteId );
    }

    /**
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserWithContentUpdateStruct()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get the ContentService implementation
        $contentService = $repository->getContentService();

        // Create a content update struct and change the remote id.
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'first_name', 'Hello', 'eng-US' );
        $contentUpdate->setField( 'last_name', 'World', 'eng-US' );

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // Updated the user record.
        $userVersion2 = $userService->updateUser( $user, $userUpdate );

        $name = sprintf(
            '%s %s',
            $userVersion2->getFieldValue( 'first_name' ),
            $userVersion2->getFieldValue( 'last_name' )
        );
        /* END: Use Case */

        $this->assertEquals( 'Hello World', $name );
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUnAssignUserFromUserGroupThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for UserService::unAssignUssrFromUserGroup() is not implemented." );
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

    private function createHash( $login, $password, $type )
    {
        switch ( $type )
        {
            case 2:
                /* PASSWORD_HASH_MD5_USER */
                return md5( "{$login}\n{$password}" );

            case 3:
                /* PASSWORD_HASH_MD5_SITE */
                $site = null;
                return md5( "{$login}\n{$password}\n{$site}" );

            case 5:
                /* PASSWORD_HASH_PLAINTEXT */
                return $password;
        }
        /* PASSWORD_HASH_MD5_PASSWORD (1) */
        return md5( $password );
    }
}
