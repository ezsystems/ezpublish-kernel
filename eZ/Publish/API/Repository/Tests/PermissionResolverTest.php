<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use function array_filter;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\LookupLimitationResult;
use eZ\Publish\API\Repository\Values\User\LookupPolicyLimitations;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\Values\User\UserReference;

/**
 *  Test case for operations in the PermissionResolver.
 *
 * @see \eZ\Publish\API\Repository\PermissionResolver
 * @group integration
 * @group permission
 */
class PermissionResolverTest extends BaseTest
{
    /**
     * Test for the getCurrentUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::getCurrentUserReference()
     */
    public function testGetCurrentUserReferenceReturnsAnonymousUserReference()
    {
        $repository = $this->getRepository();
        $anonymousUserId = $this->generateId('user', 10);
        $repository->getPermissionResolver()->setCurrentUserReference(
            new UserReference($anonymousUserId)
        );

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // Only a UserReference has previously been set to the $repository

        $permissionResolver = $repository->getPermissionResolver();
        $anonymousUserReference = $permissionResolver->getCurrentUserReference();
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::setCurrentUserReference()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testSetCurrentUserReference()
    {
        $repository = $this->getRepository();
        $repository->getPermissionResolver()->setCurrentUserReference(
            new UserReference(
                $this->generateId('user', 10)
            )
        );

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $permissionResolver = $repository->getPermissionResolver();

        $userService = $repository->getUserService();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionResolver->setCurrentUserReference($administratorUser);
        /* END: Use Case */

        $this->assertEquals(
            $administratorUserId,
            $permissionResolver->getCurrentUserReference()->getUserId()
        );

        $this->assertSame(
            $administratorUser,
            $permissionResolver->getCurrentUserReference()
        );
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
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
        $permissionResolver = $repository->getPermissionResolver();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $permissionResolver->hasAccess('content', 'remove', $anonymousUser);
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessWithAnonymousUserNo
     */
    public function testHasAccessForCurrentUserNo()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user reference
        $permissionResolver->setCurrentUserReference($anonymousUser);

        // This call will return false because anonymous user does not have access
        // to content removal
        $hasAccess = $permissionResolver->hasAccess('content', 'remove');
        /* END: Use Case */

        $this->assertFalse($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     */
    public function testHasAccessWithAdministratorUser()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return true
        $hasAccess = $permissionResolver->hasAccess('content', 'read', $administratorUser);
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessWithAdministratorUser
     */
    public function testHasAccessForCurrentUserYes()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionResolver->setCurrentUserReference($administratorUser);

        // This call will return true
        $hasAccess = $permissionResolver->hasAccess('content', 'read');
        /* END: Use Case */

        $this->assertTrue($hasAccess);
    }

    /**
     * Test for the hasAccess() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testSetCurrentUserReference
     */
    public function testHasAccessLimited()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

        // This call will return an array of permission sets describing user's access
        // to reading content
        $permissionSets = $permissionResolver->hasAccess('content', 'read');
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserNo
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
        $permissionResolver = $repository->getPermissionResolver();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user reference
        $permissionResolver->setCurrentUserReference($anonymousUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return false because anonymous user does not have access
        // to content removal and hence no permission to remove given content
        $canUser = $permissionResolver->canUser('content', 'remove', $contentInfo);

        // Performing an action without necessary permissions will fail with "UnauthorizedException"
        if (!$canUser) {
            $contentService->deleteContent($contentInfo);
        }
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserYes
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
        $permissionResolver = $repository->getPermissionResolver();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionResolver->setCurrentUserReference($administratorUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return true
        $canUser = $permissionResolver->canUser('content', 'remove', $contentInfo);

        // Performing an action having necessary permissions will succeed
        $contentService->deleteContent($contentInfo);
        /* END: Use Case */

        $this->assertTrue($canUser);
        $contentService->loadContent($homeId);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     */
    public function testCanUserWithLimitationYes()
    {
        $repository = $this->getRepository();

        $imagesFolderId = $this->generateId('object', 49);

        /* BEGIN: Use Case */
        // $imagesFolderId contains the ID of the "Images" folder

        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

        $contentService = $repository->getContentService();

        // Performing an action having necessary permissions will succeed
        $imagesFolder = $contentService->loadContent($imagesFolderId);

        // This call will return true
        $canUser = $permissionResolver->canUser('content', 'read', $imagesFolder);
        /* END: Use Case */

        $this->assertTrue($canUser);
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithLimitationNo()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user

        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

        $userService = $repository->getUserService();

        // Load administrator user using UserService, this does not check for permissions
        $administratorUser = $userService->loadUser($administratorUserId);

        // This call will return false as user with Editor role does not have
        // permission to read "Users" subtree
        $canUser = $permissionResolver->canUser('content', 'read', $administratorUser);

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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $userGroupContentTypeId = $this->generateId('type', 3);

        /* BEGIN: Use Case */
        // $userGroupContentTypeId contains the ID of the "UserGroup" ContentType

        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

        $contentTypeService = $repository->getContentTypeService();

        // Load the "UserGroup" ContentType
        $userGroupContentType = $contentTypeService->loadContentType($userGroupContentTypeId);

        // This call will throw "InvalidArgumentException" because $userGroupContentType
        // is an instance of \eZ\Publish\API\Repository\Values\ContentType\ContentType,
        // which can not be checked for user access
        $canUser = $permissionResolver->canUser('content', 'create', $userGroupContentType);
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     */
    public function testCanUserWithTargetYes()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" location

        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

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
        $canUser = $permissionResolver->canUser(
            'content',
            'create',
            $contentCreateStruct,
            [$locationCreateStruct]
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserWithTargetNo()
    {
        $repository = $this->getRepository();

        $homeLocationId = $this->generateId('location', 2);

        /* BEGIN: Use Case */
        // $homeLocationId contains the ID of the "Home" frontpage location

        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

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
        $canUser = $permissionResolver->canUser(
            'content',
            'create',
            $contentCreateStruct,
            [$locationCreateStruct]
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
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

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

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
        $canUser = $permissionResolver->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
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

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

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
        $canUser = $permissionResolver->canUser(
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
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetURLAliasService
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testSetCurrentUserReference
     * @depends eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessLimited
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithTargetThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $permissionResolver = $repository->getPermissionResolver();

        // Set created user as current user reference
        $permissionResolver->setCurrentUserReference($user);

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
        $canUser = $permissionResolver->canUser(
            'content',
            'create',
            $contentCreateStruct,
            [$rootUrlAlias]
        );
        /* END: Use Case */
    }

    /**
     * Test for the canUser() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::canUser()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCanUserThrowsBadStateException()
    {
        $this->markTestIncomplete(
            'Cannot be tested on current fixture since policy with unsupported limitation value is not available.'
        );
    }

    /**
     * Test PermissionResolver::canUser for Users with different Limitations.
     *
     * @covers       \eZ\Publish\API\Repository\PermissionResolver::canUser
     *
     * @dataProvider getDataForTestCanUserWithLimitations
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param array $targets
     * @param bool $expectedResult expected result of canUser check
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCanUserWithLimitations(
        Limitation $limitation,
        $module,
        $function,
        ValueObject $object,
        array $targets,
        $expectedResult
    ) {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $roleService = $repository->getRoleService();
        $permissionResolver = $repository->getPermissionResolver();

        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => [$limitation]],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        $roleLimitation = $limitation instanceof Limitation\RoleLimitation ? $limitation : null;
        $roleService->assignRoleToUser($role, $user, $roleLimitation);

        $permissionResolver->setCurrentUserReference($user);

        self::assertEquals(
            $expectedResult,
            $permissionResolver->canUser($module, $function, $object, $targets)
        );
    }

    /**
     * Data provider for testCanUserWithLimitations.
     * @see testCanUserWithLimitations
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function getDataForTestCanUserWithLimitations()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->sectionId = 2;

        // return data sets, numbered for readability and debugging
        return [
            0 => [
                new Limitation\SubtreeLimitation(['limitationValues' => ['/1/2/']]),
                'content',
                'create',
                $contentCreateStruct,
                [],
                false,
            ],
            1 => [
                new Limitation\SectionLimitation(['limitationValues' => [2]]),
                'content',
                'create',
                $contentCreateStruct,
                [],
                true,
            ],
            2 => [
                new Limitation\ParentContentTypeLimitation(['limitationValues' => [1]]),
                'content',
                'create',
                $contentCreateStruct,
                [],
                false,
            ],
            3 => [
                new Limitation\ParentContentTypeLimitation(['limitationValues' => [3]]), // parent type has to be the UserGroup
                'content',
                'create',
                $contentService->loadContentInfo(14), // content type user (Administrator)
                [],
                true,
            ],
        ];
    }

    /**
     * Test for the lookupLimitations() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::lookupLimitations()
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends \eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserNo
     */
    public function testLookupLimitationsForAnonymousUserHasNoAccess(): void
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
        $permissionResolver = $repository->getPermissionResolver();

        // Load anonymous user
        $anonymousUser = $userService->loadUser($anonymousUserId);

        // Set anonymous user as current user reference
        $permissionResolver->setCurrentUserReference($anonymousUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // `$lookupLimitations->hasAccess` will return false because anonymous user does not have access
        // to content removal and hence no permission to remove given content. `$lookupLimitations->lookupPolicyLimitations`
        // will be empty array
        $lookupLimitations = $permissionResolver->lookupLimitations('content', 'remove', $contentInfo);
        /* END: Use Case */

        $this->assertFalse($lookupLimitations->hasAccess);
        $this->assertEquals($lookupLimitations->roleLimitations, []);
        $this->assertEquals($lookupLimitations->lookupPolicyLimitations, []);
    }

    /**
     * Test for the lookupLimitations() method.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::lookupLimitations()
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends \eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserYes
     */
    public function testLookupLimitationsForAdministratorUser(): void
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);
        $homeId = $this->generateId('object', 57);

        /* BEGIN: Use Case */
        // $administratorUserId contains the ID of the administrator user
        // $homeId contains the ID of the "Home" frontpage

        $contentService = $repository->getContentService();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();

        // Load administrator user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set administrator user as current user reference
        $permissionResolver->setCurrentUserReference($administratorUser);

        // Load the ContentInfo for "Home" frontpage
        $contentInfo = $contentService->loadContentInfo($homeId);

        // This call will return true
        $lookupLimitations = $permissionResolver->lookupLimitations('content', 'remove', $contentInfo);
        /* END: Use Case */

        $this->assertTrue($lookupLimitations->hasAccess);
        $this->assertEquals($lookupLimitations->roleLimitations, []);
        $this->assertEquals($lookupLimitations->lookupPolicyLimitations, []);
    }

    /**
     * When one of policy pass then all limitation should be returned.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::lookupLimitations()
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends \eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserYes
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupLimitationsWithLimitations(): void
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();
        $roleService = $repository->getRoleService();

        $module = 'content';
        $function = 'create';

        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\SubtreeLimitation(['limitationValues' => ['/1/2/']])]],
                ['module' => $module, 'function' => $function, 'limitations' => [
                    new Limitation\SectionLimitation(['limitationValues' => [2]]),
                    new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']]),
                ]],
                ['module' => 'content', 'function' => 'edit', 'limitations' => [new Limitation\SectionLimitation(['limitationValues' => [2]])]],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        // Here we have no RoleLimitation
        $roleService->assignRoleToUser($role, $user);
        $permissionResolver->setCurrentUserReference($user);

        $expected = new LookupLimitationResult(
            true,
            [],
            [
                new LookupPolicyLimitations(
                    $role->getPolicies()[1],
                    [
                        new Limitation\SectionLimitation(['limitationValues' => [2]]),
                        new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']]),
                    ]
                ),
            ]
        );

        self::assertEquals(
            $expected,
            $permissionResolver->lookupLimitations($module, $function, $this->getContentCreateStruct($repository), [])
        );
    }

    /**
     * When one of policy pass then only filtered limitation should be returned.
     *
     * @see \eZ\Publish\API\Repository\PermissionResolver::lookupLimitations()
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetUserService
     * @depends \eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends \eZ\Publish\API\Repository\Tests\PermissionResolverTest::testHasAccessForCurrentUserYes
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupLimitationsWithFilteredLimitations(): void
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();
        $roleService = $repository->getRoleService();

        $module = 'content';
        $function = 'create';

        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\SubtreeLimitation(['limitationValues' => ['/1/2/']])]],
                ['module' => $module, 'function' => $function, 'limitations' => [
                    new Limitation\SectionLimitation(['limitationValues' => [2]]),
                    new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']]),
                ]],
                ['module' => 'content', 'function' => 'edit', 'limitations' => [new Limitation\SectionLimitation(['limitationValues' => [2]])]],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        // Here we have no RoleLimitation
        $roleService->assignRoleToUser($role, $user);
        $permissionResolver->setCurrentUserReference($user);

        $expected = new LookupLimitationResult(
            true,
            [],
            [
                new LookupPolicyLimitations(
                    $role->getPolicies()[1],
                    [
                        new Limitation\SectionLimitation(['limitationValues' => [2]]),
                    ]
                ),
            ]
        );

        self::assertEquals(
            $expected,
            $permissionResolver->lookupLimitations($module, $function, $this->getContentCreateStruct($repository), [], [Limitation::SECTION])
        );
    }

    /**
     * If the role limitation is set it should be taken into account. In this case, role limitation
     * will pass and ContentTypeLimitation should be returned.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupLimitationsWithRoleLimitationsHasAccess(): void
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();
        $roleService = $repository->getRoleService();

        $module = 'content';
        $function = 'create';

        /* BEGIN: Use Case */
        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\SubtreeLimitation(['limitationValues' => ['/1/2/']])]],
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']])]],
                ['module' => 'content', 'function' => 'edit', 'limitations' => [new Limitation\SectionLimitation(['limitationValues' => [2]])]],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        // SectionLimitation as RoleLimitation will pass
        $roleLimitation = new Limitation\SectionLimitation(['limitationValues' => [2]]);
        $roleService->assignRoleToUser($role, $user, $roleLimitation);
        $permissionResolver->setCurrentUserReference($user);
        /* END: Use Case */

        $expected = new LookupLimitationResult(
            true,
            [$roleLimitation],
            [
                new LookupPolicyLimitations(
                    $role->getPolicies()[1],
                    [new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']])]
                ),
            ]
        );

        self::assertEquals(
            $expected,
            $permissionResolver->lookupLimitations($module, $function, $this->getContentCreateStruct($repository), [])
        );
    }

    /**
     * If the role limitation is set and policy limitation is not set it should be taken into account.
     * In this case, role limitation will pass and SectionLimitation should be returned as role limitation
     * and limitations in LookupPolicyLimitations should be an empty array.
     *
     * @see https://jira.ez.no/browse/EZP-30728
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupLimitationsWithRoleLimitationsWithoutPolicyLimitationsHasAccess(): void
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();
        $roleService = $repository->getRoleService();

        $module = 'content';
        $function = 'create';

        /* BEGIN: Use Case */
        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => []],
                ['module' => 'content', 'function' => 'edit', 'limitations' => []],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        // SectionLimitation as RoleLimitation will pass
        $roleLimitation = new Limitation\SectionLimitation(['limitationValues' => [2]]);
        $roleService->assignRoleToUser($role, $user, $roleLimitation);
        $permissionResolver->setCurrentUserReference($user);
        /* END: Use Case */

        $expectedPolicy = current(array_filter($role->getPolicies(), function ($policy) use ($module, $function) {
            return $policy->module === $module && $policy->function === $function;
        }));

        $expected = new LookupLimitationResult(
            true,
            [$roleLimitation],
            [
                new LookupPolicyLimitations(
                    $expectedPolicy,
                    []
                ),
            ]
        );

        self::assertEquals(
            $expected,
            $permissionResolver->lookupLimitations($module, $function, $this->getContentCreateStruct($repository), [])
        );
    }

    /**
     * If the role limitation is set it should be taken into account. In this case, role limitation
     * will not pass and ContentTypeLimitation should not be returned.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\LimitationValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLookupLimitationsWithRoleLimitationsHasNoAccess(): void
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $permissionResolver = $repository->getPermissionResolver();
        $roleService = $repository->getRoleService();

        $module = 'content';
        $function = 'create';

        /* BEGIN: Use Case */
        $role = $this->createRoleWithPolicies(
            'role_' . __FUNCTION__,
            [
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\SubtreeLimitation(['limitationValues' => ['/1/2/']])]],
                ['module' => $module, 'function' => $function, 'limitations' => [new Limitation\LanguageLimitation(['limitationValues' => ['eng-US']])]],
                ['module' => 'content', 'function' => 'edit', 'limitations' => [new Limitation\SectionLimitation(['limitationValues' => [2]])]],
            ]
        );
        // create user in root user group to avoid overlapping of existing policies and limitations
        $user = $this->createUser('user', 'John', 'Doe', $userService->loadUserGroup(4));
        // SectionLimitation as RoleLimitation will not pass
        $roleLimitation = new Limitation\SectionLimitation(['limitationValues' => [3]]);
        $roleService->assignRoleToUser($role, $user, $roleLimitation);
        $permissionResolver->setCurrentUserReference($user);
        /* END: Use Case */

        $expected = new LookupLimitationResult(
            false,
            [],
            []
        );

        self::assertEquals(
            $expected,
            $permissionResolver->lookupLimitations($module, $function, $this->getContentCreateStruct($repository), [])
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param string $contentTypeIdentifier
     * @param string $mainLanguageCode
     * @param int $sectionId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getContentCreateStruct(
        Repository $repository,
        string $contentTypeIdentifier = 'folder',
        string $mainLanguageCode = 'eng-US',
        int $sectionId = 2
    ): ContentCreateStruct {
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $contentType = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, $mainLanguageCode);
        $contentCreateStruct->sectionId = $sectionId;

        return $contentCreateStruct;
    }
}
