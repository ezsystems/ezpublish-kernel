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
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;

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
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     *
     */
    public function testCreateContentWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateOne = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateOne->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateOne->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateOne->alwaysAvailable = true;

        $contentService->createContent( $contentCreateOne );

        $contentCreateTwo = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $contentCreateTwo->setField( 'title', 'Another awesome story about eZ Publish' );

        $contentCreateTwo->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateTwo->alwaysAvailable = false;

        // This call will fail with an "IllegalArgumentException", because the
        // remoteId is already in use.
        $contentService->createContent( $contentCreateTwo );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentThrowsIllegalArgumentExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateContentThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateContentThrowsContentFieldValidationExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateContentThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateContentThrowsContentValidationExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContent() is not implemented." );
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
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( 10 );

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
        $contentService = $repository->getContentService();

        // Load the VersionInfo for "Anonymous User"
        $versionInfo = $contentService->loadVersionInfoById( 10 );
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
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( 10 );

        // Now load the current content version for the info instance
        $content = $contentService->loadContentByContentInfo( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $content
        );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * 
     */
    public function testLoadContentByContentInfoWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * 
     */
    public function testLoadContentByContentInfoWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContentByContentInfo
     */
    public function testLoadContentByContentInfoThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByContentInfoThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo($contentInfo, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByContentInfoThrowsNotFoundExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByContentInfo() is not implemented." );
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
        $contentService = $repository->getContentService();

        // Load the ContentInfo for "Anonymous User"
        $contentInfo = $contentService->loadContentInfo( 10 );

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
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo($versionInfo, $languages)
     * 
     */
    public function testLoadContentByVersionInfoWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByVersionInfo() is not implemented." );
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
        $contentService = $repository->getContentService();

        // Load the Content for "Anonymous User", any language and current version
        $content = $contentService->loadContent( 10 );
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
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadContent
     */
    public function testLoadContentWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
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
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContent($contentId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContent() is not implemented." );
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
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * 
     */
    public function testLoadContentByRemoteIdWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * 
     */
    public function testLoadContentByRemoteIdWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByRemoteIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByRemoteIdThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByRemoteId($remoteId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByRemoteIdThrowsNotFoundExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * 
     */
    public function testUpdateContentMetadata()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContentMetadata()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteContent()
     * 
     */
    public function testDeleteContent()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteContent() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion()
     * 
     */
    public function testTranslateVersion()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion($translationInfo, $translationValues, $user)
     * 
     */
    public function testTranslateVersionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testTranslateVersionThrowsBadStateException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion($translationInfo, $translationValues, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testTranslateVersionThrowsBadStateExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testTranslateVersionThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion($translationInfo, $translationValues, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testTranslateVersionThrowsContentValidationExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testTranslateVersionThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the translateVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::translateVersion($translationInfo, $translationValues, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testTranslateVersionThrowsContentFieldValidationExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testPublishVersion()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId         = 'abcdef0123456789abcdef0123456789';
        $contentCreate->modificationDate = new \DateTime( '1984/01/01' );
        $contentCreate->alwaysAvailable  = true;

        // Create a content draft
        $content = $contentService->createContent( $contentCreate );

        // Now publish the content draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $publishedContent
        );

        return $publishedContent;
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId         = 'abcdef0123456789abcdef0123456789';
        $contentCreate->modificationDate = new \DateTime( '1984/01/01' );
        $contentCreate->alwaysAvailable  = true;

        // Create a content draft
        $content = $contentService->createContent( $contentCreate );

        // Now publish the content draft
        $contentService->publishVersion( $content->getVersionInfo() );

        // This call will fail with a "BadStateException", because the version
        // is already published.
        $contentService->publishVersion( $content->getVersionInfo() );
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreate );

        // Now publish this draft
        $contentPublished = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $contentPublished->contentInfo );
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create draft and publish this draft
        $content = $contentService->publishVersion(
            $contentService->createContent( $contentCreate )->getVersionInfo()
        );

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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create draft and publish this draft
        $content = $contentService->publishVersion(
            $contentService->createContent( $contentCreate )->getVersionInfo()
        );

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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create draft and publish this draft
        $content = $contentService->publishVersion(
            $contentService->createContent( $contentCreate )->getVersionInfo()
        );

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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create draft and publish this draft
        $content = $contentService->publishVersion(
            $contentService->createContent( $contentCreate )->getVersionInfo()
        );

        // Now we create a new draft from the published content
        $contentService->createContentDraft( $content->contentInfo );

        // This call will still load the published content version
        $contentPublished = $contentService->loadContentByContentInfo( $content->contentInfo );
        /* END: Use Case */

        $this->assertEquals( 1, $contentPublished->getVersionInfo()->versionNo );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     *
     */
    public function testCreateContentDraftWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testCreateContent
     */
    public function testCreateContentDraftThrowsBadStateException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreate->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreate->sectionId       = 1;
        $contentCreate->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreate );

        // Now try to create a draft from a draft
        // This call will fail with a "BadStateException"
        $contentService->createContentDraft( $content->contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft($contentInfo, $versionInfo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCreateContentDraftThrowsBadStateExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::createContentDraft() is not implemented." );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedContent = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Content',
            $updatedContent
        );

        return $updatedContent;
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // This call will fail with a "BadStateException", because $publishedContent
        // is not a draft.
        $updatedContent = $contentService->updateContent(
            $publishedContent->getVersionInfo(),
            $contentUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateContentThrowsContentFieldValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContent() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::updateContent() is not implemented." );
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedDraft = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );

        // Now publish the updated draft
        $publishedDraft = $contentService->publishVersion( $updatedDraft->getVersionInfo() );
        /* END: Use Case */

        $versionInfo = $contentService->loadVersionInfo( $publishedDraft->contentInfo );

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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedDraft = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );

        // Now publish the updated draft
        $publishedDraft = $contentService->publishVersion( $updatedDraft->getVersionInfo() );
        /* END: Use Case */

        $versionInfo = $contentService->loadVersionInfo( $publishedDraft->contentInfo, 1 );

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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedDraft = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );

        // Now publish the updated draft
        $publishedDraft = $contentService->publishVersion( $updatedDraft->getVersionInfo() );
        /* END: Use Case */

        $this->assertEquals( 2, $publishedDraft->contentInfo->currentVersionNo );
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
        $contentService = $repository->getContentService();

        // "Support" article content object
        $supportContentInfo = $contentService->loadContentInfoByRemoteId( 'affc99e41128c1475fa4f23dafb7159b' );

        // "Community" article content object
        $communityContentInfo = $contentService->loadContentInfoByRemoteId( '378acc2bc7a52400701956047a2f7d45' );

        // Create some drafts
        $contentService->createContentDraft( $supportContentInfo );
        $contentService->createContentDraft( $communityContentInfo );

        // Now $contentDrafts should contain two drafted versions
        $contentDrafts = $contentService->loadContentDrafts();
        /* BEGIN: Use Case */

        $actual = array(
            $contentDrafts[0]->status,
            $contentDrafts[0]->getContentInfo()->remoteId,
            $contentDrafts[1]->status,
            $contentDrafts[1]->getContentInfo()->remoteId,
        );
        sort( $actual );

        $this->assertEquals(
            array(
                VersionInfo::STATUS_DRAFT,
                VersionInfo::STATUS_DRAFT,
                '378acc2bc7a52400701956047a2f7d45',
                'affc99e41128c1475fa4f23dafb7159b',
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedDraft = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );

        // Now publish the updated draft
        $publishedDraft = $contentService->publishVersion( $updatedDraft->getVersionInfo() );

        // Will return the $versionInfo of $content
        $versionInfo = $contentService->loadVersionInfo( $publishedDraft->contentInfo, 1 );
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // This call will fail with a "NotFoundException", because not versionNo
        // 2 exists for this content object.
        $contentService->loadVersionInfo( $content->contentInfo, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     *
     */
    public function testLoadVersionInfoByIdWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // Now publish this draft
        $publishedContent = $contentService->publishVersion( $content->getVersionInfo() );

        // Now we create a new draft from the published content
        $draftedContent = $contentService->createContentDraft( $publishedContent->contentInfo );

        // Now create an update struct and modify some fields
        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->setField( 'title', 'An awesome² story about ezp.' );
        $contentUpdateStruct->setField( 'title', 'An awesome²³ story about ezp.', 'eng-US' );

        // Update the content draft
        $updatedDraft = $contentService->updateContent(
            $draftedContent->getVersionInfo(),
            $contentUpdateStruct
        );

        // Now publish the updated draft
        $publishedDraft = $contentService->publishVersion( $updatedDraft->getVersionInfo() );

        // Will return the $versionInfo of $content
        $versionInfo = $contentService->loadVersionInfoById( $publishedDraft->contentId, 1 );
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

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'article_subpage' );

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreateStruct->setField( 'title', 'An awesome story about eZ Publish' );

        $contentCreateStruct->remoteId        = 'abcdef0123456789abcdef0123456789';
        $contentCreateStruct->sectionId       = 1;
        $contentCreateStruct->alwaysAvailable = true;

        // Create a new content draft
        $content = $contentService->createContent( $contentCreateStruct );

        // This call will fail with a "NotFoundException", because not versionNo
        // 2 exists for this content object.
        $contentService->loadVersionInfoById( $content->contentId, 2 );
        /* END: Use Case */
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * 
     */
    public function testDeleteVersion()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteVersion() is not implemented." );
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteVersionThrowsBadStateException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteVersion() is not implemented." );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersions()
     * 
     */
    public function testLoadVersions()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadVersions() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent()
     * 
     */
    public function testCopyContent()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo)
     * 
     */
    public function testCopyContentWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::copyContent() is not implemented." );
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent()
     * 
     */
    public function testFindContent()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findContent() is not implemented." );
    }

    /**
     * Test for the findContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findContent($query, $fieldFilters, $filterOnUserPermissions)
     * 
     */
    public function testFindContentWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findContent() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle()
     * 
     */
    public function testFindSingle()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findSingle() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::findSingle($query, $fieldFilters, $filterOnUserPermissions)
     * 
     */
    public function testFindSingleWithThirdParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::findSingle() is not implemented." );
    }

    /**
     * Test for the loadRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadRelations()
     * 
     */
    public function testLoadRelations()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadRelations() is not implemented." );
    }

    /**
     * Test for the loadReverseRelations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadReverseRelations()
     * 
     */
    public function testLoadReverseRelations()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadReverseRelations() is not implemented." );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * 
     */
    public function testAddRelation()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::addRelation() is not implemented." );
    }

    /**
     * Test for the addRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testAddRelationThrowsBadStateException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::addRelation() is not implemented." );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * 
     */
    public function testDeleteRelation()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteRelation() is not implemented." );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteRelationThrowsBadStateException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteRelation() is not implemented." );
    }

    /**
     * Test for the deleteRelation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::deleteRelation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteRelationThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::deleteRelation() is not implemented." );
    }

    /**
     * Test for the addTranslationInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::addTranslationInfo()
     * 
     */
    public function testAddTranslationInfo()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::addTranslationInfo() is not implemented." );
    }

    /**
     * Test for the loadTranslationInfos() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadTranslationInfos()
     * 
     */
    public function testLoadTranslationInfos()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadTranslationInfos() is not implemented." );
    }

    /**
     * Test for the loadTranslationInfos() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadTranslationInfos($contentInfo, $filter)
     * 
     */
    public function testLoadTranslationInfosWithSecondParameter()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::loadTranslationInfos() is not implemented." );
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newContentMetadataUpdateStruct()
     * 
     */
    public function testNewContentMetadataUpdateStruct()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::newContentMetadataUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newTranslationInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newTranslationInfo()
     * 
     */
    public function testNewTranslationInfo()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::newTranslationInfo() is not implemented." );
    }

    /**
     * Test for the newTranslationValues() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newTranslationValues()
     * 
     */
    public function testNewTranslationValues()
    {
        $this->markTestIncomplete( "@TODO: Test for ContentService::newTranslationValues() is not implemented." );
    }

}
