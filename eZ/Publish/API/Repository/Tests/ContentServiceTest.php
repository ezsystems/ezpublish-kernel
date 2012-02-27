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

use eZ\Publish\API\Repository\Values\Content\VersionInfo;

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
        // Create a content type
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
                false
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
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContent($contentCreateStruct, $locationCreateStructs)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateContentThrowsInvalidArgumentExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
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
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * 
     */
    public function testLoadVersionInfoWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentServiceTest::testLoadVersionInfo
     */
    public function testLoadVersionInfoThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfo($contentInfo, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionInfoThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfo() is not implemented." );
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
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * 
     */
    public function testLoadVersionInfoByIdWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfoById() is not implemented." );
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
     * Test for the loadVersionInfoById() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionInfoById($contentId, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionInfoByIdThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfoById() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * 
     */
    public function testLoadContentByContentInfo()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByContentInfo()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByContentInfoThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentByContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentByVersionInfo()
     * 
     */
    public function testLoadContentByVersionInfo()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentByVersionInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentByVersionInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId()
     * 
     */
    public function testLoadVersionByRemoteId()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId($remoteId, $languages)
     * 
     */
    public function testLoadVersionByRemoteIdWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId($remoteId, $languages, $versionNo)
     * 
     */
    public function testLoadVersionByRemoteIdWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionByRemoteIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId($remoteId, $languages)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionByRemoteIdThrowsNotFoundExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadVersionByRemoteId($remoteId, $languages, $versionNo)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadVersionByRemoteIdThrowsNotFoundExceptionWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionByRemoteId() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::updateContentMetadata() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::updateContentMetadata() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteContent() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * 
     */
    public function testCreateContentDraft()
    {
        $this->markTestIncomplete( "Test for ContentService::createContentDraft() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::createContentDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCreateContentDraftThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentService::createContentDraft() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::loadContentDrafts()
     * 
     */
    public function testLoadContentDrafts()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentDrafts() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadContentDrafts() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::translateVersion() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * 
     */
    public function testUpdateContent()
    {
        $this->markTestIncomplete( "Test for ContentService::updateContent() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::updateContent()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUpdateContentThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentService::updateContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::updateContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::updateContent() is not implemented." );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * 
     */
    public function testPublishVersion()
    {
        $this->markTestIncomplete( "Test for ContentService::publishVersion() is not implemented." );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::publishVersion()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testPublishVersionThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentService::publishVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteVersion() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadVersions() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::copyContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::copyContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::findContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::findContent() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::findSingle() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::findSingle() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadRelations() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadReverseRelations() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::addRelation() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::addRelation() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteRelation() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteRelation() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::deleteRelation() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::addTranslationInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadTranslationInfos() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::loadTranslationInfos() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::newContentMetadataUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentService::newContentUpdateStruct()
     * 
     */
    public function testNewContentUpdateStruct()
    {
        $this->markTestIncomplete( "Test for ContentService::newContentUpdateStruct() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::newTranslationInfo() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentService::newTranslationValues() is not implemented." );
    }

}
