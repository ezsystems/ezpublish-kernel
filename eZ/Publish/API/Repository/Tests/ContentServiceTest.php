<?php
/**
 * File containing the ContentServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\Content\Field;
use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\Relation;
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\Content\Query;
use \eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for operations in the ContentService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentService
 */
class ContentServiceTest extends BaseTest
{
    /**
     * Test for the newContentCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newContentCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testNewContentCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Create a content type
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\ContentCreateStruct', $contentCreate );
    }

    /**
     * Test for the createContent() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentCreateStruct
     */
    public function testCreateContent()
    {
        if ( $this->isVersion4() )
        {
            $this->markTestSkipped( "This test requires eZ Publish 5" );
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $content = $contentService->createContent( $contentCreate );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Content', $content );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentSetsContentType( $content )
    {
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\ContentType\ContentType', $content->contentType );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentSetsContentType
     */
    public function testCreateContentSetsExpectedContentType( $content )
    {
        $this->assertEquals( 'article_subpage', $content->contentType->identifier );
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentSetsContentInfo( $content )
    {
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', $content->contentInfo );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentSetsContentInfo
     */
    public function testCreateContentSetsExpectedContentInfo( $content )
    {
        $this->assertEquals(
            array(
                $content->contentId,
                true,
                1,
                'abcdef0123456789abcdef0123456789',
                'eng-GB',
                $this->getRepository()->getCurrentUser()->id,
                false,
                null
            ),
            array(
                $content->contentInfo->contentId,
                $content->contentInfo->alwaysAvailable,
                $content->contentInfo->currentVersionNo,
                $content->contentInfo->remoteId,
                $content->contentInfo->mainLanguageCode,
                $content->contentInfo->ownerId,
                $content->contentInfo->published,
                $content->contentInfo->publishedDate,
            )
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentSetsVersionInfo( $content )
    {
        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\VersionInfo', $content->getVersionInfo() );

        return $content;
    }

    /**
     * Test for the createContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentSetsVersionInfo
     */
    public function testCreateContentSetsExpectedVersionInfo( $content )
    {
        $this->assertEquals(
            array(
                'status'               =>  VersionInfo::STATUS_DRAFT,
                'versionNo'            =>  1,
                'creatorId'            =>  $this->getRepository()->getCurrentUser()->id,
                'initialLanguageCode'  =>  'eng-GB',
            ),
            array(
                'status'               =>  $content->getVersionInfo()->status,
                'versionNo'            =>  $content->getVersionInfo()->versionNo,
                'creatorId'            =>  $content->getVersionInfo()->creatorId,
                'initialLanguageCode'  =>  $content->getVersionInfo()->initialLanguageCode,
            )
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        if ( $this->isVersion4() )
        {
            $this->markTestSkipped( "This test requires eZ Publish 5" );
        }

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();
        $contentService     = $repository->getContentService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentCreate1 = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate1->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate1->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate1->alwaysAvailable = true;

        $contentService->createContent( $contentCreate1 );

        $contentCreate2 = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreate2->setField( 'title', 'Another awesome story about eZ Publish' );

        $contentCreate2->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate2->alwaysAvailable = false;

        // This call will fail with an "InvalidArgumentException", because the
        // remoteId is already in use.
        $contentService->createContent( $contentCreate2 );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * NOTE: We have bidirectional dependencies between the ContentService and
     * the LocationService, so that we cannot use PHPUnit's test dependencies
     * here.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @depend(s) eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentWithSecondParameter()
    {
        $repository = $this->getRepository();

        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // This location will contain the above content object
        $location = $locationService->loadLocationByRemoteId(
            '0123456789abcdef0123456789abcdef'
        );
        /* END: Use Case */

        $this->assertEquals( $draft->contentInfo, $location->getContentInfo() );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithSecondParameter
     */
    public function testCreateContentThrowsInvalidArgumentExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Location id of the "Home > Community" node
        $parentLocationId = 167;

        $contentService     = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService    = $repository->getLocationService();

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        // Configure new locations
        $locationCreate1 = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate1->priority  = 23;
        $locationCreate1->hidden    = true;
        $locationCreate1->remoteId  = '0123456789abcdef0123456789aaaaaa';
        $locationCreate1->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate1->sortOrder = Location::SORT_ORDER_DESC;

        $locationCreate2 = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate2->priority  = 42;
        $locationCreate2->hidden    = true;
        $locationCreate2->remoteId  = '0123456789abcdef0123456789bbbbbb';
        $locationCreate2->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate2->sortOrder = Location::SORT_ORDER_DESC;

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );
        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Create new content object under the specified location
        $contentService->createContent(
            $contentCreate,
            array( $locationCreate1 )
        );

        // This call will fail with an "InvalidArgumentException", because the
        // Content remoteId already exists,
        $contentService->createContent(
            $contentCreate,
            array( $locationCreate2 )
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadContentInfo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( 10 );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\ContentInfo',
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentInfoThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a NotFoundException
        $contentService->loadContentInfo( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentInfoByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadContentInfoByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfoByRemoteId( 'faaeb9be3bd98ed09f606fc16d144eca' );
        /* END: Use Case */

        $this->assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\ContentInfo', $contentInfo );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @return void
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
        $contentService->loadContentInfoByRemoteId( 'abcdefghijklmnopqrstuvwxyz0123456789' );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadVersionInfo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $anonUserId = 10;

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonUserId );

        // Now load the current version info of the "Anonymous User"
        $versionInfo = $contentService->loadVersionInfo( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\VersionInfo',
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadVersionInfoById()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $anonUserId = 10;

        $contentService = $repository->getContentService();

        // Load the VersionInfo for "Anonymous User"
        $versionInfo = $contentService->loadVersionInfoById( $anonUserId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\VersionInfo',
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoById
     */
    public function testLoadVersionInfoByIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a "NotFoundException"
        $contentService->loadVersionInfoById( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $anonUserId = 10;

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonUserId );

        // Now load the current content version for the info instance
        $content = $contentService->loadContentByContentInfo( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $anonUserId = 10;

        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( $anonUserId );

        // Load the current VersionInfo
        $versionInfo = $contentService->loadVersionInfo( $contentInfo );

        // Now load the current content version for the info instance
        $content = $contentService->loadContentByVersionInfo( $versionInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadContent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $anonUserId = 10;

        $contentService = $repository->getContentService();

        // Load the Content for "Anonymous User", any language and current version
        $content = $contentService->loadContent( $anonUserId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // This call will fail with a "NotFoundException"
        $contentService->loadContent( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadContentByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Load the Content for "Anonymous User"
        $content = $contentService->loadContentByRemoteId( 'faaeb9be3bd98ed09f606fc16d144eca' );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
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
        $contentService->loadContentByRemoteId( 'a1b1c1d1e1f1a2b2c2d2e2f2a3b3c3d3' );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentWithSecondParameter
     */
    public function testPublishVersion()
    {
        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );

        return $content;
    }

    /**
     * Test for the publishVersion() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionSetsExpectedContentInfo( $content )
    {
        $this->assertEquals(
            array(
                $content->contentId,
                true,
                1,
                'abcdef0123456789abcdef0123456789',
                'eng-GB',
                $this->getRepository()->getCurrentUser()->id,
                true,
            ),
            array(
                $content->contentInfo->contentId,
                $content->contentInfo->alwaysAvailable,
                $content->contentInfo->currentVersionNo,
                $content->contentInfo->remoteId,
                $content->contentInfo->mainLanguageCode,
                $content->contentInfo->ownerId,
                $content->contentInfo->published
            )
        );

        $date = new \DateTime( '1984/01/01' );
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
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testPublishVersionSetsExpectedVersionInfo( $content )
    {
        $this->assertEquals(
            array(
                $this->getRepository()->getCurrentUser()->id,
                'eng-GB',
                VersionInfo::STATUS_PUBLISHED,
                1
            ),
            array(
                $content->getVersionInfo()->creatorId,
                $content->getVersionInfo()->initialLanguageCode,
                $content->getVersionInfo()->status,
                $content->getVersionInfo()->versionNo
            )
        );

        $date = new \DateTime( '1984/01/01' );
        $this->assertGreaterThan(
            $date->getTimestamp(),
            $content->getVersionInfo()->modificationDate->getTimestamp()
        );

        $this->assertNotNull( $content->getVersionInfo()->modificationDate );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
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
        $contentService->publishVersion( $draft->getVersionInfo() );

        // This call will fail with a "BadStateException", because the version
        // is already published.
        $contentService->publishVersion( $draft->getVersionInfo() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testCreateContentDraft()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $content->contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $draftedContent
        );

        return $draftedContent;
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsExpectedProperties( $draft )
    {
        $this->assertEquals(
            array(
                'fieldCount'     =>  4,
                'relationCount'  =>  0
            ),
            array(
                'fieldCount'     =>  count( $draft->getFields() ),
                'relationCount'  =>  count( $draft->getRelations() )
            )
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsContentInfo( $draft )
    {
        $contentInfo = $draft->contentInfo;

        $this->assertEquals(
            array(
                $draft->contentId,
                true,
                1,
                'eng-GB',
                $this->getRepository()->getCurrentUser()->id,
                'abcdef0123456789abcdef0123456789',
                1
            ),
            array(
                $contentInfo->contentId,
                $contentInfo->alwaysAvailable,
                $contentInfo->currentVersionNo,
                $contentInfo->mainLanguageCode,
                $contentInfo->ownerId,
                $contentInfo->remoteId,
                $contentInfo->sectionId
            )
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testCreateContentDraftSetsVersionInfo( $draft )
    {
        $versionInfo = $draft->getVersionInfo();

        $this->assertEquals(
            array(
                'creatorId'            =>  $this->getRepository()->getCurrentUser()->id,
                'initialLanguageCode'  =>  'eng-GB',
                'languageCodes'        =>  array( 'eng-GB' ),
                'status'               =>  VersionInfo::STATUS_DRAFT,
                'versionNo'            =>  2
            ),
            array(
                'creatorId'            =>  $versionInfo->creatorId,
                'initialLanguageCode'  =>  $versionInfo->initialLanguageCode,
                'languageCodes'        =>  $versionInfo->languageCodes,
                'status'               =>  $versionInfo->status,
                'versionNo'            =>  $versionInfo->versionNo
            )
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $draft
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testCreateContentDraftLoadVersionInfoStillLoadsPublishedVersion( $draft )
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Now we create a new draft from the published content
        $contentService->createContentDraft( $content->contentInfo );

        // This call will still load the published version
        $versionInfoPublished = $contentService->loadVersionInfo( $content->contentInfo );
        /* END: Use Case */

        $this->assertEquals( 1, $versionInfoPublished->versionNo );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
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
        $contentService->createContentDraft( $content->contentInfo );

        // This call will still load the published content version
        $contentPublished = $contentService->loadContent( $content->contentId );
        /* END: Use Case */

        $this->assertEquals( 1, $contentPublished->getVersionInfo()->versionNo );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
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
        $contentService->createContentDraft( $content->contentInfo );

        // This call will still load the published content version
        $contentPublished = $contentService->loadContentByRemoteId( 'abcdef0123456789abcdef0123456789' );
        /* END: Use Case */

        $this->assertEquals( 1, $contentPublished->getVersionInfo()->versionNo );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
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
        $contentService->createContentDraft( $content->contentInfo );

        // This call will still load the published content version
        $contentPublished = $contentService->loadContentByContentInfo( $content->contentInfo );
        /* END: Use Case */

        $this->assertEquals( 1, $contentPublished->getVersionInfo()->versionNo );
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newContentUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testNewContentUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $updateStruct = $contentService->newContentUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct',
            $updateStruct
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentUpdateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testUpdateContent()
    {
        /* BEGIN: Use Case */
        $draftVersion2 = $this->createUpdatedDraftVersion2();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $draftVersion2
        );

        return $draftVersion2;
    }

    /**
     * Test for the updateContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentSetsExpectedFields( $content )
    {
        $actual = array();
        foreach ( $content->getFields() as $field )
        {
            $actual[] = new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  $field->value,
                    'languageCode'        =>  $field->languageCode,
                    'fieldDefIdentifier'  =>  $field->fieldDefIdentifier
                )
            );
        }
        usort( $actual, function ( $field1, $field2 ) {
            if ( 0 === ( $return = strcasecmp( $field1->fieldDefIdentifier, $field2->fieldDefIdentifier ) ) )
            {
                return strcasecmp( $field1->languageCode, $field2->languageCode );
            }
            return $return;
        } );

        $expected = array(
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'body'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'index_title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'tags'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome² story about ezp.',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome²³ story about ezp.',
                    'languageCode'        =>  'eng-US',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
        );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
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
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        $contentUpdateStruct->initialLanguageCode = 'eng-GB';

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
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContent
     */
    public function testUpdateContentThrowsContentValidationExceptionWhenInitialLanguageCodeIsNotSet()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );

        // Don't set this, then the above call without languageCode will fail
        //$contentUpdateStruct->initialLanguageCode = 'eng-GB';

        // This call will fail with a "ContentValidationException", because
        // "title" was set without a languageCode and no initialLanguageCode
        // is set.
        $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
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

        $this->assertEquals( 3, $draftedContentReloaded->getVersionInfo()->versionNo );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
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

        $versionInfo = $contentService->loadVersionInfo( $contentVersion2->contentInfo );

        $this->assertEquals(
            array(
                'status'     =>  VersionInfo::STATUS_PUBLISHED,
                'versionNo'  =>  2
            ),
            array(
                'status'     =>  $versionInfo->status,
                'versionNo'  =>  $versionInfo->versionNo
            )
        );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
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

        $versionInfo = $contentService->loadVersionInfo( $contentVersion2->contentInfo, 1 );

        $this->assertEquals(
            array(
                'status'     =>  VersionInfo::STATUS_ARCHIVED,
                'versionNo'  =>  1
            ),
            array(
                'status'     =>  $versionInfo->status,
                'versionNo'  =>  $versionInfo->versionNo
            )
        );
    }


    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionFromContentDraftUpdatesContentInfoCurrentVersion()
    {
        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();
        /* END: Use Case */

        $this->assertEquals( 2, $contentVersion2->contentInfo->currentVersionNo );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testPublishVersionFromOldContentDraftKeepsCurrentVersionNo()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Create a new draft with versionNo = 2
        $draftedContentVersion2 = $contentService->createContentDraft( $content->contentInfo );

        // Create another new draft with versionNo = 3
        $draftedContentVersion3 = $contentService->createContentDraft( $content->contentInfo );

        // Publish draft with versionNo = 3
        $contentService->publishVersion( $draftedContentVersion3->getVersionInfo() );

        // Publish the first draft with versionNo = 2, currentVersionNo is still 3
        $publishedDraft = $contentService->publishVersion( $draftedContentVersion2->getVersionInfo() );
        /* END: Use Case */

        $this->assertEquals( 3, $publishedDraft->contentInfo->currentVersionNo );
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newContentMetadataUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testNewContentMetadataUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Creates a new metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        $metadataUpdate->remoteId         = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-US';
        $metadataUpdate->alwaysAvailable  = false;
        /* BEGIN: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct',
            $metadataUpdate
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testNewContentMetadataUpdateStruct
     */
    public function testUpdateContentMetadata()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // Creates a metadata update struct
        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();

        $metadataUpdate->remoteId         = 'aaaabbbbccccddddeeeeffff11112222';
        $metadataUpdate->mainLanguageCode = 'eng-US';
        $metadataUpdate->alwaysAvailable  = false;
        $metadataUpdate->publishedDate    = new \DateTime( '1984/01/01' );
        $metadataUpdate->modificationDate = new \DateTime( '1984/01/01' );

        // Update the metadata of the published content object
        $content = $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );
        /* BEGIN: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );

        return $content;
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataSetsExpectedProperties( $content )
    {
        $contentInfo = $content->contentInfo;

        $this->assertEquals(
            array(
                'remoteId'          =>  'aaaabbbbccccddddeeeeffff11112222',
                'sectionId'         =>  1,
                'alwaysAvailable'   =>  false,
                'currentVersionNo'  =>  1,
                'mainLanguageCode'  =>  'eng-US',
                'modificationDate'  =>  new \DateTime( '1984/01/01' ),
                'ownerId'           =>  $this->getRepository()->getCurrentUser()->id,
                'published'         =>  true,
                'publishedDate'     =>  new \DateTime( '1984/01/01' ),
            ),
            array(
                'remoteId'          =>  $contentInfo->remoteId,
                'sectionId'         =>  $contentInfo->sectionId,
                'alwaysAvailable'   =>  $contentInfo->alwaysAvailable,
                'currentVersionNo'  =>  $contentInfo->currentVersionNo,
                'mainLanguageCode'  =>  $contentInfo->mainLanguageCode,
                'modificationDate'  =>  $contentInfo->modificationDate,
                'ownerId'           =>  $contentInfo->ownerId,
                'published'         =>  $contentInfo->published,
                'publishedDate'     =>  $contentInfo->publishedDate,
            )
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataNotUpdatesContentVersion( $content )
    {
        $this->assertEquals( 1, $content->getVersionInfo()->versionNo );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testUpdateContentMetadata
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Support" page of an eZ Publish demo installation
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $content = $this->createContentVersion1();

        // Creates a metadata update struct
        $metadataUpdate           = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->remoteId = $supportRemoteId;

        // This call will fail with an "InvalidArgumentException", because the
        // specified remoteId is already used by the "Support" page.
        $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );
        /* BEGIN: Use Case */
    }

    /**
     * Test for the deleteContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testDeleteContent()
    {
        $repository = $this->getRepository();

        $contentService  = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Load the locations for this content object
        $locations = $locationService->loadLocations( $contentVersion2->contentInfo );

        // This will delete the content, all versions and the associated locations
        $contentService->deleteContent( $contentVersion2->contentInfo );
        /* BEGIN: Use Case */

        foreach ( $locations as $location )
        {
            $locationService->loadLocation( $location->id );
        }
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testLoadContentDraftsReturnsEmptyArrayByDefault()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        $contentDrafts = $contentService->loadContentDrafts();
        /* BEGIN: Use Case */

        $this->assertSame( array(), $contentDrafts );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContentDraft
     */
    public function testLoadContentDrafts()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // Remote ids of the "Support" and the "Community" page of a eZ Publish
        // demo installation.
        $supportRemoteId   = 'affc99e41128c1475fa4f23dafb7159b';
        $communityRemoteId = '378acc2bc7a52400701956047a2f7d45';

        $contentService = $repository->getContentService();

        // "Support" article content object
        $supportContentInfo = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // "Community" article content object
        $communityContentInfo = $contentService->loadContentInfoByRemoteId( $communityRemoteId );

        // Create some drafts
        $contentService->createContentDraft( $supportContentInfo );
        $contentService->createContentDraft( $communityContentInfo );

        // Now $contentDrafts should contain two drafted versions
        $draftedVersions = $contentService->loadContentDrafts();
        /* BEGIN: Use Case */

        $actual = array(
            $draftedVersions[0]->status,
            $draftedVersions[0]->getContentInfo()->remoteId,
            $draftedVersions[1]->status,
            $draftedVersions[1]->getContentInfo()->remoteId,
        );
        sort( $actual, SORT_STRING );

        $this->assertEquals(
            array(
                VersionInfo::STATUS_DRAFT,
                VersionInfo::STATUS_DRAFT,
                $communityRemoteId,
                $supportRemoteId,
            ),
            $actual
        );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts($user)
     *
     */
    public function testLoadContentDraftsWithFirstParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentDrafts() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadVersionInfoWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Will return the $versionInfo of $content
        $versionInfo = $contentService->loadVersionInfo( $contentVersion2->contentInfo, 1 );
        /* END: Use Case */

        $this->assertEquals( 1, $versionInfo->versionNo );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
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
        $contentService->loadVersionInfo( $draft->contentInfo, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfoWithSecondParameter
     */
    public function testLoadVersionInfoByIdWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Will return the $versionInfo of $content
        $versionInfo = $contentService->loadVersionInfoById( $contentVersion2->contentId, 1 );
        /* END: Use Case */

        $this->assertEquals( 1, $versionInfo->versionNo );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
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
        $contentService->loadVersionInfoById( $content->contentId, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByVersionInfo
     */
    public function testLoadContentByVersionInfoWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentCreateStruct->setField( 'index_title', 'British index title...' );

        $contentCreateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );
        $contentCreateStruct->setField( 'index_title', 'American index title...', 'eng-US' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Will return a content instance with fields in "eng-GB"
        $reloadedContent = $contentService->loadContentByVersionInfo(
            $publishedContent->getVersionInfo(),
            array(
                'eng-GB'
            )
        );
        /* END: Use Case */

        $actual = array();
        foreach ( $reloadedContent->getFields() as $field )
        {
            $actual[] = new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  $field->value,
                    'languageCode'        =>  $field->languageCode,
                    'fieldDefIdentifier'  =>  $field->fieldDefIdentifier
                )
            );
        }
        usort( $actual, function ( $field1, $field2 ) {
            if ( 0 === ( $return = strcasecmp( $field1->fieldDefIdentifier, $field2->fieldDefIdentifier ) ) )
            {
                return strcasecmp( $field1->languageCode, $field2->languageCode );
            }
            return $return;
        } );

        $expected = array(
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'body'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'British index title...',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'index_title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'tags'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome² story about ezp.',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
        );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentCreateStruct->setField( 'index_title', 'British index title...' );

        $contentCreateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );
        $contentCreateStruct->setField( 'index_title', 'American index title...', 'eng-US' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Will return a content instance with fields in "eng-GB"
        $reloadedContent = $contentService->loadContentByContentInfo(
            $publishedContent->contentInfo,
            array(
                'eng-GB'
            )
        );
        /* END: Use Case */

        $actual = array();
        foreach ( $reloadedContent->getFields() as $field )
        {
            $actual[] = new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  $field->value,
                    'languageCode'        =>  $field->languageCode,
                    'fieldDefIdentifier'  =>  $field->fieldDefIdentifier
                )
            );
        }
        usort( $actual, function ( $field1, $field2 ) {
            if ( 0 === ( $return = strcasecmp( $field1->fieldDefIdentifier, $field2->fieldDefIdentifier ) ) )
            {
                return strcasecmp( $field1->languageCode, $field2->languageCode );
            }
            return $return;
        } );

        $expected = array(
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'body'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'British index title...',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'index_title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'tags'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome² story about ezp.',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
        );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Will return a Content instance equal to $content
        $contentReloaded = $contentService->loadContentByContentInfo(
            $contentVersion2->contentInfo,
            null,
            1
        );
        /* END: Use Case */

        $this->assertEquals(
            'An awesome story about eZ Publish',
            $contentReloaded->getFieldValue( 'title' )
        );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfoWithThirdParameter
     */
    public function testLoadContentByContentInfoThrowsNotFoundExceptionWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "NotFoundException", because no content
        // with versionNo = 2 exists.
        $contentService->loadContentByContentInfo( $content->contentInfo, null, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // This draft contains those fields localized with "eng-US"
        $draftLocalized = $contentService->loadContent( $draft->contentId, array( 'eng-US' ) );
        /* END: Use Case */

        $this->assertLocaleFieldsEquals( $draftLocalized->getFields(), 'eng-US' );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draftVersion2 = $this->createContentDraftVersion2();

        // This content instance is identical to $contentVersion1
        $oldContent = $contentService->loadContent( $draftVersion2->contentId, null, 1 );
        /* END: Use Case */

        $this->assertEquals( 1, $oldContent->getVersionInfo()->versionNo );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
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
        $contentService->loadContent( $content->contentId, null, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentByRemoteIdWithSecondParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // This draft contains those fields localized with "eng-US"
        $draftLocalized = $contentService->loadContentByRemoteId(
            $draft->contentInfo->remoteId,
            array( 'eng-US' )
        );
        /* END: Use Case */

        $this->assertLocaleFieldsEquals( $draftLocalized->getFields(), 'eng-US' );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testLoadContentByRemoteIdWithThirdParameter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draftVersion2 = $this->createContentDraftVersion2();

        // This content instance is identical to $contentVersion1
        $oldContent = $contentService->loadContentByRemoteId(
            $draftVersion2->contentInfo->remoteId,
            null,
            1
        );
        /* END: Use Case */

        $this->assertEquals( 1, $oldContent->getVersionInfo()->versionNo );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
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
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testDeleteVersion()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createContentDraftVersion1();

        // Delete the previously created draft
        $contentService->deleteVersion( $draft->getVersionInfo() );
        /* END: Use Case */

        $contentService->loadContent( $draft->contentId );
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testDeleteVersionThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $content = $this->createContentVersion1();

        // This call will fail with a "BadStateException", because the content
        // version is currently published.
        $contentService->deleteVersion( $content->getVersionInfo() );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testLoadVersions()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Load versions of this ContentInfo instance
        $versions = $contentService->loadVersions( $contentVersion2->contentInfo );
        /* END: Use Case */

        $this->assertEquals(
            array(
                $contentService->loadVersionInfo( $contentVersion2->contentInfo, 1 ),
                $contentService->loadVersionInfo( $contentVersion2->contentInfo, 2 )
            ),
            $versions
        );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testCopyContent()
    {
        $parentLocationId = 167;

        $repository = $this->getRepository();

        $contentService  = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createMultipleLanguageContentVersion2();

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $targetLocationCreate->priority  = 42;
        $targetLocationCreate->hidden    = true;
        $targetLocationCreate->remoteId  = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Copy content with all versions and drafts
        $contentCopied = $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $contentCopied
        );

        $this->assertNotEquals(
            $contentVersion2->contentInfo->remoteId,
            $contentCopied->contentInfo->remoteId
        );

        $this->assertNotEquals(
            $contentVersion2->contentId,
            $contentCopied->contentId
        );

        $this->assertEquals(
            2,
            count( $contentService->loadVersions( $contentCopied->contentInfo ) )
        );

        $this->assertEquals( 2, $contentCopied->getVersionInfo()->versionNo );

        $this->assertAllFieldsEquals( $contentCopied->getFields() );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCopyContent
     */
    public function testCopyContentWithThirdParameter()
    {
        $parentLocationId = 167;

        $repository = $this->getRepository();

        $contentService  = $repository->getContentService();
        $locationService = $repository->getLocationService();

        /* BEGIN: Use Case */
        $contentVersion2 = $this->createContentVersion2();

        // Configure new target location
        $targetLocationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $targetLocationCreate->priority  = 42;
        $targetLocationCreate->hidden    = true;
        $targetLocationCreate->remoteId  = '01234abcdef5678901234abcdef56789';
        $targetLocationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $targetLocationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Copy only the initial version
        $contentCopied = $contentService->copyContent(
            $contentVersion2->contentInfo,
            $targetLocationCreate,
            $contentService->loadVersionInfo( $contentVersion2->contentInfo, 1 )
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $contentCopied
        );

        $this->assertNotEquals(
            $contentVersion2->contentInfo->remoteId,
            $contentCopied->contentInfo->remoteId
        );

        $this->assertNotEquals(
            $contentVersion2->contentId,
            $contentCopied->contentId
        );

        $this->assertEquals(
            1,
            count( $contentService->loadVersions( $contentCopied->contentInfo ) )
        );

        $this->assertEquals( 1, $contentCopied->getVersionInfo()->versionNo );
    }

    /**
     * Test for the findContent() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     * @see \eZ\Publish\API\Repository\ContentService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentService
     */
    public function testFindContent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Create a search query for content objects about "eZ Publish"
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'body', Criterion\Operator::LIKE, '*eZ Publish*' )
            )
        );

        // Search for matching content
        $searchResult = $contentService->findContent( $query, array() );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\SearchResult',
            $searchResult
        );

        return $searchResult;
    }

    /**
     * Test for the findContent() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SearchResult $searchResult
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindContentSearchResultContainsExpectedProperties( $searchResult )
    {
        $this->assertGreaterThan( 0, $searchResult->count );
        $this->assertEquals( $searchResult->count, count( $searchResult->items ) );

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Query',
            $searchResult->query
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindContent
     */
    public function testFindContentWithLanguageFilter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // Publish the newly created draft
        $contentService->publishVersion( $draft->getVersionInfo() );

        // Create a search query for the created content object
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'title', Criterion\Operator::LIKE, '*awesome² story about*' )
            )
        );

        // Search for matching content with field language filter
        $searchResult = $contentService->findContent(
            $query,
            array( 'languages' => array( 'eng-US' ) )
        );
        /* END: Use Case */

        $this->assertEquals( 1, $searchResult->count );
        $this->assertLocaleFieldsEquals(
            $searchResult->items[0]->getFields(),
            'eng-US'
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testFindSingle()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();

        // Create a search query for content objects about "eZ Publish"
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'body', Criterion\Operator::LIKE, '*eZ Systems*' )
            )
        );

        // Search for a matching content object
        $searchResult = $contentService->findSingle( $query, array() );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\SearchResult',
            $searchResult
        );

