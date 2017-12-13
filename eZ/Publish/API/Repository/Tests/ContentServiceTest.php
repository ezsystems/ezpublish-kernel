<?php

/**
 * File containing the ContentServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Exception;

/**
 * Test case for operations in the ContentService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentService
 * @group content
 */
class ContentServiceTest extends BaseContentServiceTest
{
    /**
     * Test for the newContentCreateStruct() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::newContentCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     * @group user
     * @group field-type
     */
    public function testNewContentCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Create a content type
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct', $contentCreate);
    }

    /**
     * Test for the createContent() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentCreateStruct
     * @group user
     * @group field-type
     */
    public function testCreateContent()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate->setField('name', 'My awesome forum');

        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $content = $contentService->createContent($contentCreate);
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $content);

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * Tests made for issue #EZP-20955 where Anonymous user is granted access to create content
     * and should have access to do that.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentCreateStruct
     * @group user
     * @group field-type
     */
    public function testCreateContentAndPublishWithPrivilegedAnonymousUser()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $anonymousUserId = $this->generateId('user', 10);

        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $roleService = $repository->getRoleService();

        // Give Anonymous user role additional rights
        $role = $roleService->loadRoleByIdentifier('Anonymous');
        $roleDraft = $roleService->createRoleDraft($role);
        $policyCreateStruct = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreateStruct->addLimitation(new SectionLimitation(array('limitationValues' => array(1))));
        $policyCreateStruct->addLimitation(new LocationLimitation(array('limitationValues' => array(2))));
        $policyCreateStruct->addLimitation(new ContentTypeLimitation(array('limitationValues' => array(1))));
        $roleDraft = $roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('content', 'publish');
        $policyCreateStruct->addLimitation(new SectionLimitation(array('limitationValues' => array(1))));
        $policyCreateStruct->addLimitation(new LocationLimitation(array('limitationValues' => array(2))));
        $policyCreateStruct->addLimitation(new ContentTypeLimitation(array('limitationValues' => array(1))));
        $roleDraft = $roleService->addPolicyByRoleDraft($roleDraft, $policyCreateStruct);
        $roleService->publishRoleDraft($roleDraft);

        // Set Anonymous user as current
        $repository->getPermissionResolver()->setCurrentUserReference($repository->getUserService()->loadUser($anonymousUserId));

        // Create a new content object:
        $contentCreate = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier('folder'),
            'eng-GB'
        );

        $contentCreate->setField('name', 'Folder 1');

        $content = $contentService->createContent(
            $contentCreate,
            array($locationService->newLocationCreateStruct(2))
        );

        $contentService->publishVersion(
            $content->getVersionInfo()
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentSetsContentInfo($content)
    {
        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo', $content->contentInfo);

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentSetsContentInfo
     */
    public function testCreateContentSetsExpectedContentInfo($content)
    {
        $this->assertEquals(
            array(
                $content->id,
                28, // id of content type "forum"
                true,
                1,
                'abcdef0123456789abcdef0123456789',
                'eng-US',
                $this->getRepository()->getCurrentUser()->id,
                false,
                null,
                // Main Location id for unpublished Content should be null
                null,
            ),
            array(
                $content->contentInfo->id,
                $content->contentInfo->contentTypeId,
                $content->contentInfo->alwaysAvailable,
                $content->contentInfo->currentVersionNo,
                $content->contentInfo->remoteId,
                $content->contentInfo->mainLanguageCode,
                $content->contentInfo->ownerId,
                $content->contentInfo->published,
                $content->contentInfo->publishedDate,
                $content->contentInfo->mainLocationId,
            )
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentSetsVersionInfo($content)
    {
        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo', $content->getVersionInfo());

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentSetsVersionInfo
     */
    public function testCreateContentSetsExpectedVersionInfo($content)
    {
        $this->assertEquals(
            array(
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => 1,
                'creatorId' => $this->getRepository()->getCurrentUser()->id,
                'initialLanguageCode' => 'eng-US',
            ),
            array(
                'status' => $content->getVersionInfo()->status,
                'versionNo' => $content->getVersionInfo()->versionNo,
                'creatorId' => $content->getVersionInfo()->creatorId,
                'initialLanguageCode' => $content->getVersionInfo()->initialLanguageCode,
            )
        );
        $this->assertTrue($content->getVersionInfo()->isDraft());
        $this->assertFalse($content->getVersionInfo()->isPublished());
        $this->assertFalse($content->getVersionInfo()->isArchived());
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentCreate1 = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate1->setField('name', 'An awesome Sidelfingen forum');

        $contentCreate1->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate1->alwaysAvailable = true;

        $draft = $contentService->createContent($contentCreate1);
        $contentService->publishVersion($draft->versionInfo);

        $contentCreate2 = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreate2->setField('name', 'An awesome Bielefeld forum');

        $contentCreate2->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate2->alwaysAvailable = false;

        // This call will fail with an "InvalidArgumentException", because the
        // remoteId is already in use.
        $contentService->createContent($contentCreate2);
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsInvalidArgumentExceptionOnFieldTypeNotAccept()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        // The name field does only accept strings and null as its values
        $contentCreate->setField('name', new \stdClass());

        // Throws InvalidArgumentException since the name field is filled
        // improperly
        $draft = $contentService->createContent($contentCreate);
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsContentFieldValidationException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentCreate1 = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate1->setField('name', 'An awesome Sidelfingen folder');
        // Violates string length constraint
        $contentCreate1->setField('short_name', str_repeat('a', 200));

        // Throws ContentFieldValidationException, since short_name does not pass
        // validation of the string length validator
        $draft = $contentService->createContent($contentCreate1);
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentRequiredFieldMissing()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentCreate1 = $contentService->newContentCreateStruct($contentType, 'eng-US');
        // Required field "name" is not set

        // Throws a ContentFieldValidationException, since a required field is
        // missing
        $draft = $contentService->createContent($contentCreate1);
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * NOTE: We have bidirectional dependencies between the ContentService and
     * the LocationService, so that we cannot use PHPUnit's test dependencies
     * here.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @group user
     */
    public function testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately()
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // The location will not have been created, yet, so this throws an
        // exception
        $location = $locationService->loadLocationByRemoteId(
            '0123456789abcdef0123456789abcdef'
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately
     */
    public function testCreateContentThrowsInvalidArgumentExceptionWithLocationCreateParameter()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $parentLocationId is a valid location ID

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        // Configure new locations
        $locationCreate1 = $locationService->newLocationCreateStruct($parentLocationId);

        $locationCreate1->priority = 23;
        $locationCreate1->hidden = true;
        $locationCreate1->remoteId = '0123456789abcdef0123456789aaaaaa';
        $locationCreate1->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate1->sortOrder = Location::SORT_ORDER_DESC;

        $locationCreate2 = $locationService->newLocationCreateStruct($parentLocationId);

        $locationCreate2->priority = 42;
        $locationCreate2->hidden = true;
        $locationCreate2->remoteId = '0123456789abcdef0123456789bbbbbb';
        $locationCreate2->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate2->sortOrder = Location::SORT_ORDER_DESC;

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');

        $contentCreate->setField('name', 'A awesome Sindelfingen forum');
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Create new content object under the specified location
        $draft = $contentService->createContent(
            $contentCreate,
            array($locationCreate1)
        );
        $contentService->publishVersion($draft->versionInfo);

        // This call will fail with an "InvalidArgumentException", because the
        // Content remoteId already exists,
        $contentService->createContent(
            $contentCreate,
            array($locationCreate2)
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @group user
     */
    public function testLoadContentInfo()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Media" folder
        $contentInfo = $contentService->loadContentInfo($mediaFolderId);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the returned value of the loadContentInfo() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @covers \eZ\Publish\API\Repository\ContentService::loadContentInfo
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function testLoadContentInfoSetsExpectedContentInfo(ContentInfo $contentInfo)
    {
        $this->assertPropertiesCorrectUnsorted(
            $this->getExpectedMediaContentInfoProperties(),
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentInfoThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentContentId = $this->generateId('object', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a NotFoundException
        $contentService->loadContentInfo($nonExistentContentId);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     */
    public function testLoadContentInfoByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Media" folder
        $contentInfo = $contentService->loadContentInfoByRemoteId('faaeb9be3bd98ed09f606fc16d144eca');
        /* END: Use Case */

        $this->assertInstanceOf('\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo', $contentInfo);

        return $contentInfo;
    }

    /**
     * Test for the returned value of the loadContentInfoByRemoteId() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfoByRemoteId
     * @covers \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function testLoadContentInfoByRemoteIdSetsExpectedContentInfo(ContentInfo $contentInfo)
    {
        $this->assertPropertiesCorrectUnsorted(
            [
                'id' => 10,
                'contentTypeId' => 4,
                'name' => 'Anonymous User',
                'sectionId' => 2,
                'currentVersionNo' => 2,
                'published' => true,
                'ownerId' => 14,
                'modificationDate' => $this->createDateTime(1072180405),
                'publishedDate' => $this->createDateTime(1033920665),
                'alwaysAvailable' => 1,
                'remoteId' => 'faaeb9be3bd98ed09f606fc16d144eca',
                'mainLanguageCode' => 'eng-US',
                'mainLocationId' => 45,
            ],
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfoByRemoteId
     */
    public function testLoadContentInfoByRemoteIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a NotFoundException
        $contentService->loadContentInfoByRemoteId('abcdefghijklmnopqrstuvwxyz0123456789');
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @group user
     */
    public function testLoadVersionInfo()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        // $mediaFolderId contains the ID of the "Media" folder

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Media" folder
        $contentInfo = $contentService->loadContentInfo($mediaFolderId);

        // Now load the current version info of the "Media" folder
        $versionInfo = $contentService->loadVersionInfo($contentInfo);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     */
    public function testLoadVersionInfoById()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        // $mediaFolderId contains the ID of the "Media" folder

        $contentService = $repository->getContentService();

        // Load the VersionInfo for "Media" folder
        $versionInfo = $contentService->loadVersionInfoById($mediaFolderId);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );

        return $versionInfo;
    }

    /**
     * Test for the returned value of the loadVersionInfoById() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function testLoadVersionInfoByIdSetsExpectedVersionInfo(VersionInfo $versionInfo)
    {
        $this->assertPropertiesCorrect(
            [
                'names' => [
                    'eng-US' => 'Media',
                ],
                'contentInfo' => new ContentInfo($this->getExpectedMediaContentInfoProperties()),
                'id' => 472,
                'versionNo' => 1,
                'modificationDate' => $this->createDateTime(1060695457),
                'creatorId' => 14,
                'creationDate' => $this->createDateTime(1060695450),
                'status' => VersionInfo::STATUS_PUBLISHED,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => [
                    'eng-US',
                ],
            ],
            $versionInfo
        );
        $this->assertTrue($versionInfo->isPublished());
        $this->assertFalse($versionInfo->isDraft());
        $this->assertFalse($versionInfo->isArchived());
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     */
    public function testLoadVersionInfoByIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentContentId = $this->generateId('object', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a "NotFoundException"
        $contentService->loadVersionInfoById($nonExistentContentId);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        // $mediaFolderId contains the ID of the "Media" folder

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Media" folder
        $contentInfo = $contentService->loadContentInfo($mediaFolderId);

        // Now load the current content version for the info instance
        $content = $contentService->loadContentByContentInfo($contentInfo);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        // $mediaFolderId contains the ID of the "Media" folder

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Media" folder
        $contentInfo = $contentService->loadContentInfo($mediaFolderId);

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo($contentInfo);

        // Now load the current content version for the info instance
        $content = $contentService->loadContentByVersionInfo($versionInfo);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @group user
     * @group field-type
     */
    public function testLoadContent()
    {
        $repository = $this->getRepository();

        $mediaFolderId = $this->generateId('object', 41);
        /* BEGIN: Use Case */
        // $mediaFolderId contains the ID of the "Media" folder

        $contentService = $repository->getContentService();

        // Load the Content for "Media" folder, any language and current version
        $content = $contentService->loadContent($mediaFolderId);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentContentId = $this->generateId('object', self::DB_INT_MAX);
        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a "NotFoundException"
        $contentService->loadContent($nonExistentContentId);
        /* END: Use Case */
    }

    /**
     * Data provider for testLoadContentByRemoteId().
     *
     * @return array
     */
    public function contentRemoteIdVersionLanguageProvider()
    {
        return [
            ['f5c88a2209584891056f987fd965b0ba', null, null],
            ['f5c88a2209584891056f987fd965b0ba', ['eng-US'], null],
            ['f5c88a2209584891056f987fd965b0ba', null, 1],
            ['f5c88a2209584891056f987fd965b0ba', ['eng-US'], 1],
            ['a6e35cbcb7cd6ae4b691f3eee30cd262', null, null],
            ['a6e35cbcb7cd6ae4b691f3eee30cd262', ['eng-US'], null],
            ['a6e35cbcb7cd6ae4b691f3eee30cd262', null, 1],
            ['a6e35cbcb7cd6ae4b691f3eee30cd262', ['eng-US'], 1],
        ];
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @covers \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId
     * @dataProvider contentRemoteIdVersionLanguageProvider
     *
     * @param string $remoteId
     * @param array|null $languages
     * @param int $versionNo
     */
    public function testLoadContentByRemoteId($remoteId, $languages, $versionNo)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $content = $contentService->loadContentByRemoteId($remoteId, $languages, $versionNo);

        $this->assertInstanceOf(
            Content::class,
            $content
        );

        $this->assertEquals($remoteId, $content->contentInfo->remoteId);
        if ($languages !== null) {
            $this->assertEquals($languages, $content->getVersionInfo()->languageCodes);
        }
        $this->assertEquals($versionNo ?: 1, $content->getVersionInfo()->versionNo);
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteId
     */
    public function testLoadContentByRemoteIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a "NotFoundException", because no content
        // object exists for the given remoteId
        $contentService->loadContentByRemoteId('a1b1c1d1e1f1a2b2c2d2e2f2a3b3c3d3');
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately
     * @group user
     * @group field-type
     */
    public function testPublishVersion()
    {
        $time = time();
        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();
        /* END: Use Case */

        $this->assertInstanceOf(Content::class, $content);
        $this->assertTrue($content->contentInfo->published);
        $this->assertEquals(VersionInfo::STATUS_PUBLISHED, $content->versionInfo->status);
        $this->assertGreaterThanOrEqual($time, $content->contentInfo->publishedDate->getTimestamp());
        $this->assertGreaterThanOrEqual($time, $content->contentInfo->modificationDate->getTimestamp());
        $this->assertTrue($content->versionInfo->isPublished());
        $this->assertFalse($content->versionInfo->isDraft());
        $this->assertFalse($content->versionInfo->isArchived());

        return $content;
    }

    /**
     * Test for the publishVersion() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionSetsExpectedContentInfo($content)
    {
        $this->assertEquals(
            array(
                $content->id,
                true,
                1,
                'abcdef0123456789abcdef0123456789',
                'eng-US',
                $this->getRepository()->getCurrentUser()->id,
                true,
            ),
            array(
                $content->contentInfo->id,
                $content->contentInfo->alwaysAvailable,
                $content->contentInfo->currentVersionNo,
                $content->contentInfo->remoteId,
                $content->contentInfo->mainLanguageCode,
                $content->contentInfo->ownerId,
                $content->contentInfo->published,
            )
        );

        $this->assertNotNull($content->contentInfo->mainLocationId);
        $date = new \DateTime('1984/01/01');
        $this->assertGreaterThan(
            $date->getTimestamp(),
            $content->contentInfo->publishedDate->getTimestamp()
        );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionSetsExpectedVersionInfo($content)
    {
        $this->assertEquals(
            array(
                $this->getRepository()->getCurrentUser()->id,
                'eng-US',
                VersionInfo::STATUS_PUBLISHED,
                1,
            ),
            array(
                $content->getVersionInfo()->creatorId,
                $content->getVersionInfo()->initialLanguageCode,
                $content->getVersionInfo()->status,
                $content->getVersionInfo()->versionNo,
            )
        );

        $date = new \DateTime('1984/01/01');
        $this->assertGreaterThan(
            $date->getTimestamp(),
            $content->getVersionInfo()->modificationDate->getTimestamp()
        );

        $this->assertNotNull($content->getVersionInfo()->modificationDate);
        $this->assertTrue($content->getVersionInfo()->isPublished());
        $this->assertFalse($content->getVersionInfo()->isDraft());
        $this->assertFalse($content->getVersionInfo()->isArchived());
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionCreatesLocationsDefinedOnCreate()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();
        /* END: Use Case */

        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocationByRemoteId(
            '0123456789abcdef0123456789abcdef'
        );

        $this->assertEquals(
            $location->getContentInfo(),
            $content->getVersionInfo()->getContentInfo()
        );

        return array($content, $location);
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionCreatesLocationsDefinedOnCreate
     */
    public function testCreateContentWithLocationCreateParameterCreatesExpectedLocation(array $testData)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
        /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
        list($content, $location) = $testData;

        $parentLocationId = $this->generateId('location', 56);
        $parentLocation = $this->getRepository()->getLocationService()->loadLocation($parentLocationId);
        $mainLocationId = $content->getVersionInfo()->getContentInfo()->mainLocationId;

        $this->assertPropertiesCorrect(
            array(
                'id' => $mainLocationId,
                'priority' => 23,
                'hidden' => true,
                'invisible' => true,
                'remoteId' => '0123456789abcdef0123456789abcdef',
                'parentLocationId' => $parentLocationId,
                'pathString' => $parentLocation->pathString . $mainLocationId . '/',
                'depth' => $parentLocation->depth + 1,
                'sortField' => Location::SORT_FIELD_NODE_ID,
                'sortOrder' => Location::SORT_ORDER_DESC,
            ),
            $location
        );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Publish the content draft
        $contentService->publishVersion($draft->getVersionInfo());

        // This call will fail with a "BadStateException", because the version
        // is already published.
        $contentService->publishVersion($draft->getVersionInfo());
        /* END: Use Case */
    }

    /**
     * Test that publishVersion() does not affect publishedDate (assuming previous version exists).
     *
     * @covers \eZ\Publish\API\Repository\ContentService::publishVersion
     */
    public function testPublishVersionDoesNotChangePublishedDate()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $publishedContent = $this->createContentVersion1();

        // force timestamps to differ
        sleep(1);

        $contentDraft = $contentService->createContentDraft($publishedContent->contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField('name', 'New name');
        $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $republishedContent = $contentService->publishVersion($contentDraft->versionInfo);

        $this->assertEquals(
            $publishedContent->contentInfo->publishedDate->getTimestamp(),
            $republishedContent->contentInfo->publishedDate->getTimestamp()
        );
        $this->assertGreaterThan(
            $publishedContent->contentInfo->modificationDate->getTimestamp(),
            $republishedContent->contentInfo->modificationDate->getTimestamp()
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @group user
     */
    public function testCreateContentDraft()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft($content->contentInfo);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $draftedContent
        );

        return $draftedContent;
    }

    /**
     * Test for the createContentDraft() method.
     *
     * Test that editor has access to edit own draft.
     * Note: Editors have access to version_read, which is needed to load content drafts.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @group user
     */
    public function testCreateContentDraftAndLoadAccess()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Set new editor as user
        $repository->setCurrentUser($user);

        // Create draft
        $draft = $this->createContentDraftVersion1(2, 'folder');

        // Try to load the draft
        $contentService = $repository->getContentService();
        $loadedDraft = $contentService->loadContent($draft->id);

        /* END: Use Case */

        $this->assertEquals($draft->id, $loadedDraft->id);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsExpectedProperties($draft)
    {
        $this->assertEquals(
            array(
                'fieldCount' => 2,
                'relationCount' => 0,
            ),
            array(
                'fieldCount' => count($draft->getFields()),
                'relationCount' => count($this->getRepository()->getContentService()->loadRelations($draft->getVersionInfo())),
            )
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsContentInfo($draft)
    {
        $contentInfo = $draft->contentInfo;

        $this->assertEquals(
            array(
                $draft->id,
                true,
                1,
                'eng-US',
                $this->getRepository()->getCurrentUser()->id,
                'abcdef0123456789abcdef0123456789',
                1,
            ),
            array(
                $contentInfo->id,
                $contentInfo->alwaysAvailable,
                $contentInfo->currentVersionNo,
                $contentInfo->mainLanguageCode,
                $contentInfo->ownerId,
                $contentInfo->remoteId,
                $contentInfo->sectionId,
            )
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsVersionInfo($draft)
    {
        $versionInfo = $draft->getVersionInfo();

        $this->assertEquals(
            array(
                'creatorId' => $this->getRepository()->getCurrentUser()->id,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => array(0 => 'eng-US'),
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => 2,
            ),
            array(
                'creatorId' => $versionInfo->creatorId,
                'initialLanguageCode' => $versionInfo->initialLanguageCode,
                'languageCodes' => $versionInfo->languageCodes,
                'status' => $versionInfo->status,
                'versionNo' => $versionInfo->versionNo,
            )
        );
        $this->assertTrue($versionInfo->isDraft());
        $this->assertFalse($versionInfo->isPublished());
        $this->assertFalse($versionInfo->isArchived());
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testCreateContentDraftLoadVersionInfoStillLoadsPublishedVersion($draft)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $contentService->createContentDraft($content->contentInfo);

        // This call will still load the published version
        $versionInfoPublished = $contentService->loadVersionInfo($content->contentInfo);
        /* END: Use Case */

        $this->assertEquals(1, $versionInfoPublished->versionNo);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftLoadContentStillLoadsPublishedVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $contentService->createContentDraft($content->contentInfo);

        // This call will still load the published content version
        $contentPublished = $contentService->loadContent($content->id);
        /* END: Use Case */

        $this->assertEquals(1, $contentPublished->getVersionInfo()->versionNo);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteId
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftLoadContentByRemoteIdStillLoadsPublishedVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $contentService->createContentDraft($content->contentInfo);

        // This call will still load the published content version
        $contentPublished = $contentService->loadContentByRemoteId('abcdef0123456789abcdef0123456789');
        /* END: Use Case */

        $this->assertEquals(1, $contentPublished->getVersionInfo()->versionNo);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftLoadContentByContentInfoStillLoadsPublishedVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $contentService->createContentDraft($content->contentInfo);

        // This call will still load the published content version
        $contentPublished = $contentService->loadContentByContentInfo($content->contentInfo);
        /* END: Use Case */

        $this->assertEquals(1, $contentPublished->getVersionInfo()->versionNo);
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @covers \eZ\Publish\API\Repository\ContentService::newContentUpdateStruct
     * @group user
     */
    public function testNewContentUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $updateStruct = $contentService->newContentUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            ContentUpdateStruct::class,
            $updateStruct
        );

        $this->assertPropertiesCorrect(
            [
                'initialLanguageCode' => null,
                'fields' => [],
            ],
            $updateStruct
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @group user
     * @group field-type
     */
    public function testUpdateContent()
    {
        /* BEGIN: Use Case */
        $draftVersion2 = $this->createUpdatedDraftVersion2();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $draftVersion2
        );

        $this->assertEquals(
            $this->generateId('user', 10),
            $draftVersion2->versionInfo->creatorId,
            'creatorId is not properly set on new Version'
        );

        return $draftVersion2;
    }

    /**
     * Test for the updateContent_WithDifferentUser() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @group user
     * @group field-type
     */
    public function testUpdateContentWithDifferentUser()
    {
        /* BEGIN: Use Case */
        $arrayWithDraftVersion2 = $this->createUpdatedDraftVersion2NotAdmin();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $arrayWithDraftVersion2[0]
        );

        $this->assertEquals(
            $this->generateId('user', $arrayWithDraftVersion2[1]),
            $arrayWithDraftVersion2[0]->versionInfo->creatorId,
            'creatorId is not properly set on new Version'
        );

        return $arrayWithDraftVersion2[0];
    }

    /**
     * Test for the updateContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentSetsExpectedFields($content)
    {
        $actual = $this->normalizeFields($content->getFields());

        $expected = array(
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField('title', 'An awesome² story about ezp.');
        $contentUpdateStruct->setField('title', 'An awesome²³ story about ezp.', 'eng-GB');

        $contentUpdateStruct->initialLanguageCode = 'eng-US';

        // This call will fail with a "BadStateException", because $publishedContent
        // is not a draft.
        $contentService->updateContent(
            $content->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsInvalidArgumentExceptionWhenFieldTypeDoesNotAccept()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        // The name field does not accept a stdClass object as its input
        $contentUpdateStruct->setField('name', new \stdClass(), 'eng-US');

        // Throws an InvalidArgumentException, since the value for field "name"
        // is not accepted
        $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentWhenMandatoryFieldIsEmpty()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Now create an update struct and set a mandatory field to null
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField('name', null);

        // Don't set this, then the above call without languageCode will fail
        $contentUpdateStruct->initialLanguageCode = 'eng-US';

        // This call will fail with a "ContentFieldValidationException", because the
        // mandatory "name" field is empty.
        $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsContentFieldValidationException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreate->setField('name', 'An awesome Sidelfingen folder');

        $draft = $contentService->createContent($contentCreate);

        $contentUpdate = $contentService->newContentUpdateStruct();
        // Violates string length constraint
        $contentUpdate->setField('short_name', str_repeat('a', 200), 'eng-US');

        // Throws ContentFieldValidationException because the string length
        // validation of the field "short_name" fails
        $contentService->updateContent($draft->getVersionInfo(), $contentUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentWithNotUpdatingMandatoryField()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Now create an update struct which does not overwrite mandatory
        // fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField(
            'description',
            '<?xml version="1.0" encoding="UTF-8"?><section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0"/>'
        );

        // Don't set this, then the above call without languageCode will fail
        $contentUpdateStruct->initialLanguageCode = 'eng-US';

        // This will only update the "description" field in the "eng-US"
        // language
        $updatedDraft = $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */

        foreach ($updatedDraft->getFields() as $field) {
            if ($field->languageCode === 'eng-US' && $field->fieldDefIdentifier === 'name' && $field->value !== null) {
                // Found field
                return;
            }
        }
        $this->fail(
            'Field with identifier "name" in language "eng-US" could not be found or has empty value.'
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testCreateContentDraftWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Now we create a new draft from the initial version
        $draftedContentReloaded = $contentService->createContentDraft(
            $contentVersion2->contentInfo,
            $contentVersion2->getVersionInfo()
        );
        /* END: Use Case */

        $this->assertEquals(3, $draftedContentReloaded->getVersionInfo()->versionNo);
    }

    /**
     * Test for the createContentDraft() method with third parameter.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     */
    public function testCreateContentDraftWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $content = $contentService->loadContent(4);
        $user = $this->createUserVersion1();

        $draftContent = $contentService->createContentDraft(
            $content->contentInfo,
            $content->getVersionInfo(),
            $user
        );

        $this->assertInstanceOf(
            Content::class,
            $draftContent
        );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testPublishVersionFromContentDraft()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();
        /* END: Use Case */

        $versionInfo = $contentService->loadVersionInfo($contentVersion2->contentInfo);

        $this->assertEquals(
            array(
                'status' => VersionInfo::STATUS_PUBLISHED,
                'versionNo' => 2,
            ),
            array(
                'status' => $versionInfo->status,
                'versionNo' => $versionInfo->versionNo,
            )
        );
        $this->assertTrue($versionInfo->isPublished());
        $this->assertFalse($versionInfo->isDraft());
        $this->assertFalse($versionInfo->isArchived());
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionFromContentDraftArchivesOldVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();
        /* END: Use Case */

        $versionInfo = $contentService->loadVersionInfo($contentVersion2->contentInfo, 1);

        $this->assertEquals(
            array(
                'status' => VersionInfo::STATUS_ARCHIVED,
                'versionNo' => 1,
            ),
            array(
                'status' => $versionInfo->status,
                'versionNo' => $versionInfo->versionNo,
            )
        );
        $this->assertTrue($versionInfo->isArchived());
        $this->assertFalse($versionInfo->isDraft());
        $this->assertFalse($versionInfo->isPublished());
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionFromContentDraftUpdatesContentInfoCurrentVersion()
    {
        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();
        /* END: Use Case */

        $this->assertEquals(2, $contentVersion2->contentInfo->currentVersionNo);
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionFromOldContentDraftArchivesNewerVersionNo()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Create a new draft with versionNo = 2
        $draftedContentVersion2 = $contentService->createContentDraft($content->contentInfo);

        // Create another new draft with versionNo = 3
        $draftedContentVersion3 = $contentService->createContentDraft($content->contentInfo);

        // Publish draft with versionNo = 3
        $contentService->publishVersion($draftedContentVersion3->getVersionInfo());

        // Publish the first draft with versionNo = 2
        // currentVersionNo is now 2, versionNo 3 will be archived
        $publishedDraft = $contentService->publishVersion($draftedContentVersion2->getVersionInfo());
        /* END: Use Case */

        $this->assertEquals(2, $publishedDraft->contentInfo->currentVersionNo);
    }

    /**
     * Test for the publishVersion() method, and that it creates limited archives.
     *
     * @todo Adapt this when per content type archive limited is added on repository Content Type model.
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionNotCreatingUnlimitedArchives()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $content = $this->createContentVersion1();

        // load first to make sure list gets updated also (cache)
        $versionInfoList = $contentService->loadVersions($content->contentInfo);
        $this->assertEquals(1, count($versionInfoList));
        $this->assertEquals(1, $versionInfoList[0]->versionNo);

        // Create a new draft with versionNo = 2
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        // Create a new draft with versionNo = 3
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        // Create a new draft with versionNo = 4
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        // Create a new draft with versionNo = 5
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        // Create a new draft with versionNo = 6
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        // Create a new draft with versionNo = 7
        $draftedContentVersion = $contentService->createContentDraft($content->contentInfo);
        $contentService->publishVersion($draftedContentVersion->getVersionInfo());

        $versionInfoList = $contentService->loadVersions($content->contentInfo);

        $this->assertEquals(6, count($versionInfoList));
        $this->assertEquals(2, $versionInfoList[0]->versionNo);
        $this->assertEquals(7, $versionInfoList[5]->versionNo);

        $this->assertEquals(
            [
                VersionInfo::STATUS_ARCHIVED,
                VersionInfo::STATUS_ARCHIVED,
                VersionInfo::STATUS_ARCHIVED,
                VersionInfo::STATUS_ARCHIVED,
                VersionInfo::STATUS_ARCHIVED,
                VersionInfo::STATUS_PUBLISHED,
            ],
            [
                $versionInfoList[0]->status,
                $versionInfoList[1]->status,
                $versionInfoList[2]->status,
                $versionInfoList[3]->status,
                $versionInfoList[4]->status,
                $versionInfoList[5]->status,
            ]
        );
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @covers \eZ\Publish\API\Repository\ContentService::newContentMetadataUpdateStruct
     * @group user
     */
    public function testNewContentMetadataUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Creates a new metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        foreach ($metadataUpdate as $propertyName => $propertyValue) {
            $this->assertNull($propertyValue, "Property '{$propertyName}' initial value should be null'");
        }

        $metadataUpdate->remoteId = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-GB';
        $metadataUpdate->alwaysAvailable = false;
        /* END: Use Case */

        $this->assertInstanceOf(
            ContentMetadataUpdateStruct::class,
            $metadataUpdate
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentMetadataUpdateStruct
     * @group user
     */
    public function testUpdateContentMetadata()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Creates a metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        $metadataUpdate->remoteId = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-GB';
        $metadataUpdate->alwaysAvailable = false;
        $metadataUpdate->publishedDate = $this->createDateTime(441759600); // 1984/01/01
        $metadataUpdate->modificationDate = $this->createDateTime(441759600); // 1984/01/01

        // Update the metadata of the published content object
        $content = $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );

        return $content;
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataSetsExpectedProperties($content)
    {
        $contentInfo = $content->contentInfo;

        $this->assertEquals(
            array(
                'remoteId' => 'aaaabbbbccccddddeeeeffff11112222',
                'sectionId' => $this->generateId('section', 1),
                'alwaysAvailable' => false,
                'currentVersionNo' => 1,
                'mainLanguageCode' => 'eng-GB',
                'modificationDate' => $this->createDateTime(441759600),
                'ownerId' => $this->getRepository()->getCurrentUser()->id,
                'published' => true,
                'publishedDate' => $this->createDateTime(441759600),
            ),
            array(
                'remoteId' => $contentInfo->remoteId,
                'sectionId' => $contentInfo->sectionId,
                'alwaysAvailable' => $contentInfo->alwaysAvailable,
                'currentVersionNo' => $contentInfo->currentVersionNo,
                'mainLanguageCode' => $contentInfo->mainLanguageCode,
                'modificationDate' => $contentInfo->modificationDate,
                'ownerId' => $contentInfo->ownerId,
                'published' => $contentInfo->published,
                'publishedDate' => $contentInfo->publishedDate,
            )
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataNotUpdatesContentVersion($content)
    {
        $this->assertEquals(1, $content->getVersionInfo()->versionNo);
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionOnDuplicateRemoteId()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $content = $this->createContentVersion1();

        // Creates a metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->remoteId = $mediaRemoteId;

        // This call will fail with an "InvalidArgumentException", because the
        // specified remoteId is already used by the "Media" page.
        $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionOnNoMetadataPropertiesSet()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentInfo = $contentService->loadContentInfo(4);
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();

        // Throws an exception because no properties are set in $contentMetadataUpdateStruct
        $contentService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testDeleteContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Load the locations for this content object
        $locations = $locationService->loadLocations($contentVersion2->contentInfo);

        // This will delete the content, all versions and the associated locations
        $contentService->deleteContent($contentVersion2->contentInfo);
        /* END: Use Case */

        foreach ($locations as $location) {
            $locationService->loadLocation($location->id);
        }
    }

    /**
     * Test for the deleteContent() method.
     *
     * Test for issue EZP-21057:
     * "contentService: Unable to delete a content with an empty file attribute"
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testDeleteContentWithEmptyBinaryField()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion = $this->createContentVersion1EmptyBinaryField();

        // Load the locations for this content object
        $locations = $locationService->loadLocations($contentVersion->contentInfo);

        // This will delete the content, all versions and the associated locations
        $contentService->deleteContent($contentVersion->contentInfo);
        /* END: Use Case */

        foreach ($locations as $location) {
            $locationService->loadLocation($location->id);
        }
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     */
    public function testLoadContentDraftsReturnsEmptyArrayByDefault()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $contentDrafts = $contentService->loadContentDrafts();
        /* END: Use Case */

        $this->assertSame(array(), $contentDrafts);
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testLoadContentDrafts()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $contentService = $repository->getContentService();

        // "Media" content object
        $mediaContentInfo = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // "eZ Publish Demo Design ..." content object
        $demoDesignContentInfo = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Create some drafts
        $contentService->createContentDraft($mediaContentInfo);
        $contentService->createContentDraft($demoDesignContentInfo);

        // Now $contentDrafts should contain two drafted versions
        $draftedVersions = $contentService->loadContentDrafts();
        /* END: Use Case */

        $actual = array(
            $draftedVersions[0]->status,
            $draftedVersions[0]->getContentInfo()->remoteId,
            $draftedVersions[1]->status,
            $draftedVersions[1]->getContentInfo()->remoteId,
        );
        sort($actual, SORT_STRING);

        $this->assertEquals(
            array(
                VersionInfo::STATUS_DRAFT,
                VersionInfo::STATUS_DRAFT,
                $demoDesignRemoteId,
                $mediaRemoteId,
            ),
            $actual
        );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts($user)
     */
    public function testLoadContentDraftsWithFirstParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        // Get current user
        $oldCurrentUser = $repository->getCurrentUser();

        // Set new editor as user
        $repository->setCurrentUser($user);

        // Remote id of the "Media" content object in an eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $contentService = $repository->getContentService();

        // "Media" content object
        $mediaContentInfo = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Create a content draft
        $contentService->createContentDraft($mediaContentInfo);

        // Reset to previous current user
        $repository->setCurrentUser($oldCurrentUser);

        // Now $contentDrafts for the previous current user and the new user
        $newCurrentUserDrafts = $contentService->loadContentDrafts($user);
        $oldCurrentUserDrafts = $contentService->loadContentDrafts($oldCurrentUser);
        /* END: Use Case */

        $this->assertSame(array(), $oldCurrentUserDrafts);

        $this->assertEquals(
            array(
                VersionInfo::STATUS_DRAFT,
                $mediaRemoteId,
            ),
            array(
                $newCurrentUserDrafts[0]->status,
                $newCurrentUserDrafts[0]->getContentInfo()->remoteId,
            )
        );
        $this->assertTrue($newCurrentUserDrafts[0]->isDraft());
        $this->assertFalse($newCurrentUserDrafts[0]->isArchived());
        $this->assertFalse($newCurrentUserDrafts[0]->isPublished());
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadVersionInfoWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $publishedContent = $this->createContentVersion1();

        $draftContent = $contentService->createContentDraft($publishedContent->contentInfo);

        // Will return the VersionInfo of the $draftContent
        $versionInfo = $contentService->loadVersionInfoById($publishedContent->id, 2);
        /* END: Use Case */

        $this->assertEquals(2, $versionInfo->versionNo);

        // Check that ContentInfo contained in VersionInfo has correct main Location id set
        $this->assertEquals(
            $publishedContent->getVersionInfo()->getContentInfo()->mainLocationId,
            $versionInfo->getContentInfo()->mainLocationId
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoWithSecondParameter
     */
    public function testLoadVersionInfoThrowsNotFoundExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // This call will fail with a "NotFoundException", because not versionNo
        // 2 exists for this content object.
        $contentService->loadVersionInfo($draft->contentInfo, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoWithSecondParameter
     */
    public function testLoadVersionInfoByIdWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $publishedContent = $this->createContentVersion1();

        $draftContent = $contentService->createContentDraft($publishedContent->contentInfo);

        // Will return the VersionInfo of the $draftContent
        $versionInfo = $contentService->loadVersionInfoById($publishedContent->id, 2);
        /* END: Use Case */

        $this->assertEquals(2, $versionInfo->versionNo);

        // Check that ContentInfo contained in VersionInfo has correct main Location id set
        $this->assertEquals(
            $publishedContent->getVersionInfo()->getContentInfo()->mainLocationId,
            $versionInfo->getContentInfo()->mainLocationId
        );

        return [
            'versionInfo' => $versionInfo,
            'draftContent' => $draftContent,
        ];
    }

    /**
     * Test for the returned value of the loadVersionInfoById() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoByIdWithSecondParameter
     * @covers \eZ\Publish\API\Repository\ContentService::loadVersionInfoById
     *
     * @param array $data
     */
    public function testLoadVersionInfoByIdWithSecondParameterSetsExpectedVersionInfo(array $data)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo */
        $versionInfo = $data['versionInfo'];
        /** @var \eZ\Publish\API\Repository\Values\Content\Content $draftContent */
        $draftContent = $data['draftContent'];

        $this->assertPropertiesCorrect(
            [
                'names' => [
                    'eng-US' => 'An awesome forum',
                ],
                'contentInfo' => new ContentInfo([
                    'id' => $draftContent->contentInfo->id,
                    'contentTypeId' => 28,
                    'name' => 'An awesome forum',
                    'sectionId' => 1,
                    'currentVersionNo' => 1,
                    'published' => true,
                    'ownerId' => 14,
                    // this Content Object is created at the test runtime
                    'modificationDate' => $versionInfo->contentInfo->modificationDate,
                    'publishedDate' => $versionInfo->contentInfo->publishedDate,
                    'alwaysAvailable' => 1,
                    'remoteId' => 'abcdef0123456789abcdef0123456789',
                    'mainLanguageCode' => 'eng-US',
                    'mainLocationId' => $draftContent->contentInfo->mainLocationId,
                ]),
                'id' => $draftContent->versionInfo->id,
                'versionNo' => 2,
                'creatorId' => 14,
                'status' => 0,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => [
                    'eng-US',
                ],
            ],
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionInfoByIdThrowsNotFoundExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "NotFoundException", because not versionNo
        // 2 exists for this content object.
        $contentService->loadVersionInfoById($content->id, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfo
     */
    public function testLoadContentByVersionInfoWithSecondParameter()
    {
        $repository = $this->getRepository();

        $sectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');

        $contentCreateStruct->setField('name', 'Sindelfingen forum²');

        $contentCreateStruct->setField('name', 'Sindelfingen forum²³', 'eng-GB');

        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId contains the ID of section 1
        $contentCreateStruct->sectionId = $sectionId;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent($contentCreateStruct);

        // Now publish this draft
        $publishedContent = $contentService->publishVersion($content->getVersionInfo());

        // Will return a content instance with fields in "eng-US"
        $reloadedContent = $contentService->loadContentByVersionInfo(
            $publishedContent->getVersionInfo(),
            array(
                'eng-GB',
            ),
            false
        );
        /* END: Use Case */

        $actual = array();
        foreach ($reloadedContent->getFields() as $field) {
            $actual[] = new Field(
                array(
                    'id' => 0,
                    'value' => ($field->value !== null ? true : null), // Actual value tested by FieldType integration tests
                    'languageCode' => $field->languageCode,
                    'fieldDefIdentifier' => $field->fieldDefIdentifier,
                )
            );
        }
        usort(
            $actual,
            function ($field1, $field2) {
                if (0 === ($return = strcasecmp($field1->fieldDefIdentifier, $field2->fieldDefIdentifier))) {
                    return strcasecmp($field1->languageCode, $field2->languageCode);
                }

                return $return;
            }
        );

        $expected = array(
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'description',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'name',
                )
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoWithLanguageParameters()
    {
        $repository = $this->getRepository();

        $sectionId = $this->generateId('section', 1);
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');

        $contentCreateStruct->setField('name', 'Sindelfingen forum²');

        $contentCreateStruct->setField('name', 'Sindelfingen forum²³', 'eng-GB');

        $contentCreateStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId contains the ID of section 1
        $contentCreateStruct->sectionId = $sectionId;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent($contentCreateStruct);

        // Now publish this draft
        $publishedContent = $contentService->publishVersion($content->getVersionInfo());

        // Will return a content instance with fields in "eng-US"
        $reloadedContent = $contentService->loadContentByContentInfo(
            $publishedContent->contentInfo,
            array(
                'eng-US',
            ),
            null,
            false
        );
        /* END: Use Case */

        $actual = $this->normalizeFields($reloadedContent->getFields());

        $expected = array(
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );

        $this->assertEquals($expected, $actual);

        // Will return a content instance with fields in "eng-GB" (versions prior to 6.0.0-beta9 returned "eng-US" also)
        $reloadedContent = $contentService->loadContentByContentInfo(
            $publishedContent->contentInfo,
            array(
                'eng-GB',
            ),
            null,
            true
        );

        $actual = $this->normalizeFields($reloadedContent->getFields());

        $expected = array(
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );

        $this->assertEquals($expected, $actual);

        // Will return a content instance with fields in main language "eng-US", as "fre-FR" does not exists
        $reloadedContent = $contentService->loadContentByContentInfo(
            $publishedContent->contentInfo,
            array(
                'fre-FR',
            ),
            null,
            true
        );

        $actual = $this->normalizeFields($reloadedContent->getFields());

        $expected = array(
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => true,
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoWithVersionNumberParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $publishedContent = $this->createContentVersion1();

        $draftContent = $contentService->createContentDraft($publishedContent->contentInfo);

        // This content instance is identical to $draftContent
        $draftContentReloaded = $contentService->loadContentByContentInfo(
            $publishedContent->contentInfo,
            null,
            2
        );
        /* END: Use Case */

        $this->assertEquals(
            2,
            $draftContentReloaded->getVersionInfo()->versionNo
        );

        // Check that ContentInfo contained in reloaded draft Content has correct main Location id set
        $this->assertEquals(
            $publishedContent->versionInfo->contentInfo->mainLocationId,
            $draftContentReloaded->versionInfo->contentInfo->mainLocationId
        );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithVersionNumberParameter
     */
    public function testLoadContentByContentInfoThrowsNotFoundExceptionWithVersionNumberParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "NotFoundException", because no content
        // with versionNo = 2 exists.
        $contentService->loadContentByContentInfo($content->contentInfo, null, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // This draft contains those fields localized with "eng-GB"
        $draftLocalized = $contentService->loadContent($draft->id, array('eng-GB'), null, false);
        /* END: Use Case */

        $this->assertLocaleFieldsEquals($draftLocalized->getFields(), 'eng-GB');

        return $draft;
    }

    /**
     * Test for the loadContent() method using undefined translation.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithSecondParameter
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $contentDraft
     */
    public function testLoadContentWithSecondParameterThrowsNotFoundException(Content $contentDraft)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentService->loadContent($contentDraft->id, array('ger-DE'), null, false);
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $publishedContent = $this->createContentVersion1();

        $draftContent = $contentService->createContentDraft($publishedContent->contentInfo);

        // This content instance is identical to $draftContent
        $draftContentReloaded = $contentService->loadContent($publishedContent->id, null, 2);
        /* END: Use Case */

        $this->assertEquals(2, $draftContentReloaded->getVersionInfo()->versionNo);

        // Check that ContentInfo contained in reloaded draft Content has correct main Location id set
        $this->assertEquals(
            $publishedContent->versionInfo->contentInfo->mainLocationId,
            $draftContentReloaded->versionInfo->contentInfo->mainLocationId
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentWithThirdParameter
     */
    public function testLoadContentThrowsNotFoundExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "NotFoundException", because for this
        // content object no versionNo=2 exists.
        $contentService->loadContent($content->id, null, 2);
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentByRemoteIdWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        $contentService->publishVersion($draft->versionInfo);

        // This draft contains those fields localized with "eng-GB"
        $draftLocalized = $contentService->loadContentByRemoteId(
            $draft->contentInfo->remoteId,
            array('eng-GB'),
            null,
            false
        );
        /* END: Use Case */

        $this->assertLocaleFieldsEquals($draftLocalized->getFields(), 'eng-GB');
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentByRemoteIdWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $publishedContent = $this->createContentVersion1();

        $draftContent = $contentService->createContentDraft($publishedContent->contentInfo);

        // This content instance is identical to $draftContent
        $draftContentReloaded = $contentService->loadContentByRemoteId(
            $publishedContent->contentInfo->remoteId,
            null,
            2
        );
        /* END: Use Case */

        $this->assertEquals(2, $draftContentReloaded->getVersionInfo()->versionNo);

        // Check that ContentInfo contained in reloaded draft Content has correct main Location id set
        $this->assertEquals(
            $publishedContent->versionInfo->contentInfo->mainLocationId,
            $draftContentReloaded->versionInfo->contentInfo->mainLocationId
        );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByRemoteIdWithThirdParameter
     */
    public function testLoadContentByRemoteIdThrowsNotFoundExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "NotFoundException", because for this
        // content object no versionNo=2 exists.
        $contentService->loadContentByRemoteId(
            $content->contentInfo->remoteId,
            null,
            2
        );
        /* END: Use Case */
    }

    /**
     * Test that retrieval of translated name field respects prioritized language list.
     *
     * @dataProvider getPrioritizedLanguageList
     * @param string[]|null $languageCodes
     */
    public function testLoadContentWithPrioritizedLanguagesList($languageCodes)
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $content = $this->createContentVersion2();

        $content = $contentService->loadContent($content->id, $languageCodes);

        $expectedName = $content->getVersionInfo()->getName(
            isset($languageCodes[0]) ? $languageCodes[0] : null
        );
        $nameValue = $content->getFieldValue('name');
        /** @var \eZ\Publish\Core\FieldType\TextLine\Value $nameValue */
        self::assertEquals($expectedName, $nameValue->text);
        self::assertEquals($expectedName, $content->getVersionInfo()->getName());
        // Also check value on shortcut method on content
        self::assertEquals($expectedName, $content->getName());
    }

    /**
     * @return array
     */
    public function getPrioritizedLanguageList()
    {
        return [
            [['eng-US']],
            [['eng-GB']],
            [['eng-GB', 'eng-US']],
            [['eng-US', 'eng-GB']],
        ];
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testDeleteVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Create new draft, because published or last version of the Content can't be deleted
        $draft = $contentService->createContentDraft(
            $content->getVersionInfo()->getContentInfo()
        );

        // Delete the previously created draft
        $contentService->deleteVersion($draft->getVersionInfo());
        /* END: Use Case */

        $versions = $contentService->loadVersions($content->getVersionInfo()->getContentInfo());

        $this->assertCount(1, $versions);
        $this->assertEquals(
            $content->getVersionInfo()->id,
            $versions[0]->id
        );
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testDeleteVersionThrowsBadStateExceptionOnPublishedVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "BadStateException", because the content
        // version is currently published.
        $contentService->deleteVersion($content->getVersionInfo());
        /* END: Use Case */
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testDeleteVersionThrowsBadStateExceptionOnLastVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // This call will fail with a "BadStateException", because the Content
        // version is the last version of the Content.
        $contentService->deleteVersion($draft->getVersionInfo());
        /* END: Use Case */
    }

    /**
     * Test for the loadVersions() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public function testLoadVersions()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Load versions of this ContentInfo instance
        $versions = $contentService->loadVersions($contentVersion2->contentInfo);
        /* END: Use Case */

        $expectedVersionsOrder = [
            $contentService->loadVersionInfo($contentVersion2->contentInfo, 1),
            $contentService->loadVersionInfo($contentVersion2->contentInfo, 2),
        ];

        $this->assertEquals($expectedVersionsOrder, $versions);

        return $versions;
    }

    /**
     * Test for the loadVersions() method.
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersions
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo[] $versions
     */
    public function testLoadVersionsSetsExpectedVersionInfo(array $versions)
    {
        $this->assertCount(2, $versions);

        $expectedVersions = [
            [
                'versionNo' => 1,
                'creatorId' => 14,
                'status' => VersionInfo::STATUS_ARCHIVED,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US'],
            ],
            [
                'versionNo' => 2,
                'creatorId' => 10,
                'status' => VersionInfo::STATUS_PUBLISHED,
                'initialLanguageCode' => 'eng-US',
                'languageCodes' => ['eng-US', 'eng-GB'],
            ],
        ];

        $this->assertPropertiesCorrect($expectedVersions[0], $versions[0]);
        $this->assertPropertiesCorrect($expectedVersions[1], $versions[1]);
        $this->assertEquals(
            $versions[0]->creationDate->getTimestamp(),
            $versions[1]->creationDate->getTimestamp(),
            'Creation time did not match within delta of 2 seconds',
            2
        );
        $this->assertEquals(
            $versions[0]->modificationDate->getTimestamp(),
            $versions[1]->modificationDate->getTimestamp(),
            'Creation time did not match within delta of 2 seconds',
            2
        );
        $this->assertTrue($versions[0]->isArchived());
        $this->assertFalse($versions[0]->isDraft());
        $this->assertFalse($versions[0]->isPublished());

        $this->assertTrue($versions[1]->isPublished());
        $this->assertFalse($versions[1]->isDraft());
        $this->assertFalse($versions[1]->isArchived());
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     * @group field-type
     */
    public function testCopyContent()
    {
        $parentLocationId = $this->generateId('location', 56);

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createMultipleLanguageContentVersion2();

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Copy content with all versions and drafts
        $contentCopied = $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $contentCopied
        );

        $this->assertNotEquals(
            $contentVersion2->contentInfo->remoteId,
            $contentCopied->contentInfo->remoteId
        );

        $this->assertNotEquals(
            $contentVersion2->id,
            $contentCopied->id
        );

        $this->assertEquals(
            2,
            count($contentService->loadVersions($contentCopied->contentInfo))
        );

        $this->assertEquals(2, $contentCopied->getVersionInfo()->versionNo);

        $this->assertAllFieldsEquals($contentCopied->getFields());

        $this->assertDefaultContentStates($contentCopied->contentInfo);

        $this->assertNotNull(
            $contentCopied->contentInfo->mainLocationId,
            'Expected main location to be set given we provided a LocationCreateStruct'
        );
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     *
     * @todo Fix to more descriptive name
     */
    public function testCopyContentWithThirdParameter()
    {
        $parentLocationId = $this->generateId('location', 56);

        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        $targetLocationCreate->priority = 42;
        $targetLocationCreate->hidden = true;
        $targetLocationCreate->remoteId = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Copy only the initial version
        $contentCopied = $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate,
            $contentService->loadVersionInfo($contentVersion2->contentInfo, 1)
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $contentCopied
        );

        $this->assertNotEquals(
            $contentVersion2->contentInfo->remoteId,
            $contentCopied->contentInfo->remoteId
        );

        $this->assertNotEquals(
            $contentVersion2->id,
            $contentCopied->id
        );

        $this->assertEquals(
            1,
            count($contentService->loadVersions($contentCopied->contentInfo))
        );

        $this->assertEquals(1, $contentCopied->getVersionInfo()->versionNo);

        $this->assertNotNull(
            $contentCopied->contentInfo->mainLocationId,
            'Expected main location to be set given we provided a LocationCreateStruct'
        );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testAddRelation()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Media" content of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $draft = $this->createContentDraftVersion1();

        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Create relation between new content object and "Media" page
        $relation = $contentService->addRelation(
            $draft->getVersionInfo(),
            $media
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Relation',
            $relation
        );

        return $contentService->loadRelations($draft->getVersionInfo());
    }

    /**
     * Test for the addRelation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     *
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationAddsRelationToContent($relations)
    {
        $this->assertEquals(
            1,
            count($relations)
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     */
    protected function assertExpectedRelations($relations)
    {
        $this->assertEquals(
            array(
                'type' => Relation::COMMON,
                'sourceFieldDefinitionIdentifier' => null,
                'sourceContentInfo' => 'abcdef0123456789abcdef0123456789',
                'destinationContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
            ),
            array(
                'type' => $relations[0]->type,
                'sourceFieldDefinitionIdentifier' => $relations[0]->sourceFieldDefinitionIdentifier,
                'sourceContentInfo' => $relations[0]->sourceContentInfo->remoteId,
                'destinationContentInfo' => $relations[0]->destinationContentInfo->remoteId,
            )
        );
    }

    /**
     * Test for the addRelation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     *
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationSetsExpectedRelations($relations)
    {
        $this->assertExpectedRelations($relations);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelationSetsExpectedRelations
     */
    public function testCreateContentDraftWithRelations()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        // RemoteId of the "Media" content of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $draft = $this->createContentDraftVersion1();
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Create relation between new content object and "Media" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $media
        );

        $content = $contentService->publishVersion($draft->versionInfo);
        $newDraft = $contentService->createContentDraft($content->contentInfo);

        return $contentService->loadRelations($newDraft->getVersionInfo());
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraftWithRelations
     */
    public function testCreateContentDraftWithRelationsCreatesRelations($relations)
    {
        $this->assertEquals(
            1,
            count($relations)
        );

        return $relations;
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Relation[] $relations
     *
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraftWithRelationsCreatesRelations
     */
    public function testCreateContentDraftWithRelationsCreatesExpectedRelations($relations)
    {
        $this->assertExpectedRelations($relations);
    }

    /**
     * Test for the addRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $content = $this->createContentVersion1();

        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // This call will fail with a "BadStateException", because content is
        // published and not a draft.
        $contentService->addRelation(
            $content->getVersionInfo(),
            $media
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testLoadRelations()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $draft = $this->createContentDraftVersion1();

        // Load other content objects
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);
        $demoDesign = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Create relation between new content object and "Media" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $media
        );

        // Create another relation with the "Demo Design" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $demoDesign
        );

        // Load all relations
        $relations = $contentService->loadRelations($draft->getVersionInfo());
        /* END: Use Case */

        usort(
            $relations,
            function ($rel1, $rel2) {
                return strcasecmp(
                    $rel2->getDestinationContentInfo()->remoteId,
                    $rel1->getDestinationContentInfo()->remoteId
                );
            }
        );

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => 'abcdef0123456789abcdef0123456789',
                    'destinationContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                ),
                array(
                    'sourceContentInfo' => 'abcdef0123456789abcdef0123456789',
                    'destinationContentInfo' => '8b8b22fe3c6061ed500fbd2b377b885f',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $relations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $relations[0]->destinationContentInfo->remoteId,
                ),
                array(
                    'sourceContentInfo' => $relations[1]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $relations[1]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the loadRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testLoadRelationsSkipsArchivedContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $draft = $this->createContentDraftVersion1();

        // Load other content objects
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);
        $demoDesign = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Create relation between new content object and "Media" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $media
        );

        // Create another relation with the "Demo Design" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $demoDesign
        );

        $demoDesignLocation = $locationService->loadLocation($demoDesign->mainLocationId);

        // Trashing Content's last Location will change its status to archived,
        // in this case relation towards it will not be loaded.
        $trashService->trash($demoDesignLocation);

        // Load all relations
        $relations = $contentService->loadRelations($draft->getVersionInfo());
        /* END: Use Case */

        $this->assertCount(1, $relations);
        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => 'abcdef0123456789abcdef0123456789',
                    'destinationContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $relations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $relations[0]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the loadRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testLoadRelationsSkipsDraftContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $draft = $this->createContentDraftVersion1();

        // Load other content objects
        $media = $contentService->loadContentByRemoteId($mediaRemoteId);
        $demoDesign = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Create draft of "Media" page
        $mediaDraft = $contentService->createContentDraft($media->contentInfo);

        // Create relation between "Media" page and new content object draft.
        // This relation will not be loaded before the draft is published.
        $contentService->addRelation(
            $mediaDraft->getVersionInfo(),
            $draft->getVersionInfo()->getContentInfo()
        );

        // Create another relation with the "Demo Design" page
        $contentService->addRelation(
            $mediaDraft->getVersionInfo(),
            $demoDesign
        );

        // Load all relations
        $relations = $contentService->loadRelations($mediaDraft->getVersionInfo());
        /* END: Use Case */

        $this->assertCount(1, $relations);
        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                    'destinationContentInfo' => '8b8b22fe3c6061ed500fbd2b377b885f',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $relations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $relations[0]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testLoadReverseRelations()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $versionInfo = $this->createContentVersion1()->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        // Create some drafts
        $mediaDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId($mediaRemoteId)
        );
        $demoDesignDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId($demoDesignRemoteId)
        );

        // Create relation between new content object and "Media" page
        $relation1 = $contentService->addRelation(
            $mediaDraft->getVersionInfo(),
            $contentInfo
        );

        // Create another relation with the "Demo Design" page
        $relation2 = $contentService->addRelation(
            $demoDesignDraft->getVersionInfo(),
            $contentInfo
        );

        // Publish drafts, so relations become active
        $contentService->publishVersion($mediaDraft->getVersionInfo());
        $contentService->publishVersion($demoDesignDraft->getVersionInfo());

        // Load all relations
        $relations = $contentService->loadRelations($versionInfo);
        $reverseRelations = $contentService->loadReverseRelations($contentInfo);
        /* END: Use Case */

        $this->assertEquals($contentInfo->id, $relation1->getDestinationContentInfo()->id);
        $this->assertEquals($mediaDraft->id, $relation1->getSourceContentInfo()->id);

        $this->assertEquals($contentInfo->id, $relation2->getDestinationContentInfo()->id);
        $this->assertEquals($demoDesignDraft->id, $relation2->getSourceContentInfo()->id);

        $this->assertEquals(0, count($relations));
        $this->assertEquals(2, count($reverseRelations));

        usort(
            $reverseRelations,
            function ($rel1, $rel2) {
                return strcasecmp(
                    $rel2->getSourceContentInfo()->remoteId,
                    $rel1->getSourceContentInfo()->remoteId
                );
            }
        );

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                    'destinationContentInfo' => 'abcdef0123456789abcdef0123456789',
                ),
                array(
                    'sourceContentInfo' => '8b8b22fe3c6061ed500fbd2b377b885f',
                    'destinationContentInfo' => 'abcdef0123456789abcdef0123456789',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $reverseRelations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $reverseRelations[0]->destinationContentInfo->remoteId,
                ),
                array(
                    'sourceContentInfo' => $reverseRelations[1]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $reverseRelations[1]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadReverseRelations
     */
    public function testLoadReverseRelationsSkipsArchivedContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $versionInfo = $this->createContentVersion1()->getVersionInfo();
        $contentInfo = $versionInfo->getContentInfo();

        // Create some drafts
        $mediaDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId($mediaRemoteId)
        );
        $demoDesignDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId($demoDesignRemoteId)
        );

        // Create relation between new content object and "Media" page
        $relation1 = $contentService->addRelation(
            $mediaDraft->getVersionInfo(),
            $contentInfo
        );

        // Create another relation with the "Demo Design" page
        $relation2 = $contentService->addRelation(
            $demoDesignDraft->getVersionInfo(),
            $contentInfo
        );

        // Publish drafts, so relations become active
        $contentService->publishVersion($mediaDraft->getVersionInfo());
        $contentService->publishVersion($demoDesignDraft->getVersionInfo());

        $demoDesignLocation = $locationService->loadLocation($demoDesignDraft->contentInfo->mainLocationId);

        // Trashing Content's last Location will change its status to archived,
        // in this case relation from it will not be loaded.
        $trashService->trash($demoDesignLocation);

        // Load all relations
        $relations = $contentService->loadRelations($versionInfo);
        $reverseRelations = $contentService->loadReverseRelations($contentInfo);
        /* END: Use Case */

        $this->assertEquals($contentInfo->id, $relation1->getDestinationContentInfo()->id);
        $this->assertEquals($mediaDraft->id, $relation1->getSourceContentInfo()->id);

        $this->assertEquals($contentInfo->id, $relation2->getDestinationContentInfo()->id);
        $this->assertEquals($demoDesignDraft->id, $relation2->getSourceContentInfo()->id);

        $this->assertEquals(0, count($relations));
        $this->assertEquals(1, count($reverseRelations));

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                    'destinationContentInfo' => 'abcdef0123456789abcdef0123456789',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $reverseRelations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $reverseRelations[0]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadReverseRelations
     */
    public function testLoadReverseRelationsSkipsDraftContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "eZ Publish Demo Design ..." page
        // of a eZ Publish demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        // Load "Media" page Content
        $media = $contentService->loadContentByRemoteId($mediaRemoteId);

        // Create some drafts
        $newDraftVersionInfo = $this->createContentDraftVersion1()->getVersionInfo();
        $demoDesignDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId($demoDesignRemoteId)
        );

        // Create relation between "Media" page and new content object
        $relation1 = $contentService->addRelation(
            $newDraftVersionInfo,
            $media->contentInfo
        );

        // Create another relation with the "Demo Design" page
        $relation2 = $contentService->addRelation(
            $demoDesignDraft->getVersionInfo(),
            $media->contentInfo
        );

        // Publish drafts, so relations become active
        $contentService->publishVersion($demoDesignDraft->getVersionInfo());
        // We will not publish new Content draft, therefore relation from it
        // will not be loaded as reverse relation for "Media" page
        //$contentService->publishVersion( $newDraftVersionInfo );

        // Load all relations
        $relations = $contentService->loadRelations($media->versionInfo);
        $reverseRelations = $contentService->loadReverseRelations($media->contentInfo);
        /* END: Use Case */

        $this->assertEquals($media->contentInfo->id, $relation1->getDestinationContentInfo()->id);
        $this->assertEquals($newDraftVersionInfo->contentInfo->id, $relation1->getSourceContentInfo()->id);

        $this->assertEquals($media->contentInfo->id, $relation2->getDestinationContentInfo()->id);
        $this->assertEquals($demoDesignDraft->id, $relation2->getSourceContentInfo()->id);

        $this->assertEquals(0, count($relations));
        $this->assertEquals(1, count($reverseRelations));

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo' => '8b8b22fe3c6061ed500fbd2b377b885f',
                    'destinationContentInfo' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
                ),
            ),
            array(
                array(
                    'sourceContentInfo' => $reverseRelations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo' => $reverseRelations[0]->destinationContentInfo->remoteId,
                ),
            )
        );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testDeleteRelation()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Media" and the "Demo Design" page of a eZ Publish
        // demo installation.
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';
        $demoDesignRemoteId = '8b8b22fe3c6061ed500fbd2b377b885f';

        $draft = $this->createContentDraftVersion1();

        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);
        $demoDesign = $contentService->loadContentInfoByRemoteId($demoDesignRemoteId);

        // Establish some relations
        $contentService->addRelation($draft->getVersionInfo(), $media);
        $contentService->addRelation($draft->getVersionInfo(), $demoDesign);

        // Delete one of the currently created relations
        $contentService->deleteRelation($draft->getVersionInfo(), $media);

        // The relations array now contains only one element
        $relations = $contentService->loadRelations($draft->getVersionInfo());
        /* END: Use Case */

        $this->assertEquals(1, count($relations));
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $content = $this->createContentVersion1();

        // Load the destination object
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // Create a new draft
        $draftVersion2 = $contentService->createContentDraft($content->contentInfo);

        // Add a relation
        $contentService->addRelation($draftVersion2->getVersionInfo(), $media);

        // Publish new version
        $contentVersion2 = $contentService->publishVersion(
            $draftVersion2->getVersionInfo()
        );

        // This call will fail with a "BadStateException", because content is
        // published and not a draft.
        $contentService->deleteRelation(
            $contentVersion2->getVersionInfo(),
            $media
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Media" page of an eZ Publish demo installation
        $mediaRemoteId = 'a6e35cbcb7cd6ae4b691f3eee30cd262';

        $draft = $this->createContentDraftVersion1();

        // Load the destination object
        $media = $contentService->loadContentInfoByRemoteId($mediaRemoteId);

        // This call will fail with a "InvalidArgumentException", because no
        // relation exists between $draft and $media.
        $contentService->deleteRelation(
            $draft->getVersionInfo(),
            $media
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testCreateContentInTransactionWithRollback()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        // Start a transaction
        $repository->beginTransaction();

        try {
            $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

            // Get a content create struct and set mandatory properties
            $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
            $contentCreate->setField('name', 'Sindelfingen forum');

            $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
            $contentCreate->alwaysAvailable = true;

            // Create a new content object
            $contentId = $contentService->createContent($contentCreate)->id;
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $contentService->loadContent($contentId);
        } catch (NotFoundException $e) {
            // This is expected
            return;
        }
        /* END: Use Case */

        $this->fail('Content object still exists after rollback.');
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testCreateContentInTransactionWithCommit()
    {
        if ($this->isVersion4()) {
            $this->markTestSkipped('This test requires eZ Publish 5');
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();

        // Start a transaction
        $repository->beginTransaction();

        try {
            $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');

            // Get a content create struct and set mandatory properties
            $contentCreate = $contentService->newContentCreateStruct($contentType, 'eng-US');
            $contentCreate->setField('name', 'Sindelfingen forum');

            $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
            $contentCreate->alwaysAvailable = true;

            // Create a new content object
            $contentId = $contentService->createContent($contentCreate)->id;

            // Commit changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load the new content object
        $content = $contentService->loadContent($contentId);
        /* END: Use Case */

        $this->assertEquals($contentId, $content->id);
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentThrowsNotFoundException
     */
    public function testCreateContentWithLocationCreateParameterInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Start a transaction
        $repository->beginTransaction();

        try {
            $draft = $this->createContentDraftVersion1();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $contentId = $draft->id;

        // Roleback the transaction
        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $contentService->loadContent($contentId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Can still load content object after rollback.');
    }

    /**
     * Test for the createContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithLocationCreateParameterDoesNotCreateLocationImmediately
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentThrowsNotFoundException
     */
    public function testCreateContentWithLocationCreateParameterInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Start a transaction
        $repository->beginTransaction();

        try {
            $draft = $this->createContentDraftVersion1();

            $contentId = $draft->id;

            // Roleback the transaction
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load the new content object
        $content = $contentService->loadContent($contentId);
        /* END: Use Case */

        $this->assertEquals($contentId, $content->id);
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testCreateContentDraftInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Load the user group content object
        $content = $contentService->loadContent($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Create a new draft
            $drafted = $contentService->createContentDraft($content->contentInfo);

            // Store version number for later reuse
            $versionNo = $drafted->versionInfo->versionNo;
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback
        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $contentService->loadContent($contentId, null, $versionNo);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Can still load content draft after rollback');
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testCreateContentDraftInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Load the user group content object
        $content = $contentService->loadContent($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Create a new draft
            $drafted = $contentService->createContentDraft($content->contentInfo);

            // Store version number for later reuse
            $versionNo = $drafted->versionInfo->versionNo;

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $content = $contentService->loadContent($contentId, null, $versionNo);
        /* END: Use Case */

        $this->assertEquals(
            $versionNo,
            $content->getVersionInfo()->versionNo
        );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testPublishVersionInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Load the user group content object
        $content = $contentService->loadContent($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            $draftVersion = $contentService->createContentDraft($content->contentInfo)->getVersionInfo();

            // Publish a new version
            $content = $contentService->publishVersion($draftVersion);

            // Store version number for later reuse
            $versionNo = $content->versionInfo->versionNo;
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback
        $repository->rollback();

        try {
            // This call will fail with a "NotFoundException"
            $contentService->loadContent($contentId, null, $versionNo);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Can still load content draft after rollback');
    }

    /**
     * Test for the publishVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testPublishVersionInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // ID of the "Administrator users" user group
        $contentId = 12;

        // Get the content service
        $contentService = $repository->getContentService();

        // Load the user group content object
        $template = $contentService->loadContent($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Publish a new version
            $content = $contentService->publishVersion(
                $contentService->createContentDraft($template->contentInfo)->getVersionInfo()
            );

            // Store version number for later reuse
            $versionNo = $content->versionInfo->versionNo;

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load current version info
        $versionInfo = $contentService->loadVersionInfo($content->contentInfo);
        /* END: Use Case */

        $this->assertEquals($versionNo, $versionInfo->versionNo);
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testUpdateContentInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Load content service
        $contentService = $repository->getContentService();

        // Create a new user group draft
        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo($contentId)
        );

        // Get an update struct and change the group name
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('name', 'Administrators', 'eng-US');

        // Start a transaction
        $repository->beginTransaction();

        try {
            // Update the group name
            $draft = $contentService->updateContent(
                $draft->getVersionInfo(),
                $contentUpdate
            );

            // Publish updated version
            $contentService->publishVersion($draft->getVersionInfo());
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes.
        $repository->rollback();

        // Name will still be "Administrator users"
        $name = $contentService->loadContent($contentId)->getFieldValue('name');
        /* END: Use Case */

        $this->assertEquals('Administrator users', $name);
    }

    /**
     * Test for the updateContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testUpdateContentInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Load content service
        $contentService = $repository->getContentService();

        // Create a new user group draft
        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo($contentId)
        );

        // Get an update struct and change the group name
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('name', 'Administrators', 'eng-US');

        // Start a transaction
        $repository->beginTransaction();

        try {
            // Update the group name
            $draft = $contentService->updateContent(
                $draft->getVersionInfo(),
                $contentUpdate
            );

            // Publish updated version
            $contentService->publishVersion($draft->getVersionInfo());

            // Commit all changes.
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Name is now "Administrators"
        $name = $contentService->loadContent($contentId)->getFieldValue('name', 'eng-US');
        /* END: Use Case */

        $this->assertEquals('Administrators', $name);
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testUpdateContentMetadataInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Load a ContentInfo object
        $contentInfo = $contentService->loadContentInfo($contentId);

        // Store remoteId for later testing
        $remoteId = $contentInfo->remoteId;

        // Start a transaction
        $repository->beginTransaction();

        try {
            // Get metadata update struct and change remoteId
            $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
            $metadataUpdate->remoteId = md5(microtime(true));

            // Update the metadata of the published content object
            $contentService->updateContentMetadata(
                $contentInfo,
                $metadataUpdate
            );
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes.
        $repository->rollback();

        // Load current remoteId
        $remoteIdReloaded = $contentService->loadContentInfo($contentId)->remoteId;
        /* END: Use Case */

        $this->assertEquals($remoteId, $remoteIdReloaded);
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testUpdateContentMetadataInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Load a ContentInfo object
        $contentInfo = $contentService->loadContentInfo($contentId);

        // Store remoteId for later testing
        $remoteId = $contentInfo->remoteId;

        // Start a transaction
        $repository->beginTransaction();

        try {
            // Get metadata update struct and change remoteId
            $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
            $metadataUpdate->remoteId = md5(microtime(true));

            // Update the metadata of the published content object
            $contentService->updateContentMetadata(
                $contentInfo,
                $metadataUpdate
            );

            // Commit all changes.
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load current remoteId
        $remoteIdReloaded = $contentService->loadContentInfo($contentId)->remoteId;
        /* END: Use Case */

        $this->assertNotEquals($remoteId, $remoteIdReloaded);
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testDeleteVersionInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Create a new draft
            $draft = $contentService->createContentDraft(
                $contentService->loadContentInfo($contentId)
            );

            $contentService->deleteVersion($draft->getVersionInfo());
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes.
        $repository->rollback();

        // This array will be empty
        $drafts = $contentService->loadContentDrafts();
        /* END: Use Case */

        $this->assertSame(array(), $drafts);
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentDrafts
     */
    public function testDeleteVersionInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 12);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Administrator users" user group

        // Get the content service
        $contentService = $repository->getContentService();

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Create a new draft
            $draft = $contentService->createContentDraft(
                $contentService->loadContentInfo($contentId)
            );

            $contentService->deleteVersion($draft->getVersionInfo());

            // Commit all changes.
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // This array will contain no element
        $drafts = $contentService->loadContentDrafts();
        /* END: Use Case */

        $this->assertSame(array(), $drafts);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testDeleteContentInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 11);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Members" user group in an eZ Publish
        // demo installation

        // Get content service
        $contentService = $repository->getContentService();

        // Load a ContentInfo instance
        $contentInfo = $contentService->loadContentInfo($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Delete content object
            $contentService->deleteContent($contentInfo);
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // This call will return the original content object
        $contentInfo = $contentService->loadContentInfo($contentId);
        /* END: Use Case */

        $this->assertEquals($contentId, $contentInfo->id);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testDeleteContentInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 11);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Members" user group in an eZ Publish
        // demo installation

        // Get content service
        $contentService = $repository->getContentService();

        // Load a ContentInfo instance
        $contentInfo = $contentService->loadContentInfo($contentId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Delete content object
            $contentService->deleteContent($contentInfo);

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Deleted content info is not found anymore
        try {
            $contentService->loadContentInfo($contentId);
        } catch (NotFoundException $e) {
            return;
        }
        /* END: Use Case */

        $this->fail('Can still load ContentInfo after commit.');
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testCopyContentInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 11);
        $locationId = $this->generateId('location', 13);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Members" user group in an eZ Publish
        // demo installation

        // $locationId is the ID of the "Administrator users" group location

        // Get services
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // Load content object to copy
        $content = $contentService->loadContent($contentId);

        // Create new target location
        $locationCreate = $locationService->newLocationCreateStruct($locationId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Copy content with all versions and drafts
            $contentService->copyContent(
                $content->contentInfo,
                $locationCreate
            );
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        $this->refreshSearch($repository);

        // This array will only contain a single admin user object
        $locations = $locationService->loadLocationChildren(
            $locationService->loadLocation($locationId)
        )->locations;
        /* END: Use Case */

        $this->assertEquals(1, count($locations));
    }

    /**
     * Test for the copyContent() method.
     *
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testCopyContentInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('object', 11);
        $locationId = $this->generateId('location', 13);
        /* BEGIN: Use Case */
        // $contentId is the ID of the "Members" user group in an eZ Publish
        // demo installation

        // $locationId is the ID of the "Administrator users" group location

        // Get services
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // Load content object to copy
        $content = $contentService->loadContent($contentId);

        // Create new target location
        $locationCreate = $locationService->newLocationCreateStruct($locationId);

        // Start a new transaction
        $repository->beginTransaction();

        try {
            // Copy content with all versions and drafts
            $contentCopied = $contentService->copyContent(
                $content->contentInfo,
                $locationCreate
            );

            // Commit all changes
            $repository->commit();
        } catch (Exception $e) {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $this->refreshSearch($repository);

        // This will contain the admin user and the new child location
        $locations = $locationService->loadLocationChildren(
            $locationService->loadLocation($locationId)
        )->locations;
        /* END: Use Case */

        $this->assertEquals(2, count($locations));
    }

    public function testURLAliasesCreatedForNewContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Automatically creates a new URLAlias for the content
        $liveContent = $contentService->publishVersion($draft->getVersionInfo());
        /* END: Use Case */

        $location = $locationService->loadLocation(
            $liveContent->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $aliases = $urlAliasService->listLocationAliases($location, false);

        $this->assertAliasesCorrect(
            array(
                '/Design/Plain-site/An-awesome-forum' => array(
                    'type' => URLAlias::LOCATION,
                    'destination' => $location->id,
                    'path' => '/Design/Plain-site/An-awesome-forum',
                    'languageCodes' => array('eng-US'),
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ),
            ),
            $aliases
        );
    }

    public function testURLAliasesCreatedForUpdatedContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        /* BEGIN: Use Case */
        $draft = $this->createUpdatedDraftVersion2();

        $location = $locationService->loadLocation(
            $draft->getVersionInfo()->getContentInfo()->mainLocationId
        );

        // Load and assert URL aliases before publishing updated Content, so that
        // SPI cache is warmed up and cache invalidation is also tested.
        $aliases = $urlAliasService->listLocationAliases($location, false);

        $this->assertAliasesCorrect(
            array(
                '/Design/Plain-site/An-awesome-forum' => array(
                    'type' => URLAlias::LOCATION,
                    'destination' => $location->id,
                    'path' => '/Design/Plain-site/An-awesome-forum',
                    'languageCodes' => array('eng-US'),
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ),
            ),
            $aliases
        );

        // Automatically marks old aliases for the content as history
        // and creates new aliases, based on the changes
        $liveContent = $contentService->publishVersion($draft->getVersionInfo());
        /* END: Use Case */

        $location = $locationService->loadLocation(
            $liveContent->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $aliases = $urlAliasService->listLocationAliases($location, false);

        $this->assertAliasesCorrect(
            array(
                '/Design/Plain-site/An-awesome-forum2' => array(
                    'type' => URLAlias::LOCATION,
                    'destination' => $location->id,
                    'path' => '/Design/Plain-site/An-awesome-forum2',
                    'languageCodes' => array('eng-US'),
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ),
                '/Design/Plain-site/An-awesome-forum23' => array(
                    'type' => URLAlias::LOCATION,
                    'destination' => $location->id,
                    'path' => '/Design/Plain-site/An-awesome-forum23',
                    'languageCodes' => array('eng-GB'),
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ),
            ),
            $aliases
        );
    }

    public function testCustomURLAliasesNotHistorizedOnUpdatedContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $content = $this->createContentVersion1();

        // Create a custom URL alias
        $urlAliasService->createUrlAlias(
            $locationService->loadLocation(
                $content->getVersionInfo()->getContentInfo()->mainLocationId
            ),
            '/my/fancy/story-about-ez-publish',
            'eng-US'
        );

        $draftVersion2 = $contentService->createContentDraft($content->contentInfo);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->initialLanguageCode = 'eng-US';
        $contentUpdate->setField('name', 'Amazing Bielefeld forum');

        $draftVersion2 = $contentService->updateContent(
            $draftVersion2->getVersionInfo(),
            $contentUpdate
        );

        // Only marks auto-generated aliases as history
        // the custom one is left untouched
        $liveContent = $contentService->publishVersion($draftVersion2->getVersionInfo());
        /* END: Use Case */

        $location = $locationService->loadLocation(
            $liveContent->getVersionInfo()->getContentInfo()->mainLocationId
        );

        $aliases = $urlAliasService->listLocationAliases($location);

        $this->assertAliasesCorrect(
            array(
                '/my/fancy/story-about-ez-publish' => array(
                    'type' => URLAlias::LOCATION,
                    'destination' => $location->id,
                    'path' => '/my/fancy/story-about-ez-publish',
                    'languageCodes' => array('eng-US'),
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                    'alwaysAvailable' => false,
                ),
            ),
            $aliases
        );
    }

    /**
     * Test to ensure that old versions are not affected by updates to newer
     * drafts.
     */
    public function testUpdatingDraftDoesNotUpdateOldVersions()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentVersion2 = $this->createContentVersion2();

        $loadedContent1 = $contentService->loadContent($contentVersion2->id, null, 1);
        $loadedContent2 = $contentService->loadContent($contentVersion2->id, null, 2);

        $this->assertNotEquals(
            $loadedContent1->getFieldValue('name', 'eng-US'),
            $loadedContent2->getFieldValue('name', 'eng-US')
        );
    }

    /**
     * Test scenario with writer and publisher users.
     * Writer can only create content. Publisher can publish this content.
     */
    public function testPublishWorkflow()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $this->createRoleWithPolicies('Publisher', [
            ['content', 'read'],
            ['content', 'create'],
            ['content', 'publish'],
        ]);

        $this->createRoleWithPolicies('Writer', [
            ['content', 'read'],
            ['content', 'create'],
        ]);

        $writerUser = $this->createCustomUserWithLogin(
            'writer',
            'writer@example.com',
            'Writers',
            'Writer'
        );

        $publisherUser = $this->createCustomUserWithLogin(
            'publisher',
            'publisher@example.com',
            'Publishers',
            'Publisher'
        );

        $repository->getPermissionResolver()->setCurrentUserReference($writerUser);
        $draft = $this->createContentDraftVersion1();

        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        $content = $contentService->publishVersion($draft->versionInfo);

        $contentService->loadContent($content->id);
    }

    /**
     * Test publish / content policy is required to be able to publish content.
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     * @expectedExceptionMessageRegExp /User does not have access to 'publish' 'content'/
     */
    public function testPublishContentWithoutPublishPolicyThrowsException()
    {
        $repository = $this->getRepository();

        $this->createRoleWithPolicies('Writer', [
            ['content', 'read'],
            ['content', 'create'],
            ['content', 'edit'],
        ]);
        $writerUser = $this->createCustomUserWithLogin(
            'writer',
            'writer@example.com',
            'Writers',
            'Writer'
        );
        $repository->getPermissionResolver()->setCurrentUserReference($writerUser);

        $this->createContentVersion1();
    }

    /**
     * Test removal of the specific translation from all the Versions of a Content Object.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     */
    public function testDeleteTranslation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $content = $this->createContentVersion2();

        // create multiple versions to exceed archive limit
        for ($i = 0; $i < 5; ++$i) {
            $contentDraft = $contentService->createContentDraft($content->contentInfo);
            $contentUpdateStruct = $contentService->newContentUpdateStruct();
            $contentDraft = $contentService->updateContent(
                $contentDraft->versionInfo,
                $contentUpdateStruct
            );
            $contentService->publishVersion($contentDraft->versionInfo);
        }

        $contentService->deleteTranslation($content->contentInfo, 'eng-GB');

        $this->assertTranslationDoesNotExist('eng-GB', $content->id);
    }

    /**
     * Test deleting a Translation which is initial for some Version, updates initialLanguageCode
     * with mainLanguageCode (assuming they are different).
     */
    public function testDeleteTranslationUpdatesInitialLanguageCodeVersion()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $content = $this->createContentVersion2();
        // create another, copied, version
        $contentDraft = $contentService->updateContent(
            $contentService->createContentDraft($content->contentInfo)->versionInfo,
            $contentService->newContentUpdateStruct()
        );
        $publishedContent = $contentService->publishVersion($contentDraft->versionInfo);

        // remove first version with only one translation as it is not the subject of this test
        $contentService->deleteVersion(
            $contentService->loadVersionInfo($publishedContent->contentInfo, 1)
        );

        // sanity check
        self::assertEquals('eng-US', $content->contentInfo->mainLanguageCode);
        self::assertEquals('eng-US', $content->versionInfo->initialLanguageCode);

        // update mainLanguageCode so it is different than initialLanguageCode for Version
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->mainLanguageCode = 'eng-GB';
        $content = $contentService->updateContentMetadata($publishedContent->contentInfo, $contentMetadataUpdateStruct);

        $contentService->deleteTranslation($content->contentInfo, 'eng-US');

        $this->assertTranslationDoesNotExist('eng-US', $content->id);
    }

    /**
     * Test removal of the specific translation properly updates languages of the URL alias.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     */
    public function testDeleteTranslationUpdatesUrlAlias()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        $content = $this->createContentVersion2();
        $mainLocation = $locationService->loadLocation($content->contentInfo->mainLocationId);

        // create custom URL alias for Content main Location
        $urlAliasService->createUrlAlias($mainLocation, '/my-custom-url', 'eng-GB');

        // create secondary Location for Content
        $secondaryLocation = $locationService->createLocation(
            $content->contentInfo,
            $locationService->newLocationCreateStruct(2)
        );

        // create custom URL alias for Content secondary Location
        $urlAliasService->createUrlAlias($secondaryLocation, '/my-secondary-url', 'eng-GB');

        // delete Translation
        $contentService->deleteTranslation($content->contentInfo, 'eng-GB');

        foreach ([$mainLocation, $secondaryLocation] as $location) {
            // check auto-generated URL aliases
            foreach ($urlAliasService->listLocationAliases($location, false) as $alias) {
                self::assertNotContains('eng-GB', $alias->languageCodes);
            }

            // check custom URL aliases
            foreach ($urlAliasService->listLocationAliases($location) as $alias) {
                self::assertNotContains('eng-GB', $alias->languageCodes);
            }
        }
    }

    /**
     * Test removal of a main translation throws BadStateException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Specified translation is the main translation of the Content Object
     */
    public function testDeleteTranslationMainLanguageThrowsBadStateException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $content = $this->createContentVersion2();

        // delete first version which has only one translation
        $contentService->deleteVersion($contentService->loadVersionInfo($content->contentInfo, 1));

        // try to delete main translation
        $contentService->deleteTranslation($content->contentInfo, $content->contentInfo->mainLanguageCode);
    }

    /**
     * Test removal of a Translation is possible when some archived Versions have only this Translation.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     */
    public function testDeleteTranslationDeletesSingleTranslationVersions()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        // content created by the createContentVersion1 method has eng-US translation only.
        $content = $this->createContentVersion1();

        // create new version and add eng-GB translation
        $contentDraft = $contentService->createContentDraft($content->contentInfo);
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField('name', 'Awesome Board', 'eng-GB');
        $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $publishedContent = $contentService->publishVersion($contentDraft->versionInfo);

        // update mainLanguageCode to avoid exception related to that
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->mainLanguageCode = 'eng-GB';

        $content = $contentService->updateContentMetadata($publishedContent->contentInfo, $contentMetadataUpdateStruct);

        $contentService->deleteTranslation($content->contentInfo, 'eng-US');

        $this->assertTranslationDoesNotExist('eng-US', $content->id);
    }

    /**
     * Test removal of the translation by the user who is not allowed to delete a content
     * throws UnauthorizedException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @expectedExceptionMessage User does not have access to 'remove' 'content'
     */
    public function testDeleteTranslationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $content = $this->createContentVersion2();

        // create user that can read/create/edit but cannot delete content
        $this->createRoleWithPolicies('Writer', [
            ['content', 'read'],
            ['content', 'versionread'],
            ['content', 'create'],
            ['content', 'edit'],
        ]);
        $writerUser = $this->createCustomUserWithLogin(
            'writer',
            'writer@example.com',
            'Writers',
            'Writer'
        );
        $repository->getPermissionResolver()->setCurrentUserReference($writerUser);
        $contentService->deleteTranslation($content->contentInfo, 'eng-GB');
    }

    /**
     * Test removal of a non-existent translation throws InvalidArgumentException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$languageCode' is invalid: ger-DE does not exist in the Content item
     */
    public function testDeleteTranslationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        // content created by the createContentVersion1 method has eng-US translation only.
        $content = $this->createContentVersion1();
        $contentService->deleteTranslation($content->contentInfo, 'ger-DE');
    }

    /**
     * Test deleting a Translation from Draft.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     */
    public function testDeleteTranslationFromDraft()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $languageCode = 'eng-GB';
        $content = $this->createMultipleLanguageContentVersion2();
        $draft = $contentService->createContentDraft($content->contentInfo);
        $draft = $contentService->deleteTranslationFromDraft($draft->versionInfo, $languageCode);
        $content = $contentService->publishVersion($draft->versionInfo);

        $loadedContent = $contentService->loadContent($content->id);
        self::assertNotContains($languageCode, $loadedContent->versionInfo->languageCodes);
        self::assertEmpty($loadedContent->getFieldsByLanguage($languageCode));
    }

    /**
     * Get values for multilingual field.
     *
     * @return array
     */
    public function providerForDeleteTranslationFromDraftRemovesUrlAliasOnPublishing()
    {
        return [
            [
                ['eng-US' => 'US Name', 'eng-GB' => 'GB Name'],
            ],
            [
                ['eng-US' => 'Same Name', 'eng-GB' => 'Same Name'],
            ],
        ];
    }

    /**
     * Test deleting a Translation from Draft removes previously stored URL aliases for published Content.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     *
     * @dataProvider providerForDeleteTranslationFromDraftRemovesUrlAliasOnPublishing
     *
     * @param string[] $fieldValues translated field values
     */
    public function testDeleteTranslationFromDraftRemovesUrlAliasOnPublishing(array $fieldValues)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $urlAliasService = $repository->getURLAliasService();

        // set language code to be removed
        $languageCode = 'eng-GB';
        $draft = $this->createMultilingualContentDraft(
            'folder',
            2,
            'eng-US',
            [
                'name' => [
                    'eng-GB' => $fieldValues['eng-GB'],
                    'eng-US' => $fieldValues['eng-US'],
                ],
            ]
        );
        $content = $contentService->publishVersion($draft->versionInfo);

        // create secondary location
        $locationService->createLocation(
            $content->contentInfo,
            $locationService->newLocationCreateStruct(5)
        );

        // sanity check
        $locations = $locationService->loadLocations($content->contentInfo);
        self::assertCount(2, $locations, 'Sanity check: Expected to find 2 Locations');
        foreach ($locations as $location) {
            $urlAliasService->createUrlAlias($location, '/us-custom_' . $location->id, 'eng-US');
            $urlAliasService->createUrlAlias($location, '/gb-custom_' . $location->id, 'eng-GB');

            // check default URL aliases
            $aliases = $urlAliasService->listLocationAliases($location, false, $languageCode);
            self::assertNotEmpty($aliases, 'Sanity check: URL alias for the translation does not exist');

            // check custom URL aliases
            $aliases = $urlAliasService->listLocationAliases($location, true, $languageCode);
            self::assertNotEmpty($aliases, 'Sanity check: Custom URL alias for the translation does not exist');
        }

        // delete translation and publish new version
        $draft = $contentService->createContentDraft($content->contentInfo);
        $draft = $contentService->deleteTranslationFromDraft($draft->versionInfo, $languageCode);
        $contentService->publishVersion($draft->versionInfo);

        // check that aliases does not exist
        foreach ($locations as $location) {
            // check default URL aliases
            $aliases = $urlAliasService->listLocationAliases($location, false, $languageCode);
            self::assertEmpty($aliases, 'URL alias for the deleted translation still exists');

            // check custom URL aliases
            $aliases = $urlAliasService->listLocationAliases($location, true, $languageCode);
            self::assertEmpty($aliases, 'Custom URL alias for the deleted translation still exists');
        }
    }

    /**
     * Test deleting a Translation from Draft which has single Translation throws BadStateException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Specified Translation is the only one Content Object Version has
     */
    public function testDeleteTranslationFromDraftThrowsBadStateExceptionOnSingleTranslation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        // create Content with single Translation
        $publishedContent = $contentService->publishVersion(
            $this->createContentDraft(
                'forum',
                2,
                ['name' => 'Eng-US Version name']
            )->versionInfo
        );

        // update mainLanguageCode to avoid exception related to trying to delete main Translation
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->mainLanguageCode = 'eng-GB';
        $publishedContent = $contentService->updateContentMetadata(
            $publishedContent->contentInfo,
            $contentMetadataUpdateStruct
        );

        // create single Translation Version from the first one
        $draft = $contentService->createContentDraft(
            $publishedContent->contentInfo,
            $publishedContent->versionInfo
        );

        // attempt to delete Translation
        $contentService->deleteTranslationFromDraft($draft->versionInfo, 'eng-US');
    }

    /**
     * Test deleting the Main Translation from Draft throws BadStateException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Specified Translation is the main Translation of the Content Object
     */
    public function testDeleteTranslationFromDraftThrowsBadStateExceptionOnMainTranslation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $mainLanguageCode = 'eng-US';
        $draft = $this->createMultilingualContentDraft(
            'forum',
            2,
            $mainLanguageCode,
            [
                'name' => [
                    'eng-US' => 'An awesome eng-US forum',
                    'eng-GB' => 'An awesome eng-GB forum',
                ],
            ]
        );
        $contentService->deleteTranslationFromDraft($draft->versionInfo, $mainLanguageCode);
    }

    /**
     * Test deleting the Translation from Published Version throws BadStateException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @expectedExceptionMessage Version is not a draft
     */
    public function testDeleteTranslationFromDraftThrowsBadStateExceptionOnPublishedVersion()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $languageCode = 'eng-US';
        $content = $this->createMultipleLanguageContentVersion2();
        $draft = $contentService->createContentDraft($content->contentInfo);
        $publishedContent = $contentService->publishVersion($draft->versionInfo);
        $contentService->deleteTranslationFromDraft($publishedContent->versionInfo, $languageCode);
    }

    /**
     * Test deleting a Translation from Draft throws UnauthorizedException if user cannot edit Content.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @expectedExceptionMessage User does not have access to 'edit' 'content'
     */
    public function testDeleteTranslationFromDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $languageCode = 'eng-GB';
        $content = $this->createMultipleLanguageContentVersion2();
        $draft = $contentService->createContentDraft($content->contentInfo);

        // create user that can read/create/delete but cannot edit or content
        $this->createRoleWithPolicies('Writer', [
            ['content', 'read'],
            ['content', 'versionread'],
            ['content', 'create'],
            ['content', 'delete'],
        ]);
        $writerUser = $this->createCustomUserWithLogin(
            'user',
            'user@example.com',
            'Writers',
            'Writer'
        );
        $repository->getPermissionResolver()->setCurrentUserReference($writerUser);

        $contentService->deleteTranslationFromDraft($draft->versionInfo, $languageCode);
    }

    /**
     * Test deleting a non-existent Translation from Draft throws InvalidArgumentException.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteTranslationFromDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessageRegExp /The Version \(ContentId=\d+, VersionNo=\d+\) is not translated into ger-DE/
     */
    public function testDeleteTranslationFromDraftThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $languageCode = 'ger-DE';
        $content = $this->createMultipleLanguageContentVersion2();
        $draft = $contentService->createContentDraft($content->contentInfo);
        $contentService->deleteTranslationFromDraft($draft->versionInfo, $languageCode);
    }

    /**
     * Simplify creating custom role with limited set of policies.
     *
     * @param $roleName
     * @param array $policies e.g. [ ['content', 'create'], ['content', 'edit'], ]
     */
    private function createRoleWithPolicies($roleName, array $policies)
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        $roleCreateStruct = $roleService->newRoleCreateStruct($roleName);
        foreach ($policies as $policy) {
            $policyCreateStruct = $roleService->newPolicyCreateStruct($policy[0], $policy[1]);
            $roleCreateStruct->addPolicy($policyCreateStruct);
        }

        $roleDraft = $roleService->createRole($roleCreateStruct);
        $roleService->publishRoleDraft($roleDraft);
    }

    /**
     * Asserts that all aliases defined in $expectedAliasProperties with the
     * given properties are available in $actualAliases and not more.
     *
     * @param array $expectedAliasProperties
     * @param array $actualAliases
     */
    private function assertAliasesCorrect(array $expectedAliasProperties, array $actualAliases)
    {
        foreach ($actualAliases as $actualAlias) {
            if (!isset($expectedAliasProperties[$actualAlias->path])) {
                $this->fail(
                    sprintf(
                        'Alias with path "%s" in languages "%s" not expected.',
                        $actualAlias->path,
                        implode(', ', $actualAlias->languageCodes)
                    )
                );
            }

            foreach ($expectedAliasProperties[$actualAlias->path] as $propertyName => $propertyValue) {
                $this->assertEquals(
                    $propertyValue,
                    $actualAlias->$propertyName,
                    sprintf(
                        'Property $%s incorrect on alias with path "%s" in languages "%s".',
                        $propertyName,
                        $actualAlias->path,
                        implode(', ', $actualAlias->languageCodes)
                    )
                );
            }

            unset($expectedAliasProperties[$actualAlias->path]);
        }

        if (!empty($expectedAliasProperties)) {
            $this->fail(
                sprintf(
                    'Missing expected aliases with paths "%s".',
                    implode('", "', array_keys($expectedAliasProperties))
                )
            );
        }
    }

    /**
     * Asserts that the given fields are equal to the default fields fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     */
    private function assertAllFieldsEquals(array $fields)
    {
        $actual = $this->normalizeFields($fields);
        $expected = $this->normalizeFields($this->createFieldsFixture());

        $this->assertEquals($expected, $actual);
    }

    /**
     * Asserts that the given fields are equal to a language filtered set of the
     * default fields fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @param string $languageCode
     */
    private function assertLocaleFieldsEquals(array $fields, $languageCode)
    {
        $actual = $this->normalizeFields($fields);

        $expected = array();
        foreach ($this->normalizeFields($this->createFieldsFixture()) as $field) {
            if ($field->languageCode !== $languageCode) {
                continue;
            }
            $expected[] = $field;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * This method normalizes a set of fields and returns a normalized set.
     *
     * Normalization means it resets the storage specific field id to zero and
     * it sorts the field by their identifier and their language code. In
     * addition, the field value is removed, since this one depends on the
     * specific FieldType, which is tested in a dedicated integration test.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function normalizeFields(array $fields)
    {
        $normalized = array();
        foreach ($fields as $field) {
            $normalized[] = new Field(
                array(
                    'id' => 0,
                    'value' => ($field->value !== null ? true : null),
                    'languageCode' => $field->languageCode,
                    'fieldDefIdentifier' => $field->fieldDefIdentifier,
                    'fieldTypeIdentifier' => $field->fieldTypeIdentifier,
                )
            );
        }
        usort(
            $normalized,
            function ($field1, $field2) {
                if (0 === ($return = strcasecmp($field1->fieldDefIdentifier, $field2->fieldDefIdentifier))) {
                    return strcasecmp($field1->languageCode, $field2->languageCode);
                }

                return $return;
            }
        );

        return $normalized;
    }

    /**
     * Returns a filtered set of the default fields fixture.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function createLocaleFieldsFixture($languageCode)
    {
        $fields = array();
        foreach ($this->createFieldsFixture() as $field) {
            if (null === $field->languageCode || $languageCode === $field->languageCode) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Asserts that given Content has default ContentStates.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    private function assertDefaultContentStates(ContentInfo $contentInfo)
    {
        $repository = $this->getRepository();
        $objectStateService = $repository->getObjectStateService();

        $objectStateGroups = $objectStateService->loadObjectStateGroups();

        foreach ($objectStateGroups as $objectStateGroup) {
            $contentState = $objectStateService->getContentState($contentInfo, $objectStateGroup);
            foreach ($objectStateService->loadObjectStates($objectStateGroup) as $objectState) {
                // Only check the first object state which is the default one.
                $this->assertEquals(
                    $objectState,
                    $contentState
                );
                break;
            }
        }
    }

    /**
     * Assert that given Content has no references to a translation specified by the $languageCode.
     *
     * @param string $languageCode
     * @param int $contentId
     */
    private function assertTranslationDoesNotExist($languageCode, $contentId)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $content = $contentService->loadContent($contentId);

        foreach ($content->fields as $fieldIdentifier => $field) {
            /** @var array $field */
            self::assertArrayNotHasKey($languageCode, $field);
            self::assertNotEquals($languageCode, $content->contentInfo->mainLanguageCode);
            self::assertArrayNotHasKey($languageCode, $content->versionInfo->getNames());
            self::assertNotEquals($languageCode, $content->versionInfo->initialLanguageCode);
            self::assertNotContains($languageCode, $content->versionInfo->languageCodes);
        }
        foreach ($contentService->loadVersions($content->contentInfo) as $versionInfo) {
            self::assertArrayNotHasKey($languageCode, $versionInfo->getNames());
            self::assertNotEquals($languageCode, $versionInfo->contentInfo->mainLanguageCode);
            self::assertNotEquals($languageCode, $versionInfo->initialLanguageCode);
            self::assertNotContains($languageCode, $versionInfo->languageCodes);
        }
    }

    /**
     * Returns the default fixture of fields used in most tests.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function createFieldsFixture()
    {
        return array(
            new Field(
                array(
                    'id' => 0,
                    'value' => 'Foo',
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => 'Bar',
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'description',
                    'fieldTypeIdentifier' => 'ezrichtext',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => 'An awesome multi-lang forum²',
                    'languageCode' => 'eng-US',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
            new Field(
                array(
                    'id' => 0,
                    'value' => 'An awesome multi-lang forum²³',
                    'languageCode' => 'eng-GB',
                    'fieldDefIdentifier' => 'name',
                    'fieldTypeIdentifier' => 'ezstring',
                )
            ),
        );
    }

    /**
     * Gets expected property values for the "Media" ContentInfo ValueObject.
     *
     * @return array
     */
    private function getExpectedMediaContentInfoProperties()
    {
        return [
            'id' => 41,
            'contentTypeId' => 1,
            'name' => 'Media',
            'sectionId' => 3,
            'currentVersionNo' => 1,
            'published' => true,
            'ownerId' => 14,
            'modificationDate' => $this->createDateTime(1060695457),
            'publishedDate' => $this->createDateTime(1060695457),
            'alwaysAvailable' => 1,
            'remoteId' => 'a6e35cbcb7cd6ae4b691f3eee30cd262',
            'mainLanguageCode' => 'eng-US',
            'mainLocationId' => 43,
        ];
    }
}
