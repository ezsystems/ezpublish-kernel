<?php

/**
 * File containing the UserServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use Exception;
use ReflectionClass;

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
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     */
    public function testLoadUserGroup()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup($mainGroupId);
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup', $userGroup);
    }

    /**
     * Test for the loadUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testLoadUserGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingGroupId = $this->generateId('group', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a NotFoundException
        $userService->loadUserGroup($nonExistingGroupId);
        /* END: Use Case */
    }

    /**
     * Test for the loadSubUserGroups() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadSubUserGroups()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     */
    public function testLoadSubUserGroups()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $userGroup = $userService->loadUserGroup($mainGroupId);

        $subUserGroups = $userService->loadSubUserGroups($userGroup);
        foreach ($subUserGroups as $subUserGroup) {
            // Do something with the $subUserGroup
            $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup', $subUserGroup);
        }
        /* END: Use Case */
    }

    /**
     * Test loading sub groups throwing NotFoundException.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadSubUserGroups
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadSubUserGroupsThrowsNotFoundException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $parentGroup = new UserGroup(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(
                                ['id' => 123456]
                            ),
                            ]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
        $userService->loadSubUserGroups($parentGroup);
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserGroupCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $groupCreate = $userService->newUserGroupCreateStruct('eng-US');
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroupCreateStruct',
            $groupCreate
        );

        return $groupCreate;
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $groupCreate
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructSetsMainLanguageCode($groupCreate)
    {
        $this->assertEquals('eng-US', $groupCreate->mainLanguageCode);
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct $groupCreate
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     */
    public function testNewUserGroupCreateStructSetsContentType($groupCreate)
    {
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $groupCreate->contentType
        );
    }

    /**
     * Test for the newUserGroupCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserGroupCreateStruct($mainLanguageCode, $contentType)
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserGroupCreateStructWithSecondParameter()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test is only relevant for eZ Publish versions > 4');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        // Load the default ContentType for user groups
        $groupType = $contentTypeService->loadContentTypeByIdentifier('user_group');

        // Instantiate a new group create struct
        $groupCreate = $userService->newUserGroupCreateStruct(
            'eng-US',
            $groupType
        );
        /* END: Use Case */

        $this->assertSame($groupType, $groupCreate->contentType);
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
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
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup',
            $userGroup
        );

        $versionInfo = $userGroup->getVersionInfo();

        $this->assertEquals(APIVersionInfo::STATUS_PUBLISHED, $versionInfo->status);
        $this->assertEquals(1, $versionInfo->versionNo);

        return $userGroup;
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     *
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupSetsExpectedProperties($userGroup)
    {
        $this->assertEquals(
            array(
                'parentId' => $this->generateId('group', 4),
            ),
            array(
                'parentId' => $userGroup->parentId,
            )
        );
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', 'Example Group');
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
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupThrowsInvalidArgumentExceptionFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', new \stdClass());

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
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     */
    public function testCreateUserGroupWhenMissingField()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');

        // This call will fail with a "ContentFieldValidationException", because the
        // only mandatory field "name" is not set.
        $userService->createUserGroup($userGroupCreate, $parentUserGroup);
        /* END: Use Case */
    }

    /**
     * Test for the createUserGroup() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     *
     * @see \eZ\Publish\API\Repository\UserService::createUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserGroupCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateUserGroupInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Use Case */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        $repository->beginTransaction();

        try {
            // Load main group
            $parentUserGroup = $userService->loadUserGroup($mainGroupId);

            // Instantiate a new create struct
            $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
            $userGroupCreate->setField('name', 'Example Group');

            // Create the new user group
            $createdUserGroupId = $userService->createUserGroup(
                $userGroupCreate,
                $parentUserGroup
            )->id;
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try {
            // Throws exception since creation of user group was rolled back
            $loadedGroup = $userService->loadUserGroup($createdUserGroupId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('User group object still exists after rollback.');
    }

    /**
     * Test for the deleteUserGroup() method.
     *
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
        $userService->deleteUserGroup($userGroup);
        /* END: Use Case */

        // We use the NotFoundException here for verification
        $userService->loadUserGroup($userGroup->id);
    }

    /**
     * Test deleting user group throwing NotFoundException.
     *
     * @covers \eZ\Publish\API\Repository\UserService::deleteUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteUserGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $userGroup = new UserGroup(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            ['contentInfo' => new ContentInfo(['id' => 123456])]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
        $userService->deleteUserGroup($userGroup);
    }

    /**
     * Test for the moveUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::moveUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadSubUserGroups
     */
    public function testMoveUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $membersGroupId = $this->generateId('group', 13);
        /* BEGIN: Use Case */
        // $membersGroupId is the ID of the "Members" user group in an eZ
        // Publish demo installation

        $userGroup = $this->createUserGroupVersion1();

        // Load the new parent group
        $membersUserGroup = $userService->loadUserGroup($membersGroupId);

        // Move user group from "Users" to "Members"
        $userService->moveUserGroup($userGroup, $membersUserGroup);

        // Reload the user group to get an updated $parentId
        $userGroup = $userService->loadUserGroup($userGroup->id);

        $this->refreshSearch($repository);

        // The returned array will no contain $userGroup
        $subUserGroups = $userService->loadSubUserGroups(
            $membersUserGroup
        );
        /* END: Use Case */

        $subUserGroupIds = array_map(
            function ($content) {
                return $content->id;
            },
            $subUserGroups
        );

        $this->assertEquals($membersGroupId, $userGroup->parentId);
        $this->assertEquals(array($userGroup->id), $subUserGroupIds);
    }

    /**
     * Test moving a user group below another group throws NotFoundException.
     *
     * @covers \eZ\Publish\API\Repository\UserService::moveUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testMoveUserGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $userGroupToMove = new UserGroup(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            ['contentInfo' => new ContentInfo(['id' => 123456])]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
        $parentUserGroup = new UserGroup(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            ['contentInfo' => new ContentInfo(['id' => 123455])]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
        $userService->moveUserGroup($userGroupToMove, $parentUserGroup);
    }

    /**
     * Test for the newUserGroupUpdateStruct() method.
     *
     * @covers \eZ\Publish\API\Repository\UserService::newUserGroupUpdateStruct
     */
    public function testNewUserGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        $groupUpdate = $userService->newUserGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            UserGroupUpdateStruct::class,
            $groupUpdate
        );

        $this->assertNull($groupUpdate->contentUpdateStruct);
        $this->assertNull($groupUpdate->contentMetadataUpdateStruct);
    }

    /**
     * Test for the updateUserGroup() method.
     *
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
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserGroup',
            $userGroup
        );

        $this->assertEquals(1, $userGroup->getVersionInfo()->versionNo);
    }

    /**
     * Test for the updateUserGroup() method.
     *
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
        $contentUpdate->setField('name', 'Sindelfingen', 'eng-US');

        // Create a group update struct and set content update struct
        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentUpdateStruct = $contentUpdate;

        // This will update the name and the increment the group version number
        $userGroup = $userService->updateUserGroup(
            $userGroup,
            $groupUpdate
        );
        /* END: Use Case */

        $this->assertEquals('Sindelfingen', $userGroup->getFieldValue('name', 'eng-US'));

        $versionInfo = $userGroup->getVersionInfo();

        $this->assertEquals(APIVersionInfo::STATUS_PUBLISHED, $versionInfo->status);
        $this->assertEquals(2, $versionInfo->versionNo);
    }

    /**
     * Test for the updateUserGroup() method.
     *
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

        $this->assertEquals(APIVersionInfo::STATUS_PUBLISHED, $versionInfo->status);
        $this->assertEquals(1, $versionInfo->versionNo);
    }

    /**
     * Test for the updateUserGroup() method.
     *
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
        $contentUpdate->setField('name', new \stdClass(), 'eng-US');

        // Create a group update struct and set content update struct
        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with an InvalidArgumentException, because the
        // field "name" does not accept the given value
        $userService->updateUserGroup($userGroup, $groupUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct()
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
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\UserCreateStruct',
            $userCreate
        );

        return $userCreate;
    }

    /**
     * Test updating a user group throws ContentFieldValidationException.
     *
     * @covers \eZ\Publish\API\Repository\UserService::updateUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateUserGroupThrowsContentFieldValidationExceptionOnRequiredFieldEmpty()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();

        $userGroup = $userService->loadUserGroup(42);
        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $contentService->newContentUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct->setField('name', '', 'eng-US');

        $userService->updateUserGroup($userGroup, $userGroupUpdateStruct);
    }

    /**
     * Test for the newUserCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserCreateStruct $userCreate
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserCreateStruct
     */
    public function testNewUserCreateStructSetsExpectedProperties($userCreate)
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
     * @see \eZ\Publish\API\Repository\UserService::newUserCreateStruct($login, $email, $password, $mainLanguageCode, $contentType)
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewUserCreateStructWithFifthParameter()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test is only relevant for eZ Publish versions > 4');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        $userType = $contentTypeService->loadContentTypeByIdentifier('user');

        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US',
            $userType
        );
        /* END: Use Case */

        $this->assertSame($userType, $userCreate->contentType);
    }

    /**
     * Test for the createUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
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
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $user
        );

        return $user;
    }

    /**
     * Test for the createUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserSetsExpectedProperties(User $user)
    {
        $this->assertEquals(
            array(
                'login' => 'user',
                'email' => 'user@example.com',
                'mainLanguageCode' => 'eng-US',
            ),
            array(
                'login' => $user->login,
                'email' => $user->email,
                'mainLanguageCode' => $user->contentInfo->mainLanguageCode,
            )
        );
    }

    /**
     * Test for the createUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserWhenMissingField()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);
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
        $group = $userService->loadUserGroup($editorsGroupId);

        // This call will fail with a "ContentFieldValidationException", because the
        // mandatory fields "first_name" and "last_name" are not set.
        $userService->createUser($userCreate, array($group));
        /* END: Use Case */
    }

    /**
     * Test for the createUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsInvalidArgumentExceptionOnFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);
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
        $userCreate->setField('first_name', new \stdClass());
        $userCreate->setField('last_name', 'User');

        // Load parent group for the user
        $group = $userService->loadUserGroup($editorsGroupId);

        // This call will fail with an "InvalidArgumentException", because the
        // value for the firled "first_name" is not accepted by the field type.
        $userService->createUser($userCreate, array($group));
        /* END: Use Case */
    }

    /**
     * Test for the createUser() method.
     *
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument 'userCreateStruct' is invalid: User with provided login already exists
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testCreateUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);
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

        $userCreate->setField('first_name', 'Example');
        $userCreate->setField('last_name', 'User');

        // Load parent group for the user
        $group = $userService->loadUserGroup($editorsGroupId);

        // This call will fail with a "InvalidArgumentException", because the
        // user with "admin" login already exists.
        $userService->createUser($userCreate, array($group));
        /* END: Use Case */
    }

    /**
     * Test for the createUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
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

        try {
            $user = $this->createUserVersion1();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try {
            // Throws exception since creation of user was rolled back
            $loadedUser = $userService->loadUser($user->id);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('User object still exists after rollback.');
    }

    /**
     * Test creating a user throwing NotFoundException.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\UserService::createUser
     */
    public function testCreateUserThrowsNotFoundException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $userCreateStruct = $userService->newUserCreateStruct('new_user', 'new_user@ez.no', 'password', 'eng-GB');
        $userCreateStruct->setField('first_name', 'New');
        $userCreateStruct->setField('last_name', 'User');

        $parentGroup = new UserGroup(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(['id' => 123456]),
                            ]
                        ),
                        'internalFields' => [],
                    ]
                ),
            ]
        );
        $userService->createUser($userCreateStruct, [$parentGroup]);
    }

    /**
     * Test for the loadUser() method.
     *
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
        $userReloaded = $userService->loadUser($user->id);
        /* END: Use Case */

        $this->assertEquals($user, $userReloaded);
    }

    /**
     * Test for the loadUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testLoadUserThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingUserId = $this->generateId('user', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $userService = $repository->getUserService();

        // This call will fail with a "NotFoundException", because no user with
        // an id equal to self::DB_INT_MAX should exist.
        $userService->loadUser($nonExistingUserId);
        /* END: Use Case */
    }

    /**
     * Test for the loadAnonymousUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadAnonymousUser()
     */
    public function testLoadAnonymousUser()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();

        // Load default anonymous user available in each eZ Publish installation
        $anonymousUser = $userService->loadUser($anonymousUserId);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $anonymousUser
        );

        $this->assertEquals('anonymous', $anonymousUser->login);
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
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
        $userReloaded = $userService->loadUserByCredentials('user', 'secret');
        /* END: Use Case */

        $this->assertEquals($user, $userReloaded);
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
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
        $userService->loadUserByCredentials('user', 'SeCrEt');
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByCredentials
     */
    public function testLoadUserByCredentialsThrowsNotFoundExceptionForUnknownPasswordEmtpy()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will fail with a "NotFoundException", because the given
        // login/password combination does not exist.
        $userService->loadUserByCredentials('user', '');
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
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
        $userService->loadUserByCredentials('üser', 'secret');
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByCredentials() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserByCredentials()
     * @expectedException \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByCredentials
     */
    public function testLoadUserByCredentialsThrowsInvalidArgumentValueForEmptyLogin()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will fail with a "InvalidArgumentValue", because the given
        // login is empty.
        $userService->loadUserByCredentials('', 'secret');
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByLogin() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserByLogin()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUserByLogin()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1('User');

        // Load the newly created user
        $userReloaded = $userService->loadUserByLogin('User');
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'hashAlgorithm' => $user->hashAlgorithm,
                'enabled' => $user->enabled,
                'maxLogin' => $user->maxLogin,
                'id' => $user->id,
                'contentInfo' => $user->contentInfo,
                'versionInfo' => $user->versionInfo,
                'fields' => $user->fields,
            ),
            $userReloaded
        );
    }

    /**
     * Test for the loadUserByLogin() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserByLogin()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByLogin
     */
    public function testLoadUserByLoginThrowsNotFoundExceptionForUnknownLogin()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will fail with a "NotFoundException", because the given
        // login/password combination does not exist.
        $userService->loadUserByLogin('user42');
        /* END: Use Case */
    }

    /**
     * Test for the loadUserByLogin() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUserByLogin()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByLogin
     */
    public function testLoadUserByLoginWorksForLoginWithWrongCase()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Lookup by user login should ignore casing
        $userReloaded = $userService->loadUserByLogin('USER');
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'hashAlgorithm' => $user->hashAlgorithm,
                'enabled' => $user->enabled,
                'maxLogin' => $user->maxLogin,
                'id' => $user->id,
                'contentInfo' => $user->contentInfo,
                'versionInfo' => $user->versionInfo,
                'fields' => $user->fields,
            ),
            $userReloaded
        );
    }

    /**
     * Test for the loadUsersByEmail() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUsersByEmail()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUserByEmail()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Load the newly created user
        $usersReloaded = $userService->loadUsersByEmail('user@example.com');
        /* END: Use Case */

        $this->assertEquals(array($user), $usersReloaded);
    }

    /**
     * Test for the loadUsersByEmail() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::loadUsersByEmail()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserByEmail
     */
    public function testLoadUserByEmailReturnsEmptyInUnknownEmail()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        // This call will return empty array, because the given
        // login/password combination does not exist.
        $emptyUserList = $userService->loadUsersByEmail('user42@example.com');
        /* END: Use Case */

        $this->assertEquals(array(), $emptyUserList);
    }

    /**
     * Test for the deleteUser() method.
     *
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
        $userService->deleteUser($user);
        /* END: Use Case */

        // We use the NotFoundException here to verify that the user not exists
        $userService->loadUser($user->id);
    }

    /**
     * Test for the newUserUpdateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::newUserUpdateStruct()
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
            UserUpdateStruct::class,
            $userUpdate
        );

        $this->assertNull($userUpdate->contentUpdateStruct);
        $this->assertNull($userUpdate->contentMetadataUpdateStruct);

        $this->assertPropertiesCorrect(
            [
                'email' => null,
                'password' => null,
                'enabled' => null,
                'maxLogin' => null,
            ],
            $userUpdate
        );
    }

    /**
     * Test for the updateUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
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
        $userVersion2 = $userService->updateUser($user, $userUpdate);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $user
        );

        return $userVersion2;
    }

    /**
     * Test for the updateUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testNewUserUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateUserNoPassword()
    {
        $repository = $this->getRepository();
        $signalSlotUserService = $repository->getUserService();

        $signalSlotUserServiceReflection = new ReflectionClass($signalSlotUserService);
        $userServiceProperty = $signalSlotUserServiceReflection->getProperty('service');
        $userServiceProperty->setAccessible(true);
        $userService = $userServiceProperty->getValue($signalSlotUserService);

        $userServiceReflection = new ReflectionClass($userService);
        $settingsProperty = $userServiceReflection->getProperty('settings');
        $settingsProperty->setAccessible(true);
        $settingsProperty->setValue(
            $userService,
            [
                'hashType' => User::PASSWORD_HASH_MD5_USER,
            ] + $settingsProperty->getValue($userService)
        );

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $settingsProperty->setValue(
            $userService,
            [
                'hashType' => User::PASSWORD_HASH_PHP_DEFAULT,
            ] + $settingsProperty->getValue($userService)
        );

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set new values for maxLogin, don't change password
        $userUpdate->maxLogin = 43;
        $userUpdate->enabled = false;

        // Updated the user record.
        $userVersion2 = $userService->updateUser($user, $userUpdate);
        /* END: Use Case */

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals(
            [
                'login' => $user->login,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'hashAlgorithm' => $user->hashAlgorithm,
                'maxLogin' => 43,
                'enabled' => false,
            ],
            [
                'login' => $userVersion2->login,
                'email' => $userVersion2->email,
                'passwordHash' => $userVersion2->passwordHash,
                'hashAlgorithm' => $userVersion2->hashAlgorithm,
                'maxLogin' => $userVersion2->maxLogin,
                'enabled' => $userVersion2->enabled,
            ]
        );
    }

    /**
     * Test for the updateUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserUpdatesExpectedProperties(User $user)
    {
        $this->assertEquals(
            array(
                'login' => 'user',
                'email' => 'user@example.com',
                'maxLogin' => 42,
                'enabled' => false,
            ),
            array(
                'login' => $user->login,
                'email' => $user->email,
                'maxLogin' => $user->maxLogin,
                'enabled' => $user->enabled,
            )
        );
    }

    /**
     * Test for the updateUser() method.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserReturnsPublishedVersion(User $user)
    {
        $this->assertEquals(
            APIVersionInfo::STATUS_PUBLISHED,
            $user->getVersionInfo()->status
        );
    }

    /**
     * Test for the updateUser() method.
     *
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
        $userVersion2 = $userService->updateUser($user, $userUpdate);

        // The contentInfo->remoteId will be changed now.
        $remoteId = $userVersion2->contentInfo->remoteId;
        /* END: Use Case */

        $this->assertEquals('85e10037d1ac0a00aa75443ced483e08', $remoteId);
    }

    /**
     * Test for the updateUser() method.
     *
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
        $contentUpdate->setField('first_name', 'Hello', 'eng-US');
        $contentUpdate->setField('last_name', 'World', 'eng-US');

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // Updated the user record.
        $userVersion2 = $userService->updateUser($user, $userUpdate);

        $name = sprintf(
            '%s %s',
            $userVersion2->getFieldValue('first_name'),
            $userVersion2->getFieldValue('last_name')
        );
        /* END: Use Case */

        $this->assertEquals('Hello World', $name);
    }

    /**
     * Test for the updateUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::updateUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUpdateUser
     */
    public function testUpdateUserWhenMissingField()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get the ContentService implementation
        $contentService = $repository->getContentService();

        // Create a content update struct and change the remote id.
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('first_name', null, 'eng-US');

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with a "ContentFieldValidationException" because the
        // mandatory field "first_name" is set to an empty value.
        $userService->updateUser($user, $userUpdate);

        /* END: Use Case */
    }

    /**
     * Test for the updateUser() method.
     *
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
        $contentUpdate->setField('first_name', new \stdClass(), 'eng-US');

        // Create a new update struct instance
        $userUpdate = $userService->newUserUpdateStruct();

        // Set the content update struct.
        $userUpdate->contentUpdateStruct = $contentUpdate;

        // This call will fail with a "InvalidArgumentException" because the
        // the field "first_name" does not accept the given value.
        $userService->updateUser($user, $userUpdate);

        /* END: Use Case */
    }

    /**
     * Test for the loadUserGroupsOfUser() method.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUserGroupsOfUser()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // This array will contain the "Editors" user group name
        $userGroupNames = [];
        foreach ($userService->loadUserGroupsOfUser($user) as $userGroup) {
            $this->assertInstanceOf(UserGroup::class, $userGroup);
            $userGroupNames[] = $userGroup->getFieldValue('name');
        }
        /* END: Use Case */

        $this->assertEquals(['Editors'], $userGroupNames);
    }

    /**
     * Test for the loadUsersOfUserGroup() method.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
     */
    public function testLoadUsersOfUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $group = $userService->loadUserGroup($this->generateId('group', 13));

        /* BEGIN: Use Case */
        $this->createUserVersion1();

        $this->refreshSearch($repository);

        // This array will contain the email of the newly created "Editor" user
        $email = array();
        foreach ($userService->loadUsersOfUserGroup($group) as $user) {
            $this->assertInstanceOf(User::class, $user);
            $email[] = $user->email;
        }
        /* END: Use Case */
        $this->assertEquals(array('user@example.com'), $email);
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::assignUserToUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroupsOfUser
     */
    public function testAssignUserToUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $administratorGroupId = $this->generateId('group', 12);
        /* BEGIN: Use Case */
        // $administratorGroupId is the ID of the "Administrator" group in an
        // eZ Publish demo installation

        $user = $this->createUserVersion1();

        // Assign group to newly created user
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup($administratorGroupId)
        );

        // This array will contain "Editors" and "Administrator users"
        $userGroupNames = array();
        foreach ($userService->loadUserGroupsOfUser($user) as $userGroup) {
            $userGroupNames[] = $userGroup->getFieldValue('name');
        }
        /* END: Use Case */

        sort($userGroupNames, SORT_STRING);

        $this->assertEquals(
            array(
                'Administrator users',
                'Editors',
            ),
            $userGroupNames
        );
    }

    /**
     * Test for the assignUserToUserGroup() method.
     *
     * @covers \eZ\Publish\API\Repository\UserService::assignUserToUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument 'user' is invalid: user is already in the given user group
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testAssignUserToUserGroup
     */
    public function testAssignUserToUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId('group', 13);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        // $editorsGroupId is the ID of the "Editors" group in an
        // eZ Publish demo installation

        // This call will fail with an "InvalidArgumentException", because the
        // user is already assigned to the "Editors" group
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup($editorsGroupId)
        );
        /* END: Use Case */
    }

    /**
     * Test for the unAssignUssrFromUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::unAssignUssrFromUserGroup()
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUserGroupsOfUser
     */
    public function testUnAssignUserFromUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId('group', 13);
        $anonymousGroupId = $this->generateId('group', 42);

        /* BEGIN: Use Case */
        // $anonymousGroupId is the ID of the "Anonymous Users" group in an eZ
        // Publish demo installation

        $user = $this->createUserVersion1();

        // Assign group to newly created user
        $userService->assignUserToUserGroup(
            $user,
            $userService->loadUserGroup($anonymousGroupId)
        );

        // Unassign user from "Editors" group
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup($editorsGroupId)
        );

        // This array will contain "Anonymous Users"
        $userGroupNames = array();
        foreach ($userService->loadUserGroupsOfUser($user) as $userGroup) {
            $userGroupNames[] = $userGroup->getFieldValue('name');
        }
        /* END: Use Case */

        $this->assertEquals(array('Anonymous Users'), $userGroupNames);
    }

    /**
     * Test for the unAssignUserFromUserGroup() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUnAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $administratorGroupId = $this->generateId('group', 12);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        // $administratorGroupId is the ID of the "Administrator" group in an
        // eZ Publish demo installation

        // This call will fail with an "InvalidArgumentException", because the
        // user is not assigned to the "Administrator" group
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup($administratorGroupId)
        );
        /* END: Use Case */
    }

    /**
     * Test for the unAssignUserFromUserGroup() method removing user from the last group.
     *
     * @covers \eZ\Publish\API\Repository\UserService::unAssignUserFromUserGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Argument 'user' has a bad state: user only has one user group, cannot unassign from last group
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testUnAssignUserFromUserGroup
     */
    public function testUnAssignUserFromUserGroupThrowsBadStateArgumentException()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $editorsGroupId = $this->generateId('group', 13);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // This call will fail with an "BadStateException", because the
        // user has to be assigned to at least one group
        $userService->unAssignUserFromUserGroup(
            $user,
            $userService->loadUserGroup($editorsGroupId)
        );
        /* END: Use Case */
    }

    /**
     * Test that multi-language logic for the loadUserGroup method respects prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserGroupWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $userGroup = $this->createMultiLanguageUserGroup();
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $userGroup->contentInfo->mainLanguageCode;
        }

        $loadedUserGroup = $userService->loadUserGroup($userGroup->id, $prioritizedLanguages);

        self::assertEquals(
            $loadedUserGroup->getName($expectedLanguageCode),
            $loadedUserGroup->getName()
        );
        self::assertEquals(
            $loadedUserGroup->getFieldValue('description', $expectedLanguageCode),
            $loadedUserGroup->getFieldValue('description')
        );
    }

    /**
     * Test that multi-language logic works correctly after updating user group main language.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserGroupWithPrioritizedLanguagesListAfterMainLanguageUpdate(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();

        $userGroup = $this->createMultiLanguageUserGroup();

        $userGroupUpdateStruct = $userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $userGroupUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode = 'eng-GB';
        $userService->updateUserGroup($userGroup, $userGroupUpdateStruct);

        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = 'eng-GB';
        }

        $loadedUserGroup = $userService->loadUserGroup($userGroup->id, $prioritizedLanguages);

        self::assertEquals(
            $loadedUserGroup->getName($expectedLanguageCode),
            $loadedUserGroup->getName()
        );
        self::assertEquals(
            $loadedUserGroup->getFieldValue('description', $expectedLanguageCode),
            $loadedUserGroup->getFieldValue('description')
        );
    }

    /**
     * Test that multi-language logic for the loadSubUserGroups method respects prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadSubUserGroups
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadSubUserGroupsWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        // create main group for subgroups
        $userGroup = $this->createMultiLanguageUserGroup(4);
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $userGroup->contentInfo->mainLanguageCode;
        }

        // create subgroups
        $this->createMultiLanguageUserGroup($userGroup->id);
        $this->createMultiLanguageUserGroup($userGroup->id);

        $userGroup = $userService->loadUserGroup($userGroup->id, $prioritizedLanguages);

        $subUserGroups = $userService->loadSubUserGroups($userGroup, 0, 2, $prioritizedLanguages);
        foreach ($subUserGroups as $subUserGroup) {
            self::assertEquals(
                $subUserGroup->getName($expectedLanguageCode),
                $subUserGroup->getName()
            );
            self::assertEquals(
                $subUserGroup->getFieldValue('description', $expectedLanguageCode),
                $subUserGroup->getFieldValue('description')
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUser method respects prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUser
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $user = $this->createMultiLanguageUser();
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $user->contentInfo->mainLanguageCode;
        }

        $loadedUser = $userService->loadUser($user->id, $prioritizedLanguages);

        self::assertEquals(
            $loadedUser->getName($expectedLanguageCode),
            $loadedUser->getName()
        );

        foreach (['fist_name', 'last_name', 'signature'] as $fieldIdentifier) {
            self::assertEquals(
                $loadedUser->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                $loadedUser->getFieldValue($fieldIdentifier)
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUser method works correctly after updating
     * user content main language.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroup
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserWithPrioritizedLanguagesListAfterMainLanguageUpdate(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();

        $user = $this->createMultiLanguageUser();
        // sanity check
        self::assertEquals($user->contentInfo->mainLanguageCode, 'eng-US');

        $userUpdateStruct = $userService->newUserUpdateStruct();
        $userUpdateStruct->contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $userUpdateStruct->contentMetadataUpdateStruct->mainLanguageCode = 'eng-GB';
        $userService->updateUser($user, $userUpdateStruct);
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = 'eng-GB';
        }

        $loadedUser = $userService->loadUser($user->id, $prioritizedLanguages);

        self::assertEquals(
            $loadedUser->getName($expectedLanguageCode),
            $loadedUser->getName()
        );

        foreach (['fist_name', 'last_name', 'signature'] as $fieldIdentifier) {
            self::assertEquals(
                $loadedUser->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                $loadedUser->getFieldValue($fieldIdentifier)
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUserByLogin method respects prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserByLogin
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserByLoginWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $user = $this->createMultiLanguageUser();

        // load, with prioritized languages, the newly created user
        $loadedUser = $userService->loadUserByLogin($user->login, $prioritizedLanguages);
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $loadedUser->contentInfo->mainLanguageCode;
        }

        self::assertEquals(
            $loadedUser->getName($expectedLanguageCode),
            $loadedUser->getName()
        );

        foreach (['first_name', 'last_name', 'signature'] as $fieldIdentifier) {
            self::assertEquals(
                $loadedUser->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                $loadedUser->getFieldValue($fieldIdentifier)
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUserByCredentials method respects
     * prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserByCredentials
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserByCredentialsWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $user = $this->createMultiLanguageUser();

        // load, with prioritized languages, the newly created user
        $loadedUser = $userService->loadUserByCredentials(
            $user->login,
            'secret',
            $prioritizedLanguages
        );
        if ($expectedLanguageCode === null) {
            $expectedLanguageCode = $loadedUser->contentInfo->mainLanguageCode;
        }

        self::assertEquals(
            $loadedUser->getName($expectedLanguageCode),
            $loadedUser->getName()
        );

        foreach (['first_name', 'last_name', 'signature'] as $fieldIdentifier) {
            self::assertEquals(
                $loadedUser->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                $loadedUser->getFieldValue($fieldIdentifier)
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUsersByEmail method respects
     * prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUsersByEmail
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUsersByEmailWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $user = $this->createMultiLanguageUser();

        // load, with prioritized languages, users by email
        $loadedUsers = $userService->loadUsersByEmail($user->email, $prioritizedLanguages);

        foreach ($loadedUsers as $loadedUser) {
            if ($expectedLanguageCode === null) {
                $expectedLanguageCode = $loadedUser->contentInfo->mainLanguageCode;
            }
            self::assertEquals(
                $loadedUser->getName($expectedLanguageCode),
                $loadedUser->getName()
            );

            foreach (['first_name', 'last_name', 'signature'] as $fieldIdentifier) {
                self::assertEquals(
                    $loadedUser->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                    $loadedUser->getFieldValue($fieldIdentifier)
                );
            }
        }
    }

    /**
     * Test that multi-language logic for the loadUserGroupsOfUser method respects
     * prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUserGroupsOfUserWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $userGroup = $this->createMultiLanguageUserGroup();
        $user = $this->createMultiLanguageUser($userGroup->id);

        $userGroups = $userService->loadUserGroupsOfUser($user, 0, 25, $prioritizedLanguages);
        foreach ($userGroups as $userGroup) {
            self::assertEquals(
                $userGroup->getName($expectedLanguageCode),
                $userGroup->getName()
            );
            self::assertEquals(
                $userGroup->getFieldValue('description', $expectedLanguageCode),
                $userGroup->getFieldValue('description')
            );
        }
    }

    /**
     * Test that multi-language logic for the loadUsersOfUserGroup method respects
     * prioritized language list.
     *
     * @covers \eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup
     * @dataProvider getPrioritizedLanguageList
     * @param string[] $prioritizedLanguages
     * @param string|null $expectedLanguageCode language code of expected translation
     */
    public function testLoadUsersOfUserGroupWithPrioritizedLanguagesList(
        array $prioritizedLanguages,
        $expectedLanguageCode
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        // create parent user group
        $userGroup = $this->createMultiLanguageUserGroup();
        // add two users to the created parent user group
        $this->createMultiLanguageUser($userGroup->id);
        $this->createMultiLanguageUser($userGroup->id);

        // test loading of users via user group with prioritized languages list
        $users = $userService->loadUsersOfUserGroup($userGroup, 0, 25, $prioritizedLanguages);
        foreach ($users as $user) {
            if ($expectedLanguageCode === null) {
                $expectedLanguageCode = $user->contentInfo->mainLanguageCode;
            }
            self::assertEquals(
                $user->getName($expectedLanguageCode),
                $user->getName()
            );

            foreach (['first_name', 'last_name', 'signature'] as $fieldIdentifier) {
                self::assertEquals(
                    $user->getFieldValue($fieldIdentifier, $expectedLanguageCode),
                    $user->getFieldValue($fieldIdentifier)
                );
            }
        }
    }

    /**
     * Get prioritized languages list data.
     *
     * Test cases using this data provider should expect the following arguments:
     * <code>
     *   array $prioritizedLanguagesList
     *   string $expectedLanguage (if null - use main language)
     * </code>
     *
     * @return array
     */
    public function getPrioritizedLanguageList()
    {
        return [
            [[], null],
            [['eng-US'], 'eng-US'],
            [['eng-GB'], 'eng-GB'],
            [['eng-US', 'eng-GB'], 'eng-US'],
            [['eng-GB', 'eng-US'], 'eng-GB'],
            // use non-existent group as the first one
            [['ger-DE'], null],
            [['ger-DE', 'eng-GB'], 'eng-GB'],
        ];
    }

    /**
     * @param int $parentGroupId
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private function createMultiLanguageUserGroup($parentGroupId = 4)
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        // create user group with multiple translations
        $parentGroupId = $this->generateId('group', $parentGroupId);
        $parentGroup = $userService->loadUserGroup($parentGroupId);

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreateStruct->setField('name', 'US user group', 'eng-US');
        $userGroupCreateStruct->setField('name', 'GB user group', 'eng-GB');
        $userGroupCreateStruct->setField('description', 'US user group description', 'eng-US');
        $userGroupCreateStruct->setField('description', 'GB user group description', 'eng-GB');
        $userGroupCreateStruct->alwaysAvailable = true;

        return $userService->createUserGroup($userGroupCreateStruct, $parentGroup);
    }

    /**
     * Create a user group fixture in a variable named <b>$userGroup</b>,.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    private function createUserGroupVersion1()
    {
        $repository = $this->getRepository();

        $mainGroupId = $this->generateId('group', 4);
        /* BEGIN: Inline */
        // $mainGroupId is the ID of the main "Users" group

        $userService = $repository->getUserService();

        // Load main group
        $parentUserGroup = $userService->loadUserGroup($mainGroupId);

        // Instantiate a new create struct
        $userGroupCreate = $userService->newUserGroupCreateStruct('eng-US');
        $userGroupCreate->setField('name', 'Example Group');

        // Create the new user group
        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $parentUserGroup
        );
        /* END: Inline */

        return $userGroup;
    }

    /**
     * Create user with multiple translations of User Content fields.
     *
     * @param int $userGroupId User group ID (default 13 - Editors)
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    private function createMultiLanguageUser($userGroupId = 13)
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $randomLogin = md5(rand() . time());
        $userCreateStruct = $userService->newUserCreateStruct(
            $randomLogin,
            "{$randomLogin}@example.com",
            'secret',
            'eng-US'
        );
        $userCreateStruct->enabled = true;
        $userCreateStruct->alwaysAvailable = true;

        // set field for each language
        foreach (['eng-US', 'eng-GB'] as $languageCode) {
            $userCreateStruct->setField('first_name', "{$languageCode} Example", $languageCode);
            $userCreateStruct->setField('last_name', "{$languageCode} User", $languageCode);
            $userCreateStruct->setField('signature', "{$languageCode} signature", $languageCode);
        }

        // Load parent group for the user
        $group = $userService->loadUserGroup($userGroupId);

        // Create a new user
        return $userService->createUser($userCreateStruct, [$group]);
    }

    /**
     * Test for the createUser() method.
     *
     * @see \eZ\Publish\API\Repository\UserService::createUser()
     */
    public function testCreateUserInvalidPasswordHashTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'type' is invalid: Password hash type '42424242' is not recognized");

        $repository = $this->getRepository();
        $signalSlotUserService = $repository->getUserService();

        $signalSlotUserServiceReflection = new ReflectionClass($signalSlotUserService);
        $userServiceProperty = $signalSlotUserServiceReflection->getProperty('service');
        $userServiceProperty->setAccessible(true);
        $userService = $userServiceProperty->getValue($signalSlotUserService);

        $userServiceReflection = new ReflectionClass($userService);
        $settingsProperty = $userServiceReflection->getProperty('settings');
        $settingsProperty->setAccessible(true);

        $defaultUserServiceSettings = $settingsProperty->getValue($userService);

        /* BEGIN: Use Case */
        $settingsProperty->setValue(
            $userService,
            [
                'hashType' => 42424242, // Non-existing hash type
            ] + $settingsProperty->getValue($userService)
        );

        try {
            $this->createUserVersion1();
        } catch (InvalidArgumentException $e) {
            // Reset to default settings, so we don't break other tests
            $settingsProperty->setValue($userService, $defaultUserServiceSettings);

            throw $e;
        }
        /* END: Use Case */

        // Reset to default settings, so we don't break other tests
        $settingsProperty->setValue($userService, $defaultUserServiceSettings);
    }
}
