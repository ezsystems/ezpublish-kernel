<?php

/**
 * File containing the RepositoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use Exception;
use eZ\Publish\API\Repository\NotificationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Values\User\UserReference;

/**
 * Test case for operations in the Repository using in memory storage.
 *
 * @see eZ\Publish\API\Repository\Repository
 * @group integration
 */
class RepositoryTest extends BaseTest
{
    /**
     * Test for the getRepository() method.
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(Repository::class, $this->getSetupFactory()->getRepository(true));
    }

    /**
     * Test for the getContentService() method.
     *
     * @group content
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getContentService()
     */
    public function testGetContentService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ContentService',
            $repository->getContentService()
        );
    }

    /**
     * Test for the getContentLanguageService() method.
     *
     * @group language
     *
     * @see \eZ\Publish\API\Repository\Repository::getContentLanguageService()
     */
    public function testGetContentLanguageService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\LanguageService',
            $repository->getContentLanguageService()
        );
    }

    /**
     * Test for the getContentTypeService() method.
     *
     * @group content-type
     * @group field-type
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getContentTypeService()
     */
    public function testGetContentTypeService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ContentTypeService',
            $repository->getContentTypeService()
        );
    }

    /**
     * Test for the getLocationService() method.
     *
     * @group location
     *
     * @see \eZ\Publish\API\Repository\Repository::getLocationService()
     */
    public function testGetLocationService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\LocationService',
            $repository->getLocationService()
        );
    }

    /**
     * Test for the getSectionService() method.
     *
     * @group section
     *
     * @see \eZ\Publish\API\Repository\Repository::getSectionService()
     */
    public function testGetSectionService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SectionService',
            $repository->getSectionService()
        );
    }

    /**
     * Test for the getUserService() method.
     *
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getUserService()
     */
    public function testGetUserService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\UserService',
            $repository->getUserService()
        );
    }

    /**
     * Test for the getNotificationService() method.
     *
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getNotificationService()
     */
    public function testGetNotificationService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            NotificationService::class,
            $repository->getNotificationService()
        );
    }

    /**
     * Test for the getTrashService() method.
     *
     * @group trash
     *
     * @see \eZ\Publish\API\Repository\Repository::getTrashService()
     */
    public function testGetTrashService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\TrashService',
            $repository->getTrashService()
        );
    }

    /**
     * Test for the getRoleService() method.
     *
     * @group role
     *
     * @see \eZ\Publish\API\Repository\Repository::getRoleService()
     */
    public function testGetRoleService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\RoleService',
            $repository->getRoleService()
        );
    }

    /**
     * Test for the getURLAliasService() method.
     *
     * @group url-alias
     *
     * @see \eZ\Publish\API\Repository\Repository::getURLAliasService()
     */
    public function testGetURLAliasService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\URLAliasService',
            $repository->getURLAliasService()
        );
    }

    /**
     * Test for the getUrlWildcardService() method.
     *
     * @group url-wildcard
     *
     * @see \eZ\Publish\API\Repository\Repository::getUrlWildcardService()
     */
    public function testGetURLWildcardService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\URLWildcardService',
            $repository->getURLWildcardService()
        );
    }

    /**
     * Test for the getObjectStateService().
     *
     * @group object-state
     *
     * @see \eZ\Publish\API\Repository\Repository::getObjectStateService()
     */
    public function testGetObjectStateService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\ObjectStateService',
            $repository->getObjectStateService()
        );
    }

    /**
     * Test for the getFieldTypeService().
     *
     * @group object-state
     *
     * @see \eZ\Publish\API\Repository\Repository::getFieldTypeService()
     */
    public function testGetFieldTypeService()
    {
        $repository = $this->getRepository();
        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\FieldTypeService',
            $repository->getFieldTypeService()
        );
    }

    /**
     * Test for the getSearchService() method.
     *
     * @group search
     *
     * @see \eZ\Publish\API\Repository\Repository::getSearchService()
     */
    public function testGetSearchService()
    {
        $repository = $this->getRepository();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\SearchService',
            $repository->getSearchService()
        );
    }

    /**
     * Test for the getSearchService() method.
     *
     * @group permission
     *
     * @see \eZ\Publish\API\Repository\Repository::getPermissionResolver()
     */
    public function testGetPermissionResolver()
    {
        $repository = $this->getRepository();

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\PermissionResolver',
            $repository->getPermissionResolver()
        );
    }

    /**
     * Test for the commit() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::commit()
     */
    public function testCommit()
    {
        $repository = $this->getRepository();

        try {
            $repository->beginTransaction();
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }
    }

    /**
     * Test for the commit() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::commit()
     * @expectedException \RuntimeException
     */
    public function testCommitThrowsRuntimeException()
    {
        $repository = $this->getRepository();
        $repository->commit();
    }

    /**
     * Test for the rollback() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     */
    public function testRollback()
    {
        $repository = $this->getRepository();
        $repository->beginTransaction();
        $repository->rollback();
    }

    /**
     * Test for the rollback() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::rollback()
     * @expectedException \RuntimeException
     */
    public function testRollbackThrowsRuntimeException()
    {
        $repository = $this->getRepository();
        $repository->rollback();
    }

    /**
     * Test for the getCurrentUser() method.
     *
     * @group content
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::getCurrentUser()
     */
    public function testGetCurrentUserReturnsAnonymousUser()
    {
        $repository = $this->getRepository();
        $anonymousUserId = $this->generateId('user', 10);
        $repository->setCurrentUser(new UserReference($anonymousUserId));

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // Only a UserReference has previously been set to the $repository
        $anonymousUser = $repository->getCurrentUser();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\User\\User',
            $anonymousUser
        );
        $this->assertEquals(
            $anonymousUser->id,
            $repository->getUserService()->loadUser($anonymousUserId)->id
        );
    }

    /**
     * Test for the setCurrentUser() method.
     *
     * @group content
     * @group user
     *
     * @see \eZ\Publish\API\Repository\Repository::setCurrentUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testSetCurrentUser()
    {
        $repository = $this->getRepository();
        $repository->setCurrentUser(new UserReference($this->generateId('user', 10)));

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user
        $repository->setCurrentUser($administratorUser);
        /* END: Use Case */

        $this->assertEquals(
            $administratorUserId,
            $repository->getCurrentUserReference()->getUserId()
        );

        $this->assertEquals(
            $administratorUser->getUserId(),
            $repository->getCurrentUser()->getUserId()
        );

        $this->assertSame(
            $administratorUser,
            $repository->getCurrentUser()
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAnonymousUserNo()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $repository->hasAccess('content', 'remove', $anonymousUser);
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessWithAnonymousUserNo
     */
    public function testHasAccessForCurrentUserNo()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user
        $repository->setCurrentUser($anonymousUser);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $repository->hasAccess('content', 'remove');
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return true
        $hasAccess = $repository->hasAccess('content', 'read', $administratorUser);
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessWithAdministratorUser
     */
    public function testHasAccessForCurrentUserYes()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user
        $repository->setCurrentUser($administratorUser);

        // This call will return true
        $hasAccess = $repository->hasAccess('content', 'read');
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     */
    public function testHasAccessLimited()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        // This call will return an array of permission sets describing user's access
        // to reading content
        $permissionSets = $repository->hasAccess('content', 'read');
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $permissionSets
        );
        $this->assertNotEmpty($permissionSets);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessForCurrentUserNo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserForAnonymousUserNo()
    {
        $repository = $this->getRepository();

        $homeId = $this->generateId('object', 57);

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // $homeId contains the ID of the "Home" frontpage

        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user
        $repository->setCurrentUser($anonymousUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return false because anonymous user does not have access
        // to content removal and hence no permission to remove given content
        $canUser = $repository->canUser('content', 'remove', $contentInfo);

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentService->deleteContent($contentInfo);
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessForCurrentUserYes
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testCanUserForAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);
        $homeId = $this->generateId('object', 57);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user
        // $homeId contains the ID of the "Home" frontpage

        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user
        $repository->setCurrentUser($administratorUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return true
        $canUser = $repository->canUser('content', 'remove', $contentInfo);

        // Performing an action having necessary permissions will succeed
        $contentService->deleteContent($contentInfo);
        /* END: Use Case */

        $this->assertTrue($canUser);
        $contentService->loadContent($homeId);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithLimitationYes()
    {
        $repository = $this->getRepository();

        $imagesFolderId = $this->generateId('object', 49);

        /* BEGIN: Use Case */
        // $imagesFolderId contains the ID of the "Images" folder

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentService = $repository->getContentService();

        // Performing an action having necessary permissions will succeed
        $imagesFolder = $contentService->loadContent($imagesFolderId);

        // This call will return true
        $canUser = $repository->canUser('content', 'read', $imagesFolder);
        /* END: Use Case */

        $this->assertTrue($canUser);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithLimitationNo()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $userService = $repository->getUserService();

        // Load administrator user using UserService, this does not check for permissions
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return false as user with Editor role does not have
        // permission to read "Users" subtree
        $canUser = $repository->canUser('content', 'read', $administratorUser);

        $contentService = $repository->getContentService();

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $content = $contentService->loadContent($administratorUserId);
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $userGroupContentTypeId = $this->generateId('type', 3);

        /* BEGIN: Use Case */
        // $userGroupContentTypeId contains the ID of the "UserGroup" ContentType

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        // Load the "UserGroup" ContentType
        $userGroupContentType = $contentTypeService->loadContentType($userGroupContentTypeId);

        // This call will throw "InvalidArgumentException" because $userGroupContentType
        // is an instance of \eZ\Publish\API\Repository\Values\ContentType\ContentType,
        // which can not be checked for user access
        $canUser = $repository->canUser('content', 'create', $userGroupContentType);
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithTargetYes()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forums');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('title', 'My awesome forums');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct = $locationService->newLocationCreateStruct($homeLocationId);

        // This call will return true
        $canUser = $repository->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action having necessary permissions will succeed
        $contentDraft = $contentService->createContent(
            $contentCreateStruct,
            [$locationCreateStruct]
        );
        /* END: Use Case */

        $this->assertTrue($canUser);
        $this->assertEquals(
            'My awesome forums',
            $contentDraft->getFieldValue('title')->text
        );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithTargetNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" frontpage location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My awesome forum');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct = $locationService->newLocationCreateStruct($homeLocationId);

        // This call will return false because user with Editor role has permission to
        // create "forum" type content only under "folder" type content.
        $canUser = $repository->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentDraft = $contentService->createContent(
                $contentCreateStruct,
                [$locationCreateStruct]
            );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     */
    public function testCanUserWithMultipleTargetsYes()
    {
        $repository = $this->getRepository();

        $imagesLocationId = $this->generateId('location', 51);
        $filesLocationId = $this->generateId('location', 52);

        /* BEGIN: Use Case */
        // $imagesLocationId contains the ID of the "Images" location
        // $filesLocationId contains the ID of the "Files" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My multipurpose folder');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct1 = $locationService->newLocationCreateStruct($imagesLocationId);
        $locationCreateStruct2 = $locationService->newLocationCreateStruct($filesLocationId);
        $locationCreateStructs = [$locationCreateStruct1, $locationCreateStruct2];

        // This call will return true
        $canUser = $repository->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStructs
        );

        // Performing an action having necessary permissions will succeed
        $contentDraft = $contentService->createContent($contentCreateStruct, $locationCreateStructs);
        /* END: Use Case */

        $this->assertTrue($canUser);
        $this->assertEquals(
            'My multipurpose folder',
            $contentDraft->getFieldValue('name')->text
        );
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithMultipleTargetsNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);
        $administratorUsersLocationId = $this->generateId('location', 13);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location
        // $administratorUsersLocationId contains the ID of the "Administrator users" location

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forums');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My awesome forums');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $locationService = $repository->getLocationService();
        $locationCreateStruct1 = $locationService->newLocationCreateStruct($homeLocationId);
        $locationCreateStruct2 = $locationService->newLocationCreateStruct($administratorUsersLocationId);
        $locationCreateStructs = [$locationCreateStruct1, $locationCreateStruct2];

        // This call will return false because user with Editor role does not have permission to
        // create content in the "Administrator users" location subtree
        $canUser = $repository->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStructs
        );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentDraft = $contentService->createContent($contentCreateStruct, $locationCreateStructs);
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $homeId = $this->generateId('object', 57);

        /* BEGIN: Use Case */
        // $homeId contains the ID of the "Home" frontpage

        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will throw "InvalidArgumentException" because $targets argument must be an
        // instance of \eZ\Publish\API\Repository\Values\ValueObject class or an array of the same
        $canUser = $repository->canUser(
            'content',
            'remove',
            $contentInfo,
            new \stdClass()
        );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetURLAliasService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentExceptionVariant()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set created user as current user
        $repository->setCurrentUser($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My awesome forum');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $urlAliasService = $repository->getURLAliasService();
        $rootUrlAlias = $urlAliasService->lookUp('/');

        // This call will throw "InvalidArgumentException" because $rootAlias is not a valid target object
        $canUser = $repository->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $rootUrlAlias
        );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::canUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCanUserThrowsBadStateException()
    {
        $this->markTestIncomplete(
            'Cannot be tested on current fixture since policy with unsupported limitation value is not available.'
        );
    }
}
