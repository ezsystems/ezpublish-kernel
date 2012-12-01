<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\UserBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,

    eZ\Publish\Core\Repository\Values\User\User,
    eZ\Publish\Core\Repository\Values\User\UserGroup,
    eZ\Publish\Core\Repository\Values\Content\Content,
    eZ\Publish\Core\Repository\Values\Content\VersionInfo,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,

    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound,
    eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for User Service
 */
abstract class UserBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\User\User::__construct
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__construct
     */
    public function testNewClass()
    {
        $user = new User();

        $this->assertPropertiesCorrect(
            array(
                'login' => null,
                'email' => null,
                'passwordHash' => null,
                'hashAlgorithm' => null,
                'maxLogin' => null,
                'enabled' => null
            ),
            $user
        );

        $group = new UserGroup();
        self::assertEquals( null, $group->parentId );
        self::assertEquals( null, $group->subGroupCount );

        $this->assertPropertiesCorrect(
            array(
                'parentId' => null,
                'subGroupCount' => null
            ),
            $group
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\User\User::__get
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $user = new User();
            $value = $user->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch( PropertyNotFound $e ) {}

        try
        {
            $userGroup = new UserGroup();
            $value = $userGroup->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch( PropertyNotFound $e ) {}
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\User\User::__set
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $user = new User();
            $user->login = 'user';
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyReadOnlyException $e ) {}

        try
        {
            $userGroup = new UserGroup();
            $userGroup->parentId = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyReadOnlyException $e ) {}
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\User\User::__isset
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__isset
     */
    public function testIsPropertySet()
    {
        $user = new User();
        $value = isset( $user->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $user->login );
        self::assertEquals( true, $value );

        $userGroup = new UserGroup();
        $value = isset( $userGroup->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $userGroup->parentId );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\User\User::__unset
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__unset
     */
    public function testUnsetProperty()
    {
        $user = new User( array( "login" => 'admin' ) );
        try
        {
            unset( $user->login );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e ) {}

        $userGroup = new UserGroup( array( "parentId" => 1 ) );
        try
        {
            unset( $userGroup->parentId );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e ) {}
    }

    /**
     * Test creating new user group
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroup()
    {
        $userService = $this->repository->getUserService();

        $parentGroup = $userService->loadUserGroup( 4 );
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( "eng-GB" );
        $userGroupCreateStruct->ownerId = 14;
        $userGroupCreateStruct->sectionId = 1;
        $userGroupCreateStruct->setField( "name", "New group" );
        $userGroupCreateStruct->setField( "description", "This is a new group" );

        $newGroup = $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );

        self::assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup", $newGroup );
    }

    /**
     * Test creating new user group throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroupThrowsContentValidationException()
    {
        $userService = $this->repository->getUserService();

        $parentGroup = $userService->loadUserGroup( 4 );
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( "eng-GB" );
        $userGroupCreateStruct->ownerId = 14;
        $userGroupCreateStruct->sectionId = 1;
        $userGroupCreateStruct->setField( "name", "" );

        $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );
    }

    /**
     * Test creating new user group throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroupThrowsContentValidationExceptionVariation()
    {
        $userService = $this->repository->getUserService();

        $parentGroup = $userService->loadUserGroup( 4 );
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( "eng-GB" );

        $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );
    }

    /**
     * Test loading a group
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     */
    public function testLoadUserGroup()
    {
        $userService = $this->repository->getUserService();
        $userGroup = $userService->loadUserGroup( 4 );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $userGroup );
    }

    /**
     * Test loading a group throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     */
    public function testLoadUserGroupThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();
        $userService->loadUserGroup( PHP_INT_MAX );
    }

    /**
     * Test loading sub groups
     * @covers \eZ\Publish\API\Repository\UserService::loadSubUserGroups
     */
    public function testLoadSubUserGroups()
    {
        $userService = $this->repository->getUserService();

        $parentGroup = $userService->loadUserGroup( 4 );
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( "eng-GB" );
        $userGroupCreateStruct->ownerId = 14;
        $userGroupCreateStruct->sectionId = 1;
        $userGroupCreateStruct->setField( "name", "New group" );
        $userGroupCreateStruct->setField( "description", "This is a new group" );

        $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );

        $subGroups = $userService->loadSubUserGroups( $parentGroup );

        self::assertInternalType( 'array', $subGroups );
        self::assertNotEmpty( $subGroups );
    }

    /**
     * Test loading sub groups throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadSubUserGroups
     */
    public function testLoadSubUserGroupsThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $parentGroup = new UserGroup(
            array(
                'content' => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array( "contentInfo" => new ContentInfo( array( "id" => PHP_INT_MAX ) ) )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
        $userService->loadSubUserGroups( $parentGroup );
    }

    /**
     * Test deleting user group
     * @covers \eZ\Publish\API\Repository\UserService::deleteUserGroup
     */
    public function testDeleteUserGroup()
    {
        $userService = $this->repository->getUserService();

        $userGroup = $userService->loadUserGroup( 12 );
        $userService->deleteUserGroup( $userGroup );

        try
        {
            $userService->loadUserGroup( $userGroup->id );
            self::fail( "Succeeded loading deleted user group" );
        }
        catch( NotFoundException $e ){}
    }

    /**
     * Test deleting user group throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::deleteUserGroup
     */
    public function testDeleteUserGroupThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $userGroup = new UserGroup(
            array(
                'content' => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array( "contentInfo" => new ContentInfo( array( "id" => PHP_INT_MAX ) ) )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
        $userService->deleteUserGroup( $userGroup );
    }

    /**
     * Test moving a user group below another group
     * @covers \eZ\Publish\API\Repository\UserService::moveUserGroup
     */
    public function testMoveUserGroup()
    {
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $userGroupToMove = $userService->loadUserGroup( 42 );
        $parentUserGroup = $userService->loadUserGroup( 12 );
        $userService->moveUserGroup( $userGroupToMove, $parentUserGroup );

        $movedUserGroup = $userService->loadUserGroup( $userGroupToMove->id );

        $newMainLocation = $locationService->loadLocation( $movedUserGroup->getVersionInfo()->getContentInfo()->mainLocationId );
        self::assertEquals(
            $parentUserGroup->getVersionInfo()->getContentInfo()->mainLocationId,
            $newMainLocation->parentLocationId
        );
    }

    /**
     * Test moving a user group below another group throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::moveUserGroup
     */
    public function testMoveUserGroupThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $userGroupToMove = new UserGroup(
            array(
                'content' => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array( "contentInfo" => new ContentInfo( array( "id" => PHP_INT_MAX ) ) )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
        $parentUserGroup = new UserGroup(
            array(
                'content' => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array( "contentInfo" => new ContentInfo( array( "id" => PHP_INT_MAX ) ) )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
        $userService->moveUserGroup( $userGroupToMove, $parentUserGroup );
    }

    /**
     * Test updating a user group
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     */
    public function testUpdateUserGroup()
    {
        $userService = $this->repository->getUserService();
        $contentService = $this->repository->getContentService();

        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $contentService->newContentUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct->setField( "name", "New anonymous group", "eng-US" );

        $userGroup = $userService->loadUserGroup( 42 );

        $updatedUserGroup = $userService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup", $updatedUserGroup );
        self::assertEquals(
            $userGroupUpdateStruct->contentUpdateStruct->fields[0]->value,
            $updatedUserGroup->getFieldValue( "name" )
        );
    }

    /**
     * Test updating a user group throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     */
    public function testUpdateUserGroupThrowsContentValidationException()
    {
        $userService = $this->repository->getUserService();
        $contentService = $this->repository->getContentService();

        $userGroup = $userService->loadUserGroup( 42 );
        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $contentService->newContentUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct->setField( "name", "", "eng-US" );

        $userService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
    }

    /**
     * Test creating a user
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUser()
    {
        self::markTestSkipped( "Breaks with InMemory storage, due to incorrect fixtures" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "New", "eng-GB" );
        $userCreateStruct->setField( "last_name", "User", "eng-GB" );

        $parentGroup = $userService->loadUserGroup( 42 );
        $createdUser = $userService->createUser( $userCreateStruct, array( $parentGroup ) );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $createdUser );
        self::assertEquals( "New", $createdUser->getFieldValue( "first_name" ) );
        self::assertEquals( "User", $createdUser->getFieldValue( "last_name" ) );
        self::assertEquals( $userCreateStruct->login, $createdUser->login );
        self::assertEquals( $userCreateStruct->email, $createdUser->email );
    }

    /**
     * Test creating a user throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "New" );
        $userCreateStruct->setField( "last_name", "User" );

        $parentGroup = new UserGroup(
            array(
                'content' => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array(
                                "contentInfo" => new ContentInfo( array( 'id' => PHP_INT_MAX ) )
                            )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test creating a user throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsContentValidationException()
    {
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "", "eng-GB" );
        $userCreateStruct->setField( "last_name", "", "eng-GB" );

        $parentGroup = $userService->loadUserGroup( 12 );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test creating a user throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsInvalidArgumentException()
    {
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "admin", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "", "eng-GB" );
        $userCreateStruct->setField( "last_name", "", "eng-GB" );

        $parentGroup = $userService->loadUserGroup( 12 );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test loading a user
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUser()
    {
        $userService = $this->repository->getUserService();

        $loadedUser = $userService->loadUser( 14 );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $loadedUser );
        self::assertEquals( 14, $loadedUser->id );
    }

    /**
     * Test loading a user throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUserThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $userService->loadUser( PHP_INT_MAX );
    }

    /**
     * Test loading anonymous user
     * @covers \eZ\Publish\API\Repository\UserService::loadAnonymousUser
     */
    public function testLoadAnonymousUser()
    {
        $userService = $this->repository->getUserService();

        $loadedUser = $userService->loadAnonymousUser();

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $loadedUser );
        self::assertEquals( 10, $loadedUser->id );
    }

    /**
     * Test loading a user by credentials
     * @covers \eZ\Publish\API\Repository\UserService::loadUserByCredentials
     */
    public function testLoadUserByCredentials()
    {
        $userService = $this->repository->getUserService();

        $loadedUser = $userService->loadUserByCredentials( 'admin', 'publish' );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $loadedUser );

        $this->assertPropertiesCorrect(
            array(
                'id' => 14,
                'login' => 'admin',
                'email' => 'spam@ez.no',
                'passwordHash' => 'c78e3b0f3d9244ed8c6d1c29464bdff9',
                'hashAlgorithm' => User::PASSWORD_HASH_MD5_USER,
                'enabled' => true,
                'maxLogin' => 10
            ),
            $loadedUser
        );
    }

    /**
     * Test loading a user by credentials throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUserByCredentialsThrowsNotFoundException()
    {
        $userService = $this->repository->getUserService();

        $userService->loadUserByCredentials( 'non_existing_user', 'invalid_password' );
    }

    /**
     * Test loading a user by credentials throwing NotFoundException because of bad password
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUserByCredentialsThrowsNotFoundExceptionBadPassword()
    {
        $userService = $this->repository->getUserService();

        $userService->loadUserByCredentials( 'admin', 'some_password' );
    }

    /**
     * Test deleting a user
     * @covers \eZ\Publish\API\Repository\UserService::deleteUser
     */
    public function testDeleteUser()
    {
        $userService = $this->repository->getUserService();

        $user = $userService->loadUser( 14 );
        $userService->deleteUser( $user );

        try
        {
            $userService->loadUser( 14 );
            self::fail( "failed deleting a user" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test updating a user
     * @covers \eZ\Publish\API\Repository\UserService::updateUser
     */
    public function testUpdateUser()
    {
        self::markTestSkipped( "Breaks with InMemory storage, due to incorrect fixtures" );
        $userService = $this->repository->getUserService();

        $userUpdateStruct = $userService->newUserUpdateStruct();
        $userUpdateStruct->contentUpdateStruct = $this->repository->getContentService()->newContentUpdateStruct();
        $userUpdateStruct->contentUpdateStruct->setField( "first_name", "New first name", "eng-US" );
        $userUpdateStruct->contentUpdateStruct->setField( "last_name", "New last name", "eng-US" );

        $user = $userService->loadUser( 14 );

        $userService->updateUser( $user, $userUpdateStruct );
        $updatedUser = $userService->loadUser( $user->id );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $updatedUser );
        self::assertEquals(
            "New first name",
            $updatedUser->getFieldValue( "first_name" )
        );

        self::assertEquals(
            "New last name",
            $updatedUser->getFieldValue( "last_name" )
        );
    }

    /**
     * Test updating a user throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUser
     */
    public function testUpdateUserThrowsContentValidationException()
    {
        $userService = $this->repository->getUserService();
        $contentService = $this->repository->getContentService();

        $user = $userService->loadUser( 14 );
        $userUpdateStruct = $userService->newUserUpdateStruct();
        $userUpdateStruct->contentUpdateStruct = $contentService->newContentUpdateStruct();
        $userUpdateStruct->contentUpdateStruct->setField( "name", "", "eng-US" );

        $userService->updateUser( $user, $userUpdateStruct );
    }

    /**
     * Test assigning a user group to user
     * @covers \eZ\Publish\API\Repository\UserService::assignUserToUserGroup
     */
    public function testAssignUserToUserGroup()
    {
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 42 );

        $userService->assignUserToUserGroup( $user, $userGroup );

        $userLocations = $locationService->loadLocations( $user->getVersionInfo()->getContentInfo() );

        if ( !is_array( $userLocations ) || empty( $userLocations ) )
            self::fail( "Failed assigning user to user group" );

        $hasAddedLocation = false;
        foreach ( $userLocations as $location )
        {
            if ( $location->parentLocationId == $userGroup->getVersionInfo()->getContentInfo()->mainLocationId )
            {
                $hasAddedLocation = true;
                break;
            }
        }

        if ( !$hasAddedLocation )
            self::fail( "Failed assigning user to user group" );
    }

    /**
     * Test assigning a user group to user throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\UserService::assignUserToUserGroup
     */
    public function testAssignUserToUserGroupThrowsInvalidArgumentException()
    {
        $userService = $this->repository->getUserService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 12 );
        $userService->assignUserToUserGroup( $user, $userGroup );
    }

    /**
     * Test removing a user from user group
     * @covers \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroup()
    {
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 12 );

        $userService->unAssignUserFromUserGroup( $user, $userGroup );

        try
        {
            $user = $userService->loadUser( 14 );
        }
        catch ( NotFoundException $e )
        {
            // user was deleted because the group we assigned him from was his last location
            return;
        }

        $userLocations = $locationService->loadLocations( $user->getVersionInfo()->getContentInfo() );

        if ( is_array( $userLocations ) && !empty( $userLocations ) )
        {
            $hasRemovedLocation = false;
            foreach ( $userLocations as $location )
            {
                if ( $location->parentLocationId == $userGroup->getVersionInfo()->getContentInfo()->mainLocationId )
                {
                    $hasRemovedLocation = true;
                    break;
                }
            }

            if ( $hasRemovedLocation )
                self::fail( "Failed removing a user from user group" );
        }
    }

    /**
     * Test removing a user from user group throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsInvalidArgumentException()
    {
        $userService = $this->repository->getUserService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 42 );
        $userService->unAssignUserFromUserGroup( $user, $userGroup );
    }

    /**
     * Test loading user groups the user belongs to
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser
     */
    public function testLoadUserGroupsOfUser()
    {
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $user = $userService->loadUser( 14 );
        $userLocations = $locationService->loadLocations(
            $user->getVersionInfo()->getContentInfo()
        );

        $groupLocationIds = array();
        foreach ( $userLocations as $userLocation )
        {
            if ( $userLocation->parentLocationId !== null )
                $groupLocationIds[] = $userLocation->parentLocationId;
        }

        $userGroups = $userService->loadUserGroupsOfUser( $user );

        foreach ( $userGroups as $userGroup )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $userGroup );
            self::assertContains(
                $userGroup->getVersionInfo()->getContentInfo()->mainLocationId,
                $groupLocationIds
            );
        }
    }

    /**
     * Test loading user groups the user belongs to
     * @covers \eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup
     */
    public function testLoadUsersOfUserGroup()
    {
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $userGroup = $userService->loadUserGroup( 12 );
        $users = $userService->loadUsersOfUserGroup( $userGroup );

        self::assertInternalType( "array", $users );
        self::assertNotEmpty( $users );

        foreach ( $users as $user )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $user );

            $userLocation = $locationService->loadLocation( $user->getVersionInfo()->getContentInfo()->mainLocationId );

            self::assertEquals(
                $userGroup->getVersionInfo()->getContentInfo()->mainLocationId,
                $userLocation->parentLocationId
            );
        }
    }

    /**
     * Test creating new UserCreateStruct
     * @covers \eZ\Publish\API\Repository\UserService::newUserCreateStruct
     */
    public function testNewUserCreateStruct()
    {
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "admin", "admin@ez.no", "password", "eng-GB" );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserCreateStruct', $userCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'mainLanguageCode' => 'eng-GB',
                'login' => 'admin',
                'email' => 'admin@ez.no',
                'password' => 'password',
                'enabled' => true,
                'fields' => array()
            ),
            $userCreateStruct
        );
    }

    /**
     * Test creating new UserGroupCreateStruct
     * @covers \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStruct()
    {
        $userService = $this->repository->getUserService();

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( "eng-GB" );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct', $userGroupCreateStruct );
        self::assertEquals( "eng-GB", $userGroupCreateStruct->mainLanguageCode );
    }

    /**
     * Test creating new UserUpdateStruct
     * @covers \eZ\Publish\API\Repository\UserService::newUserUpdateStruct
     */
    public function testNewUserUpdateStruct()
    {
        $userService = $this->repository->getUserService();

        $userUpdateStruct = $userService->newUserUpdateStruct();

        self::assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserUpdateStruct',
            $userUpdateStruct
        );

        self::assertNull( $userUpdateStruct->contentUpdateStruct );
        self::assertNull( $userUpdateStruct->contentMetadataUpdateStruct );

        $this->assertPropertiesCorrect(
            array(
                'email' => null,
                'password' => null,
                'enabled' => null,
                'maxLogin' => null
            ),
            $userUpdateStruct
        );
    }

    /**
     * Test creating new UserGroupUpdateStruct
     * @covers \eZ\Publish\API\Repository\UserService::newUserGroupUpdateStruct
     */
    public function testNewUserGroupUpdateStruct()
    {
        $userService = $this->repository->getUserService();

        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();

        self::assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct',
            $userGroupUpdateStruct
        );

        self::assertNull( $userGroupUpdateStruct->contentUpdateStruct );
        self::assertNull( $userGroupUpdateStruct->contentMetadataUpdateStruct );
    }
}
