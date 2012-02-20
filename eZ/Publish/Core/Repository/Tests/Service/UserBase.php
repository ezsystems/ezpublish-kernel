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

    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for User Service
 *
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
        self::assertEquals( null, $user->id );
        self::assertEquals( null, $user->login );
        self::assertEquals( null, $user->email );
        self::assertEquals( null, $user->passwordHash );
        self::assertEquals( null, $user->hashAlgorithm );
        self::assertEquals( null, $user->maxLogin );
        self::assertEquals( null, $user->isEnabled );

        $group = new UserGroup();
        self::assertEquals( null, $group->id );
        self::assertEquals( null, $group->parentId );
        self::assertEquals( null, $group->subGroupCount );
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
            $user->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyPermission $e ) {}

        try
        {
            $userGroup = new UserGroup();
            $userGroup->id = 42;
            // user group properties are (erroneously) public
            // @todo: enable when changed
            // self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyPermission $e ) {}
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

        $value = isset( $user->id );
        self::assertEquals( true, $value );

        $userGroup = new UserGroup();
        $value = isset( $userGroup->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $userGroup->id );
        // user group properties are (erroneously) public
        // @todo: enable when changed
        // self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\User\User::__unset
     * @covers \eZ\Publish\API\Repository\Values\User\UserGroup::__unset
     */
    public function testUnsetProperty()
    {
        $user = new User( array( "id" => 1 ) );
        try
        {
            unset( $user->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyPermission $e ) {}

        $userGroup = new UserGroup( array( "id" => 1 ) );
        try
        {
            unset( $userGroup->id );
            // user group properties are (erroneously) public
            // @todo: enable when changed
            // self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyPermission $e ) {}
    }

    /**
     * Test creating new user group
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $parentGroup = $userService->loadUserGroup( 4 );

        $userGroupCreateStruct->setField( 'name', 'New group' );
        $userGroupCreateStruct->setField( 'description', 'This is a new group' );

        $newGroup = $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $newGroup );
    }

    /**
     * Test creating new user group throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroupThrowsContentFieldValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $parentGroup = $userService->loadUserGroup( 4 );

        $userGroupCreateStruct->setField( 'name', null );

        $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );
    }

    /**
     * Test creating new user group throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUserGroup
     */
    public function testCreateUserGroupThrowsContentValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $parentGroup = $userService->loadUserGroup( 4 );

        $userService->createUserGroup( $userGroupCreateStruct, $parentGroup );
    }

    /**
     * Test loading a group
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     */
    public function testLoadUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
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
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();
        $userService->loadUserGroup( PHP_INT_MAX );
    }

    /**
     * Test loading sub groups
     * @covers \eZ\Publish\API\Repository\UserService::loadSubUserGroups
     */
    public function testLoadSubUserGroups()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();
        $parentGroup = $userService->loadUserGroup( 4 );

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
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $parentGroup = new UserGroup( array( "id" => PHP_INT_MAX ) );
        $userService->loadSubUserGroups( $parentGroup );
    }

    /**
     * Test deleting user group
     * @covers \eZ\Publish\API\Repository\UserService::deleteUserGroup
     */
    public function testDeleteUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroup = new UserGroup( array( "id" => 13 ) );
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
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroup = new UserGroup( array( "id" => PHP_INT_MAX ) );
        $userService->deleteUserGroup( $userGroup );
    }

    /**
     * Test moving a user group below another group
     * @covers \eZ\Publish\API\Repository\UserService::moveUserGroup
     */
    public function testMoveUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroupToMove = new UserGroup( array( "id" => 13 ) );
        $parentUserGroup = new UserGroup( array( "id" => 12 ) );
        $userService->moveUserGroup( $userGroupToMove, $parentUserGroup );

        $movedUserGroup = $userService->loadUserGroup( $userGroupToMove->id );
        self::assertEquals( $parentUserGroup->id, $movedUserGroup->parentId );
    }

    /**
     * Test moving a user group below another group throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::moveUserGroup
     */
    public function testMoveUserGroupThrowsNotFoundException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userGroupToMove = new UserGroup( array( "id" => PHP_INT_MAX ) );
        $parentUserGroup = new UserGroup( array( "id" => PHP_INT_MAX ) );
        $userService->moveUserGroup( $userGroupToMove, $parentUserGroup );
    }

    /**
     * Test updating a user group
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     */
    public function testUpdateUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $initialLanguageCode = "eng-GB";
        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct->initialLanguageCode = $initialLanguageCode;
        $userGroupUpdateStruct->contentUpdateStruct->setField( "name", "New editors" );

        $userGroup = new UserGroup( array( "id" => 13 ) );

        $updatedUserGroup = $userService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroup', $updatedUserGroup );
        self::assertEquals( $userGroupUpdateStruct->contentUpdateStruct->fields["name"][$initialLanguageCode],
                            $updatedUserGroup->getFieldValue( "name" )
        );
    }

    /**
     * Test updating a user group throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     */
    public function testUpdateUserGroupThrowsContentFieldValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $initialLanguageCode = "eng-GB";
        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct->initialLanguageCode = $initialLanguageCode;
        $userGroupUpdateStruct->contentUpdateStruct->setField( "name", null );

        $userGroup = new UserGroup( array( "id" => 13 ) );

        $userService->updateUserGroup( $userGroup, $userGroupUpdateStruct );
    }

    /**
     * Test updating a user group throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     */
    public function testUpdateUserGroupThrowsContentValidationException()
    {
        self::markTestIncomplete( "@todo: does this have sense? can we republish the user group object without specifying (modifying) any fields?" );
    }

    /**
     * Test creating a user
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUser()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "New" );
        $userCreateStruct->setField( "last_name", "User" );

        $parentGroup = $userService->loadUserGroup( 13 );
        $createdUser = $userService->createUser( $userCreateStruct, array( $parentGroup ) );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $createdUser );
        self::assertEquals( $userCreateStruct->fields[$userCreateStruct->mainLanguageCode]["first_name"], $createdUser->getFieldValue( "first_name" ) );
        self::assertEquals( $userCreateStruct->fields[$userCreateStruct->mainLanguageCode]["last_name"], $createdUser->getFieldValue( "last_name" ) );
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
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "New" );
        $userCreateStruct->setField( "last_name", "User" );

        $parentGroup = new UserGroup( array( "id" => PHP_INT_MAX ) );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test creating a user throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsContentFieldValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", null );
        $userCreateStruct->setField( "last_name", null );

        $parentGroup = $userService->loadUserGroup( 13 );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test creating a user throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsContentValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );

        $parentGroup = $userService->loadUserGroup( 13 );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );
    }

    /**
     * Test loading a user
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUser()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
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
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userService->loadUser( PHP_INT_MAX );
    }

    /**
     * Test loading a user by credentials
     * @covers \eZ\Publish\API\Repository\UserService::loadUserByCredentials
     */
    public function testLoadUserByCredentials()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct( "new_user", "new_user@ez.no", "password", "eng-GB" );
        $userCreateStruct->setField( "first_name", "New" );
        $userCreateStruct->setField( "last_name", "User" );

        $parentGroup = $userService->loadUserGroup( 13 );
        $userService->createUser( $userCreateStruct, array( $parentGroup ) );

        $loadedUser = $userService->loadUserByCredentials( $userCreateStruct->login, $userCreateStruct->password );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $loadedUser );
    }

    /**
     * Test loading a user by credentials throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     */
    public function testLoadUserByCredentialsThrowsNotFoundException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $userService->loadUserByCredentials( "non_existing_user", "some_password" );
    }

    /**
     * Test deleting a user
     * @covers \eZ\Publish\API\Repository\UserService::deleteUser
     */
    public function testDeleteUser()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $user = $userService->loadUser( 14 );
        $userService->deleteUser( $user );

        try
        {
            $userService->loadUser( 14 );
            self::fail( "failed deleting a user" );
        }
        catch ( NotFoundException $e ) {}
    }

    /**
     * Test updating a user
     * @covers \eZ\Publish\API\Repository\UserService::updateUser
     */
    public function testUpdateUser()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $initialLanguageCode = "eng-GB";
        $userUpdateStruct = $userService->newUserUpdateStruct();
        $userUpdateStruct->contentUpdateStruct->initialLanguageCode = $initialLanguageCode;
        $userUpdateStruct->contentUpdateStruct->setField( "first_name", "New first name" );
        $userUpdateStruct->contentUpdateStruct->setField( "last_name", "New last name" );

        $user = new User( array( "id" => 14 ) );

        $userService->updateUser( $user, $userUpdateStruct );
        $updatedUser = $userService->loadUser( $user->id );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\User', $updatedUser );
        self::assertEquals( $userUpdateStruct->contentUpdateStruct->fields["first_name"][$initialLanguageCode],
                            $updatedUser->getFieldValue( "first_name" )
        );

        self::assertEquals( $userUpdateStruct->contentUpdateStruct->fields["last_name"][$initialLanguageCode],
                            $updatedUser->getFieldValue( "last_name" )
        );
    }

    /**
     * Test updating a user throwing ContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUser
     */
    public function testUpdateUserThrowsContentFieldValidationException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $initialLanguageCode = "eng-GB";
        $userUpdateStruct = $userService->newUserUpdateStruct();
        $userUpdateStruct->contentUpdateStruct->initialLanguageCode = $initialLanguageCode;
        $userUpdateStruct->contentUpdateStruct->setField( "first_name", null );
        $userUpdateStruct->contentUpdateStruct->setField( "last_name", null );

        $user = new User( array( "id" => 14 ) );

        $userService->updateUser( $user, $userUpdateStruct );
    }

    /**
     * Test updating a user throwing ContentValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @covers \eZ\Publish\API\Repository\UserService::updateUser
     */
    public function testUpdateUserThrowsContentValidationException()
    {
        self::markTestIncomplete( "@todo: does this have sense? can we republish the user object without specifying (modifying) any fields?" );
    }

    /**
     * Test assigning a user group to user
     * @covers \eZ\Publish\API\Repository\UserService::assignUserToUserGroup
     */
    public function testAssignUserToUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 13 );
        $userGroupMainLocation = $locationService->loadMainLocation( $userGroup->getVersionInfo()->getContentInfo() );

        $userService->assignUserToUserGroup( $user, $userGroup );

        $userLocations = $locationService->loadLocations( $user->getVersionInfo()->getContentInfo() );

        if ( !is_array( $userLocations ) || empty( $userLocations ) )
            self::fail( "Failed assigning user to user group" );

        $hasAddedLocation = false;
        /** @var $location \eZ\Publish\API\Repository\Values\Content\Location */
        foreach ( $userLocations as $location )
        {
            if ( $location->parentId == $userGroupMainLocation->id )
            {
                $hasAddedLocation = true;
                break;
            }
        }

        if ( !$hasAddedLocation )
            self::fail( "Failed assigning user to user group" );
    }

    /**
     * Test removing a user from user group
     * @covers \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroup()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();
        $locationService = $this->repository->getLocationService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 12 );
        $userGroupMainLocation = $locationService->loadMainLocation( $userGroup->getVersionInfo()->getContentInfo() );

        $userService->unAssignUserFromUserGroup( $user, $userGroup );

        $userLocations = $locationService->loadLocations( $user->getVersionInfo()->getContentInfo() );

        if ( is_array( $userLocations ) && !empty( $userLocations ) )
        {
            $hasRemovedLocation = false;
            /** @var $location \eZ\Publish\API\Repository\Values\Content\Location */
            foreach ( $userLocations as $location )
            {
                if ( $location->parentId == $userGroupMainLocation->id )
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
     * Test removing a user from user group throwing IllegalArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsIllegalArgumentException()
    {
        self::markTestSkipped( "@todo: depends on content service, enable when implemented" );
        $userService = $this->repository->getUserService();

        $user = $userService->loadUser( 14 );
        $userGroup = $userService->loadUserGroup( 13 );
        $userService->unAssignUserFromUserGroup( $user, $userGroup );
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
        self::assertEquals( "admin", $userCreateStruct->login );
        self::assertEquals( "admin@ez.no", $userCreateStruct->email );
        self::assertEquals( "password", $userCreateStruct->password );
        self::assertEquals( "eng-GB", $userCreateStruct->mainLanguageCode );
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
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $userService = $this->repository->getUserService();

        $userUpdateStruct = $userService->newUserUpdateStruct();
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserUpdateStruct', $userUpdateStruct );
        self::assertNull( $userUpdateStruct->email );
        self::assertNull( $userUpdateStruct->isEnabled );
        self::assertNull( $userUpdateStruct->maxLogin );
        self::assertNull( $userUpdateStruct->password );
        self::assertNull( $userUpdateStruct->contentUpdateStruct );
    }

    /**
     * Test creating new UserGroupUpdateStruct
     * @covers \eZ\Publish\API\Repository\UserService::newUserGroupUpdateStruct
     */
    public function testNewUserGroupUpdateStruct()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $userService = $this->repository->getUserService();

        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct', $userGroupUpdateStruct );
        self::assertNull( $userGroupUpdateStruct->contentUpdateStruct );
    }
}
