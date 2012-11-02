<?php
/**
 * File containing the UserServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\User\User;

/**
 * Test case for operations in the UserService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\UserService
 * @group integration
 * @group user
 */
class UserServiceTest extends BaseTest
{
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

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup( $mainGroupId );
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

        $nonExistingGroupId = $this->generateId(  'group', PHP_INT_MAX );
        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a NotFoundException
        $userService->loadUserGroup( $nonExistingGroupId );
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

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup( $mainGroupId );

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
        $userService = $repository->getUserService();

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
        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroup',
            $userGroup
        );

        $versionInfo = $userGroup->getVersionInfo();

        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $versionInfo->status );
        $this->assertEquals( 1, $versionInfo->versionNo );

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
                'parentId' => $this->generateId( 'group', 4 ),
                'subGroupCount' => 0
            ),
            array(
                'parentId' => $userGroup->parentId,
                'subGroupCount' => $userGroup->subGroupCount
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
        $userService = $repository->getUserService();
        $mainGroupId = $this->generateId( 'group', 4 );

        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );
        $parentGroupCount = $parentUserGroup->subGroupCount;

        /* BEGIN: Use Case */
        $this->createUserGroupVersion1();

        // This should be one greater than before
        $subGroupCount = $userService->loadUserGroup( $mainGroupId )->subGroupCount;
        /* END: Use Case */

        $this->assertEquals( $parentGroupCount + 1, $subGroupCount );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', 'Example Group' );
        $userGroupCreate->remoteId = '5f7f0bdb3381d6a461d8c29ff53d908f';

        // This call will fail with an "InvalidArgumentException", because the
        // specified remoteId is already used for the "Members" user group.
        $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Use Case */
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsInvalidArgumentExceptionFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
        $userGroupCreate->setField( 'name', new \stdClass() );

        // This call will fail with an "InvalidArgumentException", because the
        // specified remoteId is already used for the "Members" user group.
        $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Use Case */
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

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );

        // This call will fail with a "ContentValidationException", because the
        // only mandatory field "name" is not set.
        $userService->createUserGroup( $userGroupCreate, $parentUserGroup );
        /* END: Use Case */
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
    public function testCreateUserGroupInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $repository->beginTransaction();

        try
        {
            // Load main group
            $parentUserGroup = $userService->loadUserGroup( $mainGroupId );

            // Instantiate a new create struct
            $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-US' );
            $userGroupCreate->setField( 'name', 'Example Group' );

            // Create the new user group
            $createdUserGroupId = $userService->createUserGroup(
                $userGroupCreate,
                $parentUserGroup
            )->id;
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try
        {
            // Throws exception since creation of user group was rolled back
            $loadedGroup = $userService->loadUserGroup( $createdUserGroupId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            return;
        }
        /* END: Use Case */

        $this->fail( 'User group object still exists after rollback.' );
    }

    /**
     * Test for the deleteUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::deleteUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testDeleteUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Delete the currently created user group again
        $userService->deleteUserGroup( $userGroup );
        /* END: Use Case */

        // We use the NotFoundException here for verification
        $userService->loadUserGroup( $userGroup->id );
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadSubUserGroups
     */
    public function testMoveUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $membersGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $membersGroupId is the ID of the "Members" user group in an eZ
        // Publish demo installation

        $userGroup = $this->createUserGroupVersion1();

        // Load the new parent group
        $membersUserGroup = $userService->loadUserGroup( $membersGroupId );

        // Move user group from "Users" to "Members"
        $userService->moveUserGroup( $userGroup, $membersUserGroup );

        // Reload the user group to get an updated $parentId
        $userGroup = $userService->loadUserGroup( $userGroup->id );

        // The returned array will no contain $userGroup
        $subUserGroups = $userService->loadSubUserGroups(
            $membersUserGroup
        );
        /* END: Use Case */

        $subUserGroupIds = array_map(
            function ( $content )
            {
                return $content->id;
            },
            $subUserGroups
        );

        $this->assertEquals( $membersGroupId, $userGroup->parentId );
        $this->assertEquals( array( $userGroup->id ), $subUserGroupIds );
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testMoveUserGroup
     */
    public function testMoveUserGroupIncrementsSubGroupCountOnNewParent()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $membersGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $membersGroupId is the ID of the "Members" user group in an eZ
        // Publish demo installation

        $userGroup = $this->createUserGroupVersion1();

        // Load the new parent group
        $membersUserGroup = $userService->loadUserGroup( $membersGroupId );

        // Move user group from "Users" to "Members"
        $userService->moveUserGroup( $userGroup, $membersUserGroup );

        // Reload the user group to get an updated $subGroupCount
        $membersUserGroupUpdated = $userService->loadUserGroup( $membersGroupId );
        /* END: Use Case */

        $this->assertEquals( 1, $membersUserGroupUpdated->subGroupCount );
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testMoveUserGroup
     */
    public function testMoveUserGroupDecrementsSubGroupCountOnOldParent()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $membersGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $membersGroupId is the ID of the "Members" user group in an eZ
        // Publish demo installation

        $userGroup = $this->createUserGroupVersion1();

        // Load the new parent group
        $membersUserGroup = $userService->loadUserGroup( $membersGroupId );

        // Move user group from "Users" to "Members"
        $userService->moveUserGroup( $userGroup, $membersUserGroup );
        /* END: Use Case */

        $mainUserGroup = $userService->loadUserGroup( $this->generateId( 'group', 4 ) );

        $this->assertEquals( 5, $mainUserGroup->subGroupCount );
    }

    /**
     * Test for the newUserGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testNewUserGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $groupUpdate = $userService->newUserGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct',
            $groupUpdate
        );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupUpdateStruct
     */
    public function testUpdateUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Create a group update struct and change nothing
        $groupUpdate = $userService->newUserGroupUpdateStruct();

        // This update will do nothing
        $userGroup = $userService->updateUserGroup(
            $userGroup,
            $groupUpdate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\User\UserGroup',
            $userGroup
        );

        $this->assertEquals( 1, $userGroup->getVersionInfo()->versionNo );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUserGroup
     */
    public function testUpdateUserGroupWithSubContentUpdateStruct()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the content service
        $contentService = $repository->getContentService();

        // Create a content update struct and update the group name
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'Sindelfingen', 'eng-US' );

        // Create a group update struct and set content update struct
        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentUpdateStruct = $contentUpdate;

        // This will update the name and the increment the group version number
        $userGroup = $userService->updateUserGroup(
            $userGroup,
            $groupUpdate
        );
        /* END: Use Case */

        $this->assertEquals( 'Sindelfingen', $userGroup->getFieldValue( 'name', 'eng-US' ) );

        $versionInfo = $userGroup->getVersionInfo();

        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $versionInfo->status );
        $this->assertEquals( 2, $versionInfo->versionNo );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUserGroup
     */
    public function testUpdateUserGroupWithSubContentMetadataUpdateStruct()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the content service
        $contentService = $repository->getContentService();

        // Create a metadata update struct and change the remoteId
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->remoteId = '3c61299780663bafa3af2101e52125da';

        // Create a group update struct and set content update struct
        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentMetadataUpdateStruct = $metadataUpdate;

        // This will update the name and the increment the group version number
        $userGroup = $userService->updateUserGroup(
            $userGroup,
            $groupUpdate
        );
        /* END: Use Case */

        $this->assertEquals(
            '3c61299780663bafa3af2101e52125da',
            $userGroup->contentInfo->remoteId
        );

        $versionInfo = $userGroup->getVersionInfo();

        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $versionInfo->status );
        $this->assertEquals( 1, $versionInfo->versionNo );
    }

    /**
     * Test for the updateUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUserGroup
     */
    public function testUpdateUserGroupThrowsInvalidArgumentExceptionOnFieldTypeNotAccept()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $userGroup = $this->createUserGroupVersion1();

        // Load the content service
        $contentService = $repository->getContentService();

        // Create a content update struct and update the group name
        $contentUpdate = $contentService->newContentUpdateStruct();
        // An object of stdClass is not accepted as a value by the field "name"
        $contentUpdate->setField( 'name', new \stdClass(), 'eng-US' );

        // Create a group update struct and set content update struct
        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with an InvalidArgumentException, because the
        // field "name" does not accept the given value
        $userService->updateUserGroup( $userGroup, $groupUpdate );
        /* END: Use Case */
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
                'login' => 'user',
                'email' => 'user@example.com',
                'password' => 'secret',
                'mainLanguageCode' => 'eng-US',
            ),
            array(
                'login' => $userCreate->login,
                'email' => $userCreate->email,
                'password' => $userCreate->password,
                'mainLanguageCode' => $userCreate->mainLanguageCode,
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
        $userService = $repository->getUserService();

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
                'login' => 'user',
                'email' => 'user@example.com',
                'passwordHash' => $this->createHash(
                    'user',
                    'secret',
                    $user->hashAlgorithm
                ),
                'mainLanguageCode' => 'eng-US'
            ),
            array(
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'mainLanguageCode' => $user->contentInfo->mainLanguageCode
            )
        );
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

        $editorsGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $editorsGroupId is the ID of the "Editors" user group in an eZ
        // Publish demo installation

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
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsInvalidArgumentExceptionOnFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $editorsGroupId is the ID of the "Editors" user group in an eZ
        // Publish demo installation

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );

        // An object of stdClass is not a valid value for the field first_name
        $userCreate->setField( 'first_name', new \stdClass() );
        $userCreate->setField( 'last_name', 'User' );

        // Load parent group for the user
        $group = $userService->loadUserGroup( $editorsGroupId );

        // This call will fail with an "InvalidArgumentException", because the
        // value for the firled "first_name" is not accepted by the field type.
        $userService->createUser( $userCreate, array( $group ) );
        /* END: Use Case */
    }

    /**
     * Test for the createUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        // $editorsGroupId is the ID of the "Editors" user group in an eZ
        // Publish demo installation

        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            // admin is an existing login
            'admin',
            'user@example.com',
            'secret',
            'eng-US'
        );

        $userCreate->setField( 'first_name', 'Example' );
        $userCreate->setField( 'last_name', 'User' );

        // Load parent group for the user
        $group = $userService->loadUserGroup( $editorsGroupId );

        // This call will fail with a "InvalidArgumentException", because the
        // user with "admin" login already exists.
        $userService->createUser( $userCreate, array( $group ) );
        /* END: Use Case */
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
    public function testCreateUserInTransactionWithRollback()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $repository->beginTransaction();

        try
        {
            $user = $this->createUserVersion1();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try
        {
            // Throws exception since creation of user was rolled back
            $loadedUser = $userService->loadUser( $user->id );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            return;
        }
        /* END: Use Case */

        $this->fail( 'User object still exists after rollback.' );
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

        $nonExistingUserId = $this->generateId( 'useer', PHP_INT_MAX );
        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a "NotFoundException", because no user with
        // an id equal to PHP_INT_MAX should exist.
        $userService->loadUser( $nonExistingUserId );
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
        $userUpdate->password = 'my-new-password';
        $userUpdate->maxLogin = 42;
        $userUpdate->enabled = false;

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
                'login' => 'user',
                'email' => 'user@example.com',
                'passwordHash' => $this->createHash(
                    'user',
                    'my-new-password',
                    $user->hashAlgorithm
                ),
                'maxLogin' => 42,
                'enabled' => false
            ),
            array(
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'maxLogin' => $user->maxLogin,
                'enabled' => $user->enabled
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
        $userUpdate->contentMetadataUpdateStruct = $metadataUpdate;

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
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserThrowsContentValidationException()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get the ContentService implementation
        $contentService = $repository->getContentService();

        // Create a content update struct and change the remote id.
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'first_name', null, 'eng-US' );

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with a "ContentValidationException" because the
        // mandary field "first_name" is set to an empty value.
        $userService->updateUser( $user, $userUpdate );

        /* END: Use Case */
    }

    /**
     * Test for the updateUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserThrowsInvalidArgumentExceptionOnFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get the ContentService implementation
        $contentService = $repository->getContentService();

        $contentUpdate = $contentService->newContentUpdateStruct();
        // An object of stdClass is not valid for the field first_name
        $contentUpdate->setField( 'first_name', new \stdClass(), 'eng-US' );

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with a "InvalidArgumentException" because the
        // the field "first_name" does not accept the given value.
        $userService->updateUser( $user, $userUpdate );

        /* END: Use Case */
    }

    /**
     * Test for the loadUserGroupsOfUser() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUserGroupsOfUser()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // This array will contain the "Editors" user group name
        $userGroupNames = array();
        foreach ( $userService->loadUserGroupsOfUser( $user ) as $userGroup )
        {
            $userGroupNames[] = $userGroup->getFieldValue( 'name' );
        }
        /* END: Use Case */

        $this->assertEquals( array( 'Editors' ), $userGroupNames );
    }

    /**
     * Test for the loadUsersOfUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUsersOfUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $group = $userService->loadUserGroup( $this->generateId( 'group', 13 ) );

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This array will contain the email of the newly created "Editor" user
        $email = array();
        foreach ( $userService->loadUsersOfUserGroup( $group ) as $user )
        {
            $email[] = $user->email;
        }
        /* END: Use Case */
        $this->assertEquals( array( 'user@example.com' ), $email );
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroupsOfUser
     */
    public function testAssignUserToUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $administratorGroupId = $this->generateId( 'group', 12 );
        /* BEGIN: Use Case */
        // $administratorGroupId is the ID of the "Administrator" group in an
        // eZ Publish demo installation

        $user = $this->createUserVersion1();

        // Assign group to newly created user
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup( $administratorGroupId )
        );

        // This array will contain "Editors" and "Administrator users"
        $userGroupNames = array();
        foreach ( $userService->loadUserGroupsOfUser( $user ) as $userGroup )
        {
            $userGroupNames[] = $userGroup->getFieldValue( 'name' );
        }
        /* END: Use Case */

        sort( $userGroupNames, SORT_STRING );

        $this->assertEquals(
            array(
                'Administrator users',
                'Editors'
            ),
            $userGroupNames
        );
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testAssignUserToUserGroup
     */
    public function testAssignUserToUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId( 'group', 13 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        // $editorsGroupId is the ID of the "Editors" group in an
        // eZ Publish demo installation

        // This call will fail with an "InvalidArgumentException", because the
        // user is already assigned to the "Editors" group
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup( $editorsGroupId )
        );
        /* END: Use Case */
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroupsOfUser
     */
    public function testUnAssignUserFromUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId( 'group', 13 );
        $anonymousGroupId = $this->generateId( 'group', 42 );

        /* BEGIN: Use Case */
        // $anonymousGroupId is the ID of the "Anonymous Users" group in an eZ
        // Publish demo installation

        $user = $this->createUserVersion1();

        // Assign group to newly created user
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup( $anonymousGroupId )
        );

        // Unassign user from "Editors" group
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup( $editorsGroupId )
        );

        // This array will contain "Anonymous Users"
        $userGroupNames = array();
        foreach ( $userService->loadUserGroupsOfUser( $user ) as $userGroup )
        {
            $userGroupNames[] = $userGroup->getFieldValue( 'name' );
        }
        /* END: Use Case */

        $this->assertEquals( array( 'Anonymous Users' ), $userGroupNames );
    }

    /**
     * Test for the unAssignUserFromUserGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUnAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $administratorGroupId = $this->generateId( 'group', 12 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        // $administratorGroupId is the ID of the "Administrator" group in an
        // eZ Publish demo installation

        // This call will fail with an "InvalidArgumentException", because the
        // user is not assigned to the "Adminstrator" group
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup( $administratorGroupId )
        );
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

        $mainGroupId = $this->generateId( 'group', 4 );
        /* BEGIN: Inline */
        // $mainGroupId is the ID of the main "Users" group

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