        return $searchResult;
    }

    /**
     * Test for the findSingle() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\SearchResult $searchResult
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindSingle
     */
    public function testFindSingleSearchResultContainsExpectedProperties( $searchResult )
    {
        $this->assertEquals( 1, $searchResult->count );
        $this->assertEquals( 1, count( $searchResult->items ) );

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Query',
            $searchResult->query
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testFindSingle
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersion
     */
    public function testFindSingleWithLanguageFilter()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // Publish the newly created draft
        $contentService->publishVersion( $draft->getVersionInfo() );

        // Create a search query for the created content object
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'title', Criterion\Operator::LIKE, '*awesome² story about*' )
            )
        );

        // Search for matching content with field language filter
        $searchResult = $contentService->findSingle(
            $query,
            array( 'languages' => array( 'eng-US' ) )
        );
        /* END: Use Case */

        $this->assertEquals( 1, $searchResult->count );
        $this->assertLocaleFieldsEquals(
            $searchResult->items[0]->getFields(),
            'eng-US'
        );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testPublishVersionFromContentDraft
     */
    public function testAddRelation()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Support" page of an eZ Publish demo installation
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $draft = $this->createContentDraftVersion1();

        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // Create relation between new content object and "Support" page
        $relation = $contentService->addRelation(
            $draft->getVersionInfo(),
            $support
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Relation',
            $relation
        );

        return $contentService->loadContent( $draft->contentId );
    }

    /**
     * Test for the addRelation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationAddsRelationToContent( $content )
    {
        $this->assertEquals( 1, count( $content->getRelations() ) );
    }

    /**
     * Test for the addRelation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationSetsExpectedRelations( $content )
    {
        $relations = $content->getRelations();

        $this->assertEquals(
            array(
                'type'                             =>  Relation::COMMON,
                'sourceFieldDefinitionIdentifier'  =>  null,
                'sourceContentInfo'                =>  'abcdef0123456789abcdef0123456789',
                'destinationContentInfo'           =>  'affc99e41128c1475fa4f23dafb7159b',
            ),
            array(
                'type'                             =>  $relations[0]->type,
                'sourceFieldDefinitionIdentifier'  =>  $relations[0]->sourceFieldDefinitionIdentifier,
                'sourceContentInfo'                =>  $relations[0]->sourceContentInfo->remoteId,
                'destinationContentInfo'           =>  $relations[0]->destinationContentInfo->remoteId,
            )
        );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testAddRelationThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Support" page of an eZ Publish demo installation
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $content = $this->createContentVersion1();

        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // This call will fail with a "BadStateException", because content is
        // published and not a draft.
        $contentService->addRelation(
            $content->getVersionInfo(),
            $support
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testLoadRelations()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Support" and the "Community" page of a eZ Publish
        // demo installation.
        $supportRemoteId   = 'affc99e41128c1475fa4f23dafb7159b';
        $communityRemoteId = '378acc2bc7a52400701956047a2f7d45';

        $draft = $this->createContentDraftVersion1();

        // Load other content objects
        $support   = $contentService->loadContentInfoByRemoteId( $supportRemoteId );
        $community = $contentService->loadContentInfoByRemoteId( $communityRemoteId );

        // Create relation between new content object and "Support" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $support
        );

        // Create another relation with the "Community" page
        $contentService->addRelation(
            $draft->getVersionInfo(),
            $community
        );

        // Load all relations
        $relations = $contentService->loadRelations( $draft->getVersionInfo() );
        /* END: Use Case */

        usort( $relations, function( $rel1, $rel2 ) {
            return strcasecmp(
                $rel2->getDestinationContentInfo()->remoteId,
                $rel1->getDestinationContentInfo()->remoteId
            );
        } );

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo'       =>  'abcdef0123456789abcdef0123456789',
                    'destinationContentInfo'  =>  'affc99e41128c1475fa4f23dafb7159b',
                ),
                array(
                    'sourceContentInfo'       =>  'abcdef0123456789abcdef0123456789',
                    'destinationContentInfo'  =>  '378acc2bc7a52400701956047a2f7d45',
                )
            ),
            array(
                array(
                    'sourceContentInfo'       =>  $relations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo'  =>  $relations[0]->destinationContentInfo->remoteId,
                ),
                array(
                    'sourceContentInfo'       =>  $relations[1]->sourceContentInfo->remoteId,
                    'destinationContentInfo'  =>  $relations[1]->destinationContentInfo->remoteId,
                )
            )
        );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testAddRelation
     */
    public function testLoadReverseRelations()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Support" and the "Community" page of a eZ Publish
        // demo installation.
        $supportRemoteId   = 'affc99e41128c1475fa4f23dafb7159b';
        $communityRemoteId = '378acc2bc7a52400701956047a2f7d45';

        $content = $this->createContentVersion1();

        // Create some drafts
        $supportDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId( $supportRemoteId )
        );
        $communityDraft = $contentService->createContentDraft(
            $contentService->loadContentInfoByRemoteId( $communityRemoteId )
        );

        // Create relation between new content object and "Support" page
        $contentService->addRelation(
            $supportDraft->getVersionInfo(),
            $content->contentInfo
        );

        // Create another relation with the "Community" page
        $contentService->addRelation(
            $communityDraft->getVersionInfo(),
            $content->contentInfo
        );

        // Load all relations
        $relations = $contentService->loadReverseRelations( $content->contentInfo );
        /* END: Use Case */

        usort( $relations, function( $rel1, $rel2 ) {
            return strcasecmp(
                $rel2->getSourceContentInfo()->remoteId,
                $rel1->getSourceContentInfo()->remoteId
            );
        } );

        $this->assertEquals(
            array(
                array(
                    'sourceContentInfo'       =>  'affc99e41128c1475fa4f23dafb7159b',
                    'destinationContentInfo'  =>  'abcdef0123456789abcdef0123456789',
                ),
                array(
                    'sourceContentInfo'       =>  '378acc2bc7a52400701956047a2f7d45',
                    'destinationContentInfo'  =>  'abcdef0123456789abcdef0123456789',
                )
            ),
            array(
                array(
                    'sourceContentInfo'       =>  $relations[0]->sourceContentInfo->remoteId,
                    'destinationContentInfo'  =>  $relations[0]->destinationContentInfo->remoteId,
                ),
                array(
                    'sourceContentInfo'       =>  $relations[1]->sourceContentInfo->remoteId,
                    'destinationContentInfo'  =>  $relations[1]->destinationContentInfo->remoteId,
                )
            )
        );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadRelations
     */
    public function testDeleteRelation()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // Remote ids of the "Support" and the "Community" page of a eZ Publish
        // demo installation.
        $supportRemoteId   = 'affc99e41128c1475fa4f23dafb7159b';
        $communityRemoteId = '378acc2bc7a52400701956047a2f7d45';

        $draft = $this->createContentDraftVersion1();

        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );
        $community = $contentService->loadContentInfoByRemoteId( $communityRemoteId );

        // Establish some relations
        $contentService->addRelation( $draft->getVersionInfo(), $support );
        $contentService->addRelation( $draft->getVersionInfo(), $community );

        // Delete one of the currently created relations
        $contentService->deleteRelation( $draft->getVersionInfo(), $support );

        // The relations array now contains only one element
        $relations = $contentService->loadRelations( $draft->getVersionInfo() );
        /* END: Use Case */

        $this->assertEquals( 1, count( $relations ) );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsBadStateException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Support" page of an eZ Publish demo installation
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $content = $this->createContentVersion1();

        // Load the destination object
        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // Create a new draft
        $draftVersion2 = $contentService->createContentDraft( $content->contentInfo );

        // Add a relation
        $contentService->addRelation( $draftVersion2->getVersionInfo(), $support );

        // Publish new version
        $contentVersion2 = $contentService->publishVersion(
            $draftVersion2->getVersionInfo()
        );

        // This call will fail with a "BadStateException", because content is
        // published and not a draft.
        $contentService->deleteRelation(
            $contentVersion2->getVersionInfo(),
            $support
        );
        /* END: Use Case */
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testDeleteRelation
     */
    public function testDeleteRelationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        // RemoteId of the "Support" page of an eZ Publish demo installation
        $supportRemoteId = 'affc99e41128c1475fa4f23dafb7159b';

        $draft = $this->createContentDraftVersion1();

        // Load the destination object
        $support = $contentService->loadContentInfoByRemoteId( $supportRemoteId );

        // This call will fail with a "InvalidArgumentException", because no
        // relation exists between $draft and $support.
        $contentService->deleteRelation(
            $draft->getVersionInfo(),
            $support
        );
        /* END: Use Case */
    }

    /**
     * Creates a fresh clean content draft.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createContentDraftVersion1()
    {
        $repository = $this->getRepository();
        /* BEGIN: Inline */
        // Location id of the "Home > Community" node
        $parentLocationId = 167;

        $contentService     = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService    = $repository->getLocationService();

        // Configure new location
        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate->priority  = 23;
        $locationCreate->hidden    = true;
        $locationCreate->remoteId  = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );
        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create a draft
        $draft = $contentService->createContent( $contentCreate, array( $locationCreate ) );
        /* END: Inline */

        return $draft;
    }

    /**
     * Creates a fresh clean published content instance.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createContentVersion1()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createContentDraftVersion1();

        // Publish this draft
        $content = $contentService->publishVersion( $draft->getVersionInfo() );
        /* END: Inline */

        return $content;
    }

    /**
     * Creates a new content draft named <b>$draftVersion2</b> from a currently
     * published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createContentDraftVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $content = $this->createContentVersion1();

        // Create a new draft from the published content
        $draftVersion2 = $contentService->createContentDraft( $content->contentInfo );
        /* END: Inline */

        return $draftVersion2;
    }

    /**
     * Creates an updated content draft named <b>$draftVersion2</b> from
     * a currently published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createUpdatedDraftVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draftVersion2 = $this->createContentDraftVersion2();

        // Create an update struct and modify some fields
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdate->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        $contentUpdate->initialLanguageCode = 'eng-GB';

        // Update the content draft
        $draftVersion2 = $contentService->updateContent(
            $draftVersion2->getVersionInfo(),
            $contentUpdate
        );
        /* END: Inline */

        return $draftVersion2;
    }

    /**
     * Creates an updated content object named <b>$contentVersion2</b> from
     * a currently published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createContentVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draftVersion2 = $this->createUpdatedDraftVersion2();

        // Publish the updated draft
        $contentVersion2 = $contentService->publishVersion( $draftVersion2->getVersionInfo() );
        /* END: Inline */

        return $contentVersion2;
    }

    /**
     * Creates an updated content draft named <b>$draft</b>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createMultipleLanguageDraftVersion1()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createContentDraftVersion1();

        $contentUpdate = $contentService->newContentUpdateStruct();

        $contentUpdate->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdate->setField( 'index_title', 'British index title...' );

        $contentUpdate->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );
        $contentUpdate->setField( 'index_title', 'American index title...', 'eng-US' );

        $contentUpdate->initialLanguageCode = 'eng-GB';

        $draft = $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdate
        );
        /* BEGIN: Inline */

        return $draft;
    }

    /**
     * Creates a published content object with versionNo=2 named
     * <b>$contentVersion2</b>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function createMultipleLanguageContentVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // Publish this version.
        $contentVersion1 = $contentService->publishVersion(
            $draft->getVersionInfo()
        );

        // Create a new draft and update with same values
        $draftVersion2 = $contentService->createContentDraft(
            $contentVersion1->contentInfo
        );

        $contentUpdate = $contentService->newContentUpdateStruct();

        $contentService->updateContent(
            $draftVersion2->getVersionInfo(),
            $contentUpdate
        );

        // Finally publish version 2
        $contentVersion2 = $contentService->publishVersion(
            $draftVersion2->getVersionInfo()
        );
        /* BEGIN: Inline */

        return $contentVersion2;
    }

    /**
     * Asserts that the given fields are equal to the default fieldxs fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @return void
     */
    private function assertAllFieldsEquals( array $fields )
    {
        $actual   = $this->normalizeFields( $fields );
        $expected = $this->createFieldsFixture();

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Asserts that the given fields are equal to a language filtered set of the
     * default fields fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @param string $languageCode
     *
     * @return void
     */
    private function assertLocaleFieldsEquals( array $fields, $languageCode )
    {
        $actual = $this->normalizeFields( $fields );

        $expected = array();
        foreach ( $this->createFieldsFixture() as $field )
        {
            if ( $field->languageCode !== $languageCode )
            {
                continue;
            }
            $expected[] = $field;
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * This method normalizes a set of fields and returns a normalized set.
     *
     * Normalization means it resets the storage specific field id to zero and
     * it sorts the field by their identifier and their language code.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function normalizeFields( array $fields )
    {
        $normalized = array();
        foreach ( $fields as $field )
        {
            $normalized[] = new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  $field->value,
                    'languageCode'        =>  $field->languageCode,
                    'fieldDefIdentifier'  =>  $field->fieldDefIdentifier
                )
            );
        }
        usort( $normalized, function ( $field1, $field2 ) {
            if ( 0 === ( $return = strcasecmp( $field1->fieldDefIdentifier, $field2->fieldDefIdentifier ) ) )
            {
                return strcasecmp( $field1->languageCode, $field2->languageCode );
            }
            return $return;
        } );

        return $normalized;
    }

    /**
     * Returns a filtered set of the default fields fixture.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function createLocaleFieldsFixture( $languageCode )
    {
        $fields = array();
        foreach ( $this->createFieldsFixture() as $field )
        {
            if ( null === $field->languageCode || $languageCode === $field->languageCode )
            {
                $fields[] = $field;
            }
        }
        return $fields;
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
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'body'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'British index title...',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'index_title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'American index title...',
                    'languageCode'        =>  'eng-US',
                    'fieldDefIdentifier'  =>  'index_title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  null,
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'tags'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome² story about ezp.',
                    'languageCode'        =>  'eng-GB',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
            new Field(
                array(
                    'id'                  =>  0,
                    'value'               =>  'An awesome²³ story about ezp.',
                    'languageCode'        =>  'eng-US',
                    'fieldDefIdentifier'  =>  'title'
                )
            ),
        );
    }
}
