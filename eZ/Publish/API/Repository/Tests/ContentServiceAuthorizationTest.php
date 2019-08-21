<?php

/**
 * File containing the ContentServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;

/**
 * Test case for operations in the ContentServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 * @group authorization
 */
class ContentServiceAuthorizationTest extends BaseContentServiceTest
{
    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate->setField('name', 'Awesome Sindelfingen forum');

        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'create\' \'content\'/');

        $contentService->createContent($contentCreate);
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'create\' \'content\'/');

        $this->createContentDraftVersion1();
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 10);
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        // $contentId contains a content object ID not accessible for anonymous
        $contentService->loadContentInfo($contentId);
        /* END: Use Case */
    }

    /**
     * Test for the sudo() method.
     *
     * @see \eZ\Publish\API\Repository\Repository::sudo()
     * @depends testLoadContentInfoThrowsUnauthorizedException
     */
    public function testSudo()
    {
        $repository = $this->getRepository();
        $contentId = $this->generateId('object', 10);
        // Set restricted editor user
        $repository->setCurrentUser($this->createAnonymousWithEditorRole());

        $contentInfo = $repository->sudo(function (Repository $repository) use ($contentId) {
            return $repository->getContentService()->loadContentInfo($contentId);
        });

        $this->assertInstanceOf(
            ContentInfo::class,
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfoList() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoList()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfoList
     */
    public function testLoadContentInfoListSkipsUnauthorizedItems()
    {
        $repository = $this->getRepository();
        $contentId = $this->generateId('object', 10);
        $contentService = $repository->getContentService();
        $repository->setCurrentUser($this->createAnonymousWithEditorRole());

        $list = $contentService->loadContentInfoList([$contentId]);

        $this->assertCount(0, $list);
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfoByRemoteId
     */
    public function testLoadContentInfoByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // RemoteId of the "Anonymous User" in an eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentInfoByRemoteId($anonymousRemoteId);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testLoadVersionInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadVersionInfo($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoWithSecondParameter
     */
    public function testLoadVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadVersionInfo($contentInfo, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadVersionInfoById($anonymousUserId);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoByIdWithSecondParameter
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadVersionInfoById($anonymousUserId, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionForFirstDraft()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentDraft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        // content versionread policy is needed because it is a draft
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadVersionInfoById(
            $contentDraft->id,
            $contentDraft->contentInfo->currentVersionNo
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByContentInfo($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithLanguageParameters
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByContentInfo($contentInfo, ['eng-US']);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithVersionNumberParameter
     */
    public function testLoadContentByContentInfoThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByContentInfo($contentInfo, ['eng-US'], 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfo
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo($contentInfo);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByVersionInfo($versionInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfoWithSecondParameter
     */
    public function testLoadContentByVersionInfoThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo($anonymousUserId);

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo($contentInfo);

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByVersionInfo($versionInfo, ['eng-US']);
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContent($anonymousUserId);
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithSecondParameter
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContent($anonymousUserId, ['eng-US']);
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithThirdParameter
     */
    public function testLoadContentThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContent($anonymousUserId, ['eng-US'], 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method on a draft.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsUnauthorizedExceptionOnDrafts()
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $user = $this->createUserVersion1();

        // Set new editor as a content owner
        $repository->setCurrentUser($user);

        // Create draft with this user
        $draft = $this->createContentDraftVersion1(2, 'folder');

        // Load anonymous user
        $userService = $repository->getUserService();
        $user = $userService->loadUser($anonymousUserId);
        $repository->setCurrentUser($user);

        // Try to load the draft with anonymous user to make sure access won't be allowed by throwing an exception
        $contentService = $repository->getContentService();

        $this->expectException(UnauthorizedException::class);
        // content versionread policy is needed because it is a draft
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadContent($draft->id);
        /* END: Use Case */
    }

    /**
     * Test for the ContentService::loadContent() method on an archive.
     *
     * This test the version permission on loading archived versions
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsUnauthorizedExceptionsOnArchives()
    {
        /** @var $repository \eZ\Publish\API\Repository\Repository */
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // get necessary services
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationSercice = $repository->getLocationService();

        // set admin as current user
        $repository->setCurrentUser($repository->getUserService()->loadUserByLogin('admin'));

        // create folder
        $newStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-US'
        );
        $newStruct->setField('name', 'Test Folder');
        $draft = $contentService->createContent(
            $newStruct,
            [$locationSercice->newLocationCreateStruct(2)]
        );
        $object = $contentService->publishVersion($draft->versionInfo);

        // update folder to make an archived version
        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField('name', 'Test Folder Updated');
        $draftUpdated = $contentService->updateContent(
            $contentService->createContentDraft($object->contentInfo)->versionInfo,
            $updateStruct
        );
        $objectUpdated = $contentService->publishVersion($draftUpdated->versionInfo);

        // set an anonymous as current user
        $repository->setCurrentUser($repository->getUserService()->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        // content versionread policy is needed because it is a draft
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadContent($objectUpdated->id, null, 1);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteId
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByRemoteId($anonymousRemoteId);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteIdWithSecondParameter
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByRemoteId($anonymousRemoteId, ['eng-US']);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteIdWithThirdParameter
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote id of the "Anonymous" user in a eZ Publish demo installation
        $anonymousRemoteId = 'faaeb9be3bd98ed09f606fc16d144eca';

        $contentService = $repository->getContentService();

        $pseudoEditor = $this->createAnonymousWithEditorRole();

        // Set restricted editor user
        $repository->setCurrentUser($pseudoEditor);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadContentByRemoteId($anonymousRemoteId, ['eng-US'], 2);
        /* END: Use Case */
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $content = $this->createContentVersion1();

        // Get ContentInfo instance.
        $contentInfo = $content->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // Creates a metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        $metadataUpdate->remoteId = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-US';
        $metadataUpdate->alwaysAvailable = false;
        $metadataUpdate->publishedDate = $this->createDateTime();
        $metadataUpdate->modificationDate = $this->createDateTime();

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'edit\' \'content\'/');

        $contentService->updateContentMetadata(
            $contentInfo,
            $metadataUpdate
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteContent
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentVersion2 = $this->createContentVersion2();

        // Get ContentInfo instance
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'remove\' \'content\'/');

        $contentService->deleteContent($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $content = $this->createContentVersion1();

        // Get ContentInfo instance
        $contentInfo = $content->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'edit\' \'content\'/');

        $contentService->createContentDraft($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraftWithSecondParameter
     */
    public function testCreateContentDraftThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $content = $this->createContentVersion1();

        // Get ContentInfo and VersionInfo instances
        $contentInfo = $content->contentInfo;
        $versionInfo = $content->getVersionInfo();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'edit\' \'content\'/');

        $contentService->createContentDraft($contentInfo, $versionInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testLoadContentDraftsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentService = $repository->getContentService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadContentDrafts();
        /* END: Use Case */
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts($user)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testLoadContentDraftsThrowsUnauthorizedExceptionWithFirstParameter()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId('user', 14);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // $administratorUserId is  the ID of the "Administrator" user in a eZ
        // Publish demo installation.

        $contentService = $repository->getContentService();

        // Load the user service
        $userService = $repository->getUserService();

        // Load the "Administrator" user
        $administratorUser = $userService->loadUser($administratorUserId);

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadContentDrafts($administratorUser);
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $draftVersion2 = $this->createContentDraftVersion2();

        // Get VersionInfo instance
        $versionInfo = $draftVersion2->getVersionInfo();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // Create an update struct and modify some fields
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('name', 'An awesome² story about ezp.');
        $contentUpdate->setField('name', 'An awesome²³ story about ezp.', 'eng-GB');

        $contentUpdate->initialLanguageCode = 'eng-US';

        $this->expectException(UnauthorizedException::class);
        /* TODO - the `content/edit` policy should be probably needed */
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->updateContent($versionInfo, $contentUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $draft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'publish\' \'content\'/');

        $contentService->publishVersion($draft->getVersionInfo());
        /* END: Use Case */
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteVersion
     */
    public function testDeleteVersionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $draft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionremove\' \'content\'/');

        $contentService->deleteVersion($draft->getVersionInfo());
        /* END: Use Case */
    }

    /**
     * Test for the loadVersions() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersions
     */
    public function testLoadVersionsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentVersion2 = $this->createContentVersion2();

        // Get ContentInfo instance of version 2
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadVersions($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $parentLocationId = $this->generateId('location', 52);

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentVersion2 = $this->createMultipleLanguageContentVersion2();

        // Get ContentInfo instance of version 2
        $contentInfo = $contentVersion2->contentInfo;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->copyContent(
            $contentInfo,
            $targetLocationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContentWithGivenVersion
     */
    public function testCopyContentThrowsUnauthorizedExceptionWithGivenVersion()
    {
        $parentLocationId = $this->generateId('location', 52);

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $contentVersion2 = $this->createContentVersion2();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate,
            $contentService->loadVersionInfo($contentVersion2->contentInfo, 1)
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testLoadRelationsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Remote id of the "Setup" page of a eZ Publish demo installation.
        $setupRemoteId = '241d538ce310074e602f29f49e44e938';

        $versionInfo = $contentService->loadVersionInfo(
            $contentService->loadContentInfoByRemoteId(
                $setupRemoteId
            )
        );

        // Set media editor as current user
        $repository->setCurrentUser($user);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'read\' \'content\'/');

        $contentService->loadRelations($versionInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testLoadRelationsForDraftVersionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $draft = $this->createContentDraftVersion1();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->loadRelations($draft->versionInfo);
        /* END: Use Case */
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadReverseRelations
     */
    public function testLoadReverseRelationsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // Remote id of the "Media" page of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $contentInfo = $contentService->loadContentInfoByRemoteId(
            $mediaRemoteId
        );

        // Set media editor as current user
        $repository->setCurrentUser($user);

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'reverserelatedlist\' \'content\'/');

        $contentService->loadReverseRelations($contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the addRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // Remote id of the "Media" page of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $draft = $this->createContentDraftVersion1();

        // Get the draft's version info
        $versionInfo = $draft->getVersionInfo();

        // Load other content object
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->addRelation(
            $versionInfo,
            $media
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        // Remote ids of the "Media" and the "Demo Design" page of a eZ Publish
        // demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $draft = $this->createContentDraftVersion1();

        // Get the draft's version info
        $versionInfo = $draft->getVersionInfo();

        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);
        $demoDesign = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Establish some relations
        $contentService->addRelation($draft->getVersionInfo(), $media);
        $contentService->addRelation($draft->getVersionInfo(), $demoDesign);

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessageRegExp('/\'versionread\' \'content\'/');

        $contentService->deleteRelation($versionInfo, $media);
        /* END: Use Case */
    }

    /**
     * Creates a pseudo editor with a limitation to objects in the "Media/Images"
     * subtree.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    private function createAnonymousWithEditorRole()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $roleService = $repository->getRoleService();
        $userService = $repository->getUserService();

        $user = $userService->loadUser($anonymousUserId);
        $role = $roleService->loadRoleByIdentifier('Editor');

        // Assign "Editor" role with limitation to "Media/Images"
        $roleService->assignRoleToUser(
            $role,
            $user,
            new \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation(
                [
                    'limitationValues' => ['/1/43/51/'],
                ]
            )
        );

        $pseudoEditor = $userService->loadUser($user->id);
        /* END: Inline */

        return $pseudoEditor;
    }

    /**
     * Test that for an user that doesn't have access (read permissions) to an
     * related object, executing loadRelations() would not throw any exception,
     * only that the non-readable related object(s) won't be loaded.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testLoadRelationsWithUnauthorizedRelations()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous User" in an eZ Publish
        // demo installation
        $mainLanguage = 'eng-GB';

        $contentService = $repository->getContentService();
        $contenTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $sectionService = $repository->getSectionService();
        $userService = $repository->getUserService();

        // set the current user as admin to create the environment to test
        $repository->setCurrentUser($userService->loadUserByLogin('admin'));

        // create section
        // since anonymous users have their read permissions to specific sections
        // the created section will be non-readable to them
        $sectionCreate = $sectionService->newSectionCreateStruct();
        $sectionCreate->identifier = 'private';
        $sectionCreate->name = 'Private Section';
        $section = $sectionService->createSection($sectionCreate);

        // create objects for testing
        // here we will create 4 objects which 2 will be readable by an anonymous
        // user, and the other 2 wont these last 2 will go to a private section
        // where anonymous can't read, just like:
        // readable object 1 -> /Main Folder
        // readable object 2 -> /Main Folder/Available Folder
        // non-readable object 1 -> /Restricted Folder
        // non-readable object 2 -> /Restricted Folder/Unavailable Folder
        //
        // here is created - readable object 1 -> /Main Folder
        $mainFolderCreate = $contentService->newContentCreateStruct(
            $contenTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguage
        );
        $mainFolderCreate->setField('name', 'Main Folder');
        $mainFolder = $contentService->publishVersion(
            $contentService->createContent(
                $mainFolderCreate,
                [$locationService->newLocationCreateStruct(2)]
            )->versionInfo
        );

        // here is created readable object 2 -> /Main Folder/Available Folder
        $availableFolderCreate = $contentService->newContentCreateStruct(
            $contenTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguage
        );
        $availableFolderCreate->setField('name', 'Avaliable Folder');
        $availableFolder = $contentService->publishVersion(
            $contentService->createContent(
                $availableFolderCreate,
                [$locationService->newLocationCreateStruct($mainFolder->contentInfo->mainLocationId)]
            )->versionInfo
        );

        // here is created the non-readable object 1 -> /Restricted Folder
        $restrictedFolderCreate = $contentService->newContentCreateStruct(
            $contenTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguage
        );
        $restrictedFolderCreate->setField('name', 'Restricted Folder');
        $restrictedFolderCreate->sectionId = $section->id;
        $restrictedFolder = $contentService->publishVersion(
            $contentService->createContent(
                $restrictedFolderCreate,
                [$locationService->newLocationCreateStruct(2)]
            )->versionInfo
        );

        // here is created non-readable object 2 -> /Restricted Folder/Unavailable Folder
        $unavailableFolderCreate = $contentService->newContentCreateStruct(
            $contenTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguage
        );
        $unavailableFolderCreate->setField('name', 'Unavailable Folder');
        $unavailableFolder = $contentService->publishVersion(
            $contentService->createContent(
                $unavailableFolderCreate,
                [$locationService->newLocationCreateStruct($restrictedFolder->contentInfo->mainLocationId)]
            )->versionInfo
        );

        // this will be our test object, which will have all the relations (as source)
        // and it is readable by the anonymous user
        $testFolderCreate = $contentService->newContentCreateStruct(
            $contenTypeService->loadContentTypeByIdentifier('folder'),
            $mainLanguage
        );
        $testFolderCreate->setField('name', 'Test Folder');
        $testFolderDraft = $contentService->createContent(
            $testFolderCreate,
            [$locationService->newLocationCreateStruct(2)]
        )->versionInfo;

        // add relations to test folder (as source)
        // the first 2 will be read by the user
        // and the other 2 wont
        //
        // create relation from Test Folder to Main Folder
        $mainRelation = $contentService->addRelation(
            $testFolderDraft,
            $mainFolder->getVersionInfo()->getContentInfo()
        );
        // create relation from Test Folder to Available Folder
        $availableRelation = $contentService->addRelation(
            $testFolderDraft,
            $availableFolder->getVersionInfo()->getContentInfo()
        );
        // create relation from Test Folder to Restricted Folder
        $contentService->addRelation(
            $testFolderDraft,
            $restrictedFolder->getVersionInfo()->getContentInfo()
        );
        //create relation from Test Folder to Unavailable Folder
        $contentService->addRelation(
            $testFolderDraft,
            $unavailableFolder->getVersionInfo()->getContentInfo()
        );

        // publish Test Folder
        $testFolder = $contentService->publishVersion($testFolderDraft);

        // set the current user to be an anonymous user since we want to test that
        // if the user doesn't have access to an related object that object wont
        // be loaded and no exception will be thrown
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // finaly load relations ( verify no exception is thrown )
        $actualRelations = $contentService->loadRelations($testFolder->getVersionInfo());

        /* END: Use case */

        // assert results
        // verify that the only expected relations are from the 2 readable objects
        // Main Folder and Available Folder
        $expectedRelations = [
            $mainRelation->destinationContentInfo->id => $mainRelation,
            $availableRelation->destinationContentInfo->id => $availableRelation,
        ];

        // assert there are as many expected relations as actual ones
        $this->assertEquals(
            count($expectedRelations),
            count($actualRelations),
            "Expected '" . count($expectedRelations)
            . "' relations found '" . count($actualRelations) . "'"
        );

        // assert each relation
        foreach ($actualRelations as $relation) {
            $destination = $relation->destinationContentInfo;
            $expected = $expectedRelations[$destination->id]->destinationContentInfo;
            $this->assertNotEmpty($expected, "Non expected relation with '{$destination->id}' id found");
            $this->assertEquals(
                $expected->id,
                $destination->id,
                "Expected relation with '{$expected->id}' id found '{$destination->id}' id"
            );
            $this->assertEquals(
                $expected->name,
                $destination->name,
                "Expected relation with '{$expected->name}' name found '{$destination->name}' name"
            );

            // remove from list
            unset($expectedRelations[$destination->id]);
        }

        // verify all expected relations were found
        $this->assertEquals(
            0,
            count($expectedRelations),
            "Expected to find '" . (count($expectedRelations) + count($actualRelations))
            . "' relations found '" . count($actualRelations) . "'"
        );
    }

    /**
     * Test copying Content to the authorized Location (limited by policies).
     */
    public function testCopyContentToAuthorizedLocation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $roleService = $repository->getRoleService();

        // Create and publish folders for the test case
        $folderDraft = $this->createContentDraft('folder', 2, ['name' => 'Folder1']);
        $contentService->publishVersion($folderDraft->versionInfo);
        $authorizedFolderDraft = $this->createContentDraft('folder', 2, ['name' => 'AuthorizedFolder']);
        $authorizedFolder = $contentService->publishVersion($authorizedFolderDraft->versionInfo);

        // Prepare Role for the test case
        $roleIdentifier = 'authorized_folder';
        $roleCreateStruct = $roleService->newRoleCreateStruct($roleIdentifier);
        $locationLimitation = new LocationLimitation(
            ['limitationValues' => [$authorizedFolder->contentInfo->mainLocationId]]
        );
        $roleCreateStruct->addPolicy($roleService->newPolicyCreateStruct('content', 'read'));
        $roleCreateStruct->addPolicy($roleService->newPolicyCreateStruct('content', 'versionread'));
        $roleCreateStruct->addPolicy($roleService->newPolicyCreateStruct('content', 'manage_locations'));

        $policyCreateStruct = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreateStruct->addLimitation($locationLimitation);
        $roleCreateStruct->addPolicy($policyCreateStruct);

        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);

        // Create a user with that Role
        $user = $this->createCustomUserVersion1('Users', $roleIdentifier);
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        // Test copying Content to the authorized Location
        $contentService->copyContent(
            $authorizedFolder->contentInfo,
            $locationService->newLocationCreateStruct(
                $authorizedFolder->contentInfo->mainLocationId
            )
        );
    }

    /**
     * Test copying Content to the authorized Location (limited by policies).
     */
    public function testCopyContentToAuthorizedLocationWithSubtreeLimitation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $roleService = $repository->getRoleService();

        // Create and publish folders for the test case
        $folderDraft = $this->createContentDraft('folder', 2, ['name' => 'Folder1']);
        $contentService->publishVersion($folderDraft->versionInfo);
        $authorizedFolderDraft = $this->createContentDraft('folder', 2, ['name' => 'AuthorizedFolder']);
        $authorizedFolder = $contentService->publishVersion($authorizedFolderDraft->versionInfo);

        // Prepare Role for the test case
        $roleIdentifier = 'authorized_subree';
        $subtreeLimitation = new SubtreeLimitation(
            ['limitationValues' => ['/1/2']]
        );
        $policiesData = [
            [
                'module' => 'content',
                'function' => 'read',
                'limitations' => [$subtreeLimitation],
            ],
            [
                'module' => 'content',
                'function' => 'versionread',
                'limitations' => [$subtreeLimitation],
            ],
            [
                'module' => 'content',
                'function' => 'create',
                'limitations' => [$subtreeLimitation],
            ],
            [
                'module' => 'content',
                'function' => 'manage_locations',
            ],
        ];

        $this->createRoleWithPolicies($roleIdentifier, $policiesData);

        // Create a user with that Role
        $user = $this->createCustomUserVersion1('Users', $roleIdentifier);
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        // Test copying Content to the authorized Location
        $contentService->copyContent(
            $authorizedFolder->contentInfo,
            $locationService->newLocationCreateStruct(
                $authorizedFolder->contentInfo->mainLocationId
            )
        );
    }
}
