<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\Repository\Values\User\UserReference;

/**
 *  Test case for operations in the PermissionService.
 *
 * @see \eZ\Publish\API\Repository\PermissionService
 * @group integration
 * @group permission
 */
class PermissionServiceTest extends BaseTest
{
    /**
     * Test for the getCurrentUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::getCurrentUserReference()
     */
    public function testGetCurrentUserReferenceReturnsAnonymousUserReference()
    {
        $repository = $this->getRepository();
        $anonymousUserId = $this->generateId('user', 10);
        $repository->getPermissionService()->setCurrentUserReference(
            new UserReference($anonymousUserId)
        );

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // Only a UserReference has previously been set to the $repository

        $permissionService = $repository->getPermissionService();
        $anonymousUserReference = $permissionService->getCurrentUserReference();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\User\UserReference',
            $anonymousUserReference
        );
        $this->assertEquals(
            $anonymousUserReference->getUserId(),
            $repository->getUserService()->loadUser($anonymousUserId)->id
        );
    }

    /**
     * Test for the setCurrentUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::setCurrentUserReference()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testSetCurrentUserReference()
    {
        $repository = $this->getRepository();
        $repository->getPermissionService()->setCurrentUserReference(
            new UserReference(
                $this->generateId('user', 10)
            )
        );

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $permissionService = $repository->getPermissionService();

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionService->setCurrentUserReference($administratorUser);
        /* END: Use Case */

        $this->assertEquals(
            $administratorUserId,
            $permissionService->getCurrentUserReference()->getUserId()
        );

        $this->assertSame(
            $administratorUser,
            $permissionService->getCurrentUserReference()
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::hasAccess()
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
        $permissionService = $repository->getPermissionService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $permissionService->hasAccess('content', 'remove', $anonymousUser);
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessWithAnonymousUserNo
     */
    public function testHasAccessForCurrentUserNo()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $permissionService = $repository->getPermissionService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user reference
        $permissionService->setCurrentUserReference($anonymousUser);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $permissionService->hasAccess('content', 'remove');
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();
        $permissionService = $repository->getPermissionService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return true
        $hasAccess = $permissionService->hasAccess('content', 'read', $administratorUser);
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessWithAdministratorUser
     */
    public function testHasAccessForCurrentUserYes()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();
        $permissionService = $repository->getPermissionService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionService->setCurrentUserReference($administratorUser);

        // This call will return true
        $hasAccess = $permissionService->hasAccess('content', 'read');
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testSetCurrentUserReference
     */
    public function testHasAccessLimited()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        // This call will return an array of permission sets describing user's access
        // to reading content
        $permissionSets = $permissionService->hasAccess('content', 'read');
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessForCurrentUserNo
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
        $permissionService = $repository->getPermissionService();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user reference
        $permissionService->setCurrentUserReference($anonymousUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return false because anonymous user does not have access
        // to content removal and hence no permission to remove given content
        $canUser = $permissionService->canUser('content', 'remove', $contentInfo);

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentService->deleteContent($contentInfo);
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessForCurrentUserYes
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
        $permissionService = $repository->getPermissionService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionService->setCurrentUserReference($administratorUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return true
        $canUser = $permissionService->canUser('content', 'remove', $contentInfo);

        // Performing an action having necessary permissions will succeed
        $contentService->deleteContent($contentInfo);
        /* END: Use Case */

        $this->assertTrue($canUser);
        $contentService->loadContent($homeId);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     */
    public function testCanUserWithLimitationYes()
    {
        $repository = $this->getRepository();

        $imagesFolderId = $this->generateId('object', 49);

        /* BEGIN: Use Case */
        // $imagesFolderId contains the ID of the "Images" folder

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        $contentService = $repository->getContentService();

        // Performing an action having necessary permissions will succeed
        $imagesFolder = $contentService->loadContent($imagesFolderId);

        // This call will return true
        $canUser = $permissionService->canUser('content', 'read', $imagesFolder);
        /* END: Use Case */

        $this->assertTrue($canUser);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithLimitationNo()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        $userService = $repository->getUserService();

        // Load administrator user using UserService, this does not check for permissions
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return false as user with Editor role does not have
        // permission to read "Users" subtree
        $canUser = $permissionService->canUser('content', 'read', $administratorUser);

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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $userGroupContentTypeId = $this->generateId('type', 3);

        /* BEGIN: Use Case */
        // $userGroupContentTypeId contains the ID of the "UserGroup" ContentType

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        $contentTypeService = $repository->getContentTypeService();

        // Load the "UserGroup" ContentType
        $userGroupContentType = $contentTypeService->loadContentType($userGroupContentTypeId);

        // This call will throw "InvalidArgumentException" because $userGroupContentType
        // is an instance of \eZ\Publish\API\Repository\Values\ContentType\ContentType,
        // which can not be checked for user access
        $canUser = $permissionService->canUser('content', 'create', $userGroupContentType);
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     */
    public function testCanUserWithTargetYes()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

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
        $canUser = $permissionService->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action having necessary permissions will succeed
        $contentDraft = $contentService->createContent(
            $contentCreateStruct,
            array($locationCreateStruct)
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithTargetNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" frontpage location

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

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
        $canUser = $permissionService->canUser(
            'content',
            'create',
            $contentCreateStruct,
            $locationCreateStruct
        );

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentDraft = $contentService->createContent(
                $contentCreateStruct,
                array($locationCreateStruct)
            );
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
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

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

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
        $locationCreateStructs = array($locationCreateStruct1, $locationCreateStruct2);

        // This call will return true
        $canUser = $permissionService->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
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

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

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
        $locationCreateStructs = array($locationCreateStruct1, $locationCreateStruct2);

        // This call will return false because user with Editor role does not have permission to
        // create content in the "Administrator users" location subtree
        $canUser = $permissionService->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $homeId = $this->generateId('object', 57);

        /* BEGIN: Use Case */
        // $homeId contains the ID of the "Home" frontpage

        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will throw "InvalidArgumentException" because $targets argument must be an
        // instance of \eZ\Publish\API\Repository\Values\ValueObject class or an array of the same
        $canUser = $permissionService->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetURLAliasService
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionServiceTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentExceptionVariant()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $permissionService = $repository->getPermissionService();

        // Set created user as current user reference
        $permissionService->setCurrentUserReference($user);

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My awesome forum');
        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->alwaysAvailable = true;

        $urlAliasService = $repository->getURLAliasService();
        $rootUrlAlias = $urlAliasService->lookup('/');

        // This call will throw "InvalidArgumentException" because $rootAlias is not a valid target object
        $canUser = $permissionService->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionService::canUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCanUserThrowsBadStateException()
    {
        $this->markTestIncomplete(
            'Cannot be tested on current fixture since policy with unsupported limitation value is not available.'
        );
    }
}
