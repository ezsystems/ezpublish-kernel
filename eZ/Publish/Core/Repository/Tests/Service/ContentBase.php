<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\ContentBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Values\Content\VersionInfo,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\Content as APIContent,
    eZ\Publish\Core\Repository\Values\Content\Content,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\SearchResult,
    eZ\Publish\SPI\Persistence\Content\Search\Result as SPISearchResult,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for Content service
 */
abstract class ContentBase extends BaseServiceTest
{
    protected $testContentType;

    /**
     * Returns a persistence Handler mock
     *
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceHandlerMock;

    /**
     * Search handler mock
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Search\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchHandlerMock;

    protected function getContentInfoExpectedValues()
    {
        // Legacy fixture content ID=4 values
        return array(
            "id" => 4,
            "name" => "Users",
            "sectionId" => 2,
            "currentVersionNo" => 1,
            "published" => true,
            "ownerId" => 14,
            "modificationDate" => $this->getDateTime( 1033917596 ),
            "publishedDate" => $this->getDateTime( 1033917596 ),
            "alwaysAvailable" => true,
            "remoteId" => "f5c88a2209584891056f987fd965b0ba",
            "mainLanguageCode" => "eng-US",
            "mainLocationId" => 5
        );
    }

    /**
     *
     *
     * @param bool $draft
     *
     * @return array
     */
    protected function getVersionInfoExpectedValues( $draft = false )
    {
        // Legacy fixture content 4 current version (1) values
        $values = array(
            //"id" => 4,
            "versionNo" => 1,
            "modificationDate" => $this->getDateTime( 0 ),
            "creatorId" => 14,
            "creationDate" => $this->getDateTime( 0 ),
            "status" => VersionInfo::STATUS_PUBLISHED,
            "initialLanguageCode" => "eng-US",
            "languageCodes" => array( "eng-US" ),
            // Implementation properties
            "names" => array( "eng-US" => "Users" )
        );

        if ( $draft )
        {
            //$values["id"] = 675;
            $values["creatorId"] = $this->repository->getCurrentUser()->id;
            $values["versionNo"] = 2;
            $values["status"] = VersionInfo::STATUS_DRAFT;
            unset( $values["modificationDate"] );
            unset( $values["creationDate"] );
        }

        return $values;
    }

    /**
     *
     *
     * @param array $languages
     *
     * @return mixed
     */
    protected function getFieldValuesExpectedValues( array $languages = null )
    {
        // Legacy fixture content ID=4 field values
        $fieldValues = array(
            "eng-US" => array(
                "name" => array( "eng-US" => "Users" ),
                "description" => array( "eng-US" => "Main group" )
            )
        );

        $returnArray = array();
        foreach ( $fieldValues as $languageCode => $languageFieldValues )
        {
            if ( !empty( $languages ) && !in_array( $languageCode, $languages ) ) continue;
            $returnArray = array_merge_recursive( $returnArray, $languageFieldValues );
        }
        return $returnArray;
    }

    protected function getExpectedContentType()
    {

    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoValues( $contentInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getContentInfoExpectedValues(),
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadContentInfoThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because given contentId does not exist
        $contentInfo = $contentService->loadContentInfo( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfo
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadContentInfoThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentInfo() is not implemented." );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function testLoadContentInfoByRemoteId()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfoByRemoteId( "f5c88a2209584891056f987fd965b0ba" );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $contentInfo
        );

        return $contentInfo;
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @depends testLoadContentInfoByRemoteId
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     *
     * @param $contentInfo
     * @return void
     */
    public function testLoadContentInfoByRemoteIdValues( $contentInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getContentInfoExpectedValues(),
            $contentInfo
        );
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadContentInfoByRemoteIdThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because remoteId does not exist
        $contentInfo = $contentService->loadContentInfoByRemoteId( "this-remote-id-does-not-exist" );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentInfoByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentInfoByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadContentInfoByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentInfoByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function testLoadVersionInfoById()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4 );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );

        return $versionInfo;
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @depends testLoadVersionInfoById
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoByIdValues( $versionInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getVersionInfoExpectedValues(),
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function testLoadVersionInfoByIdWithSecondParameter()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4, 1 );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo',
            $versionInfo
        );

        return $versionInfo;
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @depends testLoadVersionInfoByIdWithSecondParameter
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @return void
     */
    public function testLoadVersionInfoByIdWithSecondParameterValues( $versionInfo )
    {
        $this->assertPropertiesCorrect(
            $this->getVersionInfoExpectedValues(),
            $versionInfo
        );
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @return void
     */
    public function testLoadVersionInfoByIdThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because version with given number does not exists
        $versionInfo = $contentService->loadVersionInfoById( 4, PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadVersionInfoById() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @return void
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersionInfo() is not implemented." );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @depends testLoadVersionInfoById
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
     */
    public function testLoadVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedService(
            array( "loadVersionInfoById" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadVersionInfoById"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadVersionInfo(
            new ContentInfo( array( "id" => 42 ) ),
            7
        );
    }

    /**
     * Data provider for testLoadContent()
     *
     * @return array
     */
    public function testLoadContentArgumentsProvider()
    {
        return array(
            array( 4, null, null ),
            array( 4, array( "eng-US" ), null ),
            array( 4, null, 1 ),
            array( 4, array( "eng-US" ), 1 )
        );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @dataProvider testLoadContentArgumentsProvider
     *
     * @param int $contentId
     * @param array $languages
     * @param int $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testLoadContent( $contentId, array $languages = null, $versionNo = null  )
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $content = $contentService->loadContent( $contentId, $languages, $versionNo );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $content
        );

        $this->assertContentValues( $content, $languages );
    }

    /**
     *
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $languages
     * @param bool $draft
     *
     * @return void
     */
    protected function assertContentValues( APIContent $content, array $languages = null, $draft = false )
    {
        $versionInfoValues = $this->getVersionInfoExpectedValues( $draft );
        $contentInfoValues = $this->getContentInfoExpectedValues();
        $fieldValuesValues = $this->getFieldValuesExpectedValues( $languages );

        $this->assertPropertiesCorrect(
            $versionInfoValues,
            $content->getVersionInfo()
        );

        $this->assertPropertiesCorrect(
            $contentInfoValues,
            $content->contentInfo
        );

        $this->assertEquals(
            $fieldValuesValues,
            $content->fields
        );

        // @todo assert relations

        $this->assertEquals( $content->id, $content->contentInfo->id );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContent() is not implemented." );
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionContentNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content with id PHP_INT_MAX does not exist
        $content = $contentService->loadContent( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionVersionNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because version number PHP_INT_MAX for content with id 4 does not exist
        $content = $contentService->loadContent( 4, null, PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionLanguageNotFound()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content does not exists in "eng-GB" language
        $content = $contentService->loadContent( 4, array( "eng-GB" ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentThrowsNotFoundExceptionLanguageNotFoundVariation()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because content does not exists in "eng-GB" language
        $content = $contentService->loadContent( 4, array( "eng-US", "eng-GB" ) );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @depends testLoadContent
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $contentServiceMock = $this->getPartlyMockedService(
            array( "loadContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadContent"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( array( "cro-HR" ) ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadContentByContentInfo(
            new ContentInfo( array( "id" => 42 ) ),
            array( "cro-HR" ),
            7
        );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @depends testLoadContent
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedService(
            array( "loadContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadContent"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( array( "cro-HR" ) ),
            $this->equalTo( 7 )
        );

        $contentServiceMock->loadContentByVersionInfo(
            new VersionInfo(
                array(
                    "contentInfo" => new ContentInfo( array( "id" => 42 ) ),
                    "versionNo" => 7
                )
            ),
            array( "cro-HR" )
        );
    }

    /**
     * Data provider for testLoadContentByRemoteId()
     *
     * @return array
     */
    public function testLoadContentByRemoteIdArgumentsProvider()
    {
        return array(
            array( "f5c88a2209584891056f987fd965b0ba", null, null ),
            array( "f5c88a2209584891056f987fd965b0ba", array( "eng-US" ), null ),
            array( "f5c88a2209584891056f987fd965b0ba", null, 1 ),
            array( "f5c88a2209584891056f987fd965b0ba", array( "eng-US" ), 1 )
        );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByRemoteId
     * @dataProvider testLoadContentByRemoteIdArgumentsProvider
     *
     * @param string $remoteId
     * @param array $languages
     * @param int $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function testLoadContentByRemoteId( $remoteId, array $languages = null, $versionNo )
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $content = $contentService->loadContentByRemoteId( $remoteId, $languages, $versionNo );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\Content",
            $content
        );

        $this->assertContentValues( $content, $languages );
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentByRemoteIdThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because given remoteId does not exist
        $content = $contentService->loadContentByRemoteId( "non-existent-remote-id" );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentByRemoteId() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByRemoteId
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentByRemoteId() is not implemented." );
    }

    /**
     * Test for the newContentCreateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public function testNewContentCreateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();

        $folderContentType = $contentTypeService->loadContentType( 1 );

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $folderContentType,
            "eng-GB"
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct',
            $contentCreateStruct
        );

        return array(
            "contentType" => $folderContentType,
            "contentCreateStruct" => $contentCreateStruct
        );
    }

    /**
     * Test for the newContentCreateStruct() method.
     *
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentCreateStruct
     *
     * @param array $data
     * @return void
     */
    public function testNewContentCreateStructValues( array $data )
    {
        $contentType = $data["contentType"];
        $contentCreateStruct = $data["contentCreateStruct"];

        $expectedValues = array(
            "fields" => array(),
            "contentType" => $contentType,
            "sectionId" => null,
            "ownerId" => null,
            "alwaysAvailable" => null,
            "remoteId" => null,
            "mainLanguageCode" => "eng-GB",
            "modificationDate" => null
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $contentCreateStruct
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @depends testNewContentCreateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @return array
     */
    public function testCreateContent()
    {
        $time = time();
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_translatable", "and thumbs opposable", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = $this->repository->getCurrentUser()->id;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $locationCreates = array(
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "db787a9143f57828dd4331573466a013",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 2
                )
            ),
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "a3dd7c1c9e04c89e446a70f647286e6b",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
        );

        $contentDraft = $contentService->createContent( $contentCreate, $locationCreates );
        /* END: Use Case */

        $this->assertInstanceOf( 'eZ\\Publish\\API\\Repository\\Values\\Content\\Content', $contentDraft );

        return array(
            "expected" => $contentCreate,
            "actual" => $contentDraft,
            "loadedActual" => $contentService->loadContent( $contentDraft->id, null, 1 ),
            "time" => $time
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    public function testCreateContentStructValues( array $data )
    {
        $this->assertCreateContentStructValues( $data );
    }

    /**
     * Test for the createContent() method.
     *
     * Because of the way ContentHandler::create() is implemented and tested in legacy storage it is also necessary to
     * test loaded content object, not only the one returned by ContentService::createContent
     *
     * @depends testCreateContent
     * @depends testLoadContent
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    public function testCreateContentStructValuesLoaded( array $data )
    {
        $data["actual"] = $data['loadedActual'];

        $this->assertCreateContentStructValues( $data );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     *
     * @param array $data
     */
    protected function assertCreateContentStructValues( array $data )
    {
        $this->assertCreateContentStructValuesContentInfo( $data );
        $this->assertCreateContentStructValuesVersionInfo( $data );
        $this->assertCreateContentStructValuesRelations( $data );
        $this->assertCreateContentStructValuesFields( $data );
        $this->assertCreateContentStructValuesNodeAssignments( $data );
    }

    /**
     * Asserts that ContentInfo is valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesContentInfo( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];

        $this->assertPropertiesCorrect(
            array(
                "id" => $contentDraft->id,
                // @todo
                //"name" => ,
                "sectionId" => $contentCreate->sectionId,
                "currentVersionNo" => 1,
                "published" => false,
                "ownerId" => $contentCreate->ownerId,
                "modificationDate" => null,
                "publishedDate" => null,
                "alwaysAvailable" => $contentCreate->alwaysAvailable,
                "remoteId" => $contentCreate->remoteId,
                "mainLanguageCode" => $contentCreate->mainLanguageCode,
                // @todo: should be null, InMemory skips creating node assignments and creates locations right away
                //"mainLocationId" => null,
                // implementation properties
                // @todo: test content type
                //"contentTypeId" => $contentCreate->contentType->id
            ),
            $contentDraft->contentInfo
        );
        $this->assertNotNull( $contentDraft->id );
    }

    /**
     * Asserts that VersionInfo is valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesVersionInfo( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];
        $time = $data['time'];

        $this->assertPropertiesCorrect(
            array(
                //"id"
                "versionNo" => 1,
                //"creationDate"
                //"modificationDate"
                "creatorId" => $contentCreate->ownerId,
                "status" => VersionInfo::STATUS_DRAFT,
                "initialLanguageCode" => $contentCreate->mainLanguageCode,
                //"languageCodes"
                // implementation properties
                // @todo: test content draft id
                //"contentId" => $contentDraft->id,
                // @todo
                "names" => array(
                    "eng-GB" => "value for field definition with empty default value",
                    "eng-US" => "value for field definition with empty default value"
                )
            ),
            $contentDraft->versionInfo
        );

        $languageCodes = $this->getLanguageCodesFromFields( $contentCreate->fields, $contentCreate->mainLanguageCode );

        $this->assertCount( count( $languageCodes ), $contentDraft->versionInfo->languageCodes );
        foreach ( $contentDraft->versionInfo->languageCodes as $languageCode )
        {
            $this->assertTrue( in_array( $languageCode, $languageCodes ) );
        }
        $this->assertNotNull( $contentDraft->versionInfo->id );
        $this->assertGreaterThanOrEqual( $this->getDateTime( $time ), $contentDraft->versionInfo->creationDate );
        $this->assertGreaterThanOrEqual( $this->getDateTime( $time ), $contentDraft->versionInfo->modificationDate );
    }

    /**
     *
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesRelations( array $data )
    {
        // @todo: relations not implemented yet
    }

    /**
     * Asserts that fields are valid after Content creation
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesFields( array $data )
    {
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['actual'];
        /** @var $contentCreate \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
        $contentCreate = $data['expected'];

        $createdFields = $contentDraft->getFields();
        $createdInLanguageCodes = $this->getLanguageCodesFromFields(
            $contentCreate->fields,
            $contentCreate->mainLanguageCode
        );

        $this->assertCount(
            count( $contentDraft->contentType->fieldDefinitions ) * count( $createdInLanguageCodes ),
            $createdFields,
            "Number of created fields does not match number of content type field definitions multiplied by number of languages the content is created in"
        );

        // Check field values
        $structFields = array();
        foreach ( $contentCreate->fields as $field )
            $structFields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        foreach ( $contentDraft->contentType->fieldDefinitions as $fieldDefinition )
        {
            $this->assertArrayHasKey(
                $fieldDefinition->identifier,
                $contentDraft->fields,
                "Field values are missing for field definition '{$fieldDefinition->identifier}'"
            );

            foreach ( $createdInLanguageCodes as $languageCode )
            {
                $this->assertArrayHasKey(
                    $languageCode,
                    $contentDraft->fields[$fieldDefinition->identifier],
                    "Field value is missing for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}'"
                );

                // If field is not set in create struct, it should have default value
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentCreate->mainLanguageCode;
                if ( isset( $structFields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $this->assertEquals(
                        $structFields[$fieldDefinition->identifier][$valueLanguageCode]->value,
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to given struct field value"
                    );
                }
                else
                {
                    $this->assertEquals(
                        $fieldDefinition->defaultValue,
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to default value"
                    );
                }
            }
        }
    }

    /**
     * Gathers language codes from an array of fields
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @param string $mainLanguageCode
     *
     * @return array an array of language code strings
     */
    protected function getLanguageCodesFromFields( array $fields, $mainLanguageCode )
    {
        $languageCodes = array( $mainLanguageCode );
        foreach ( $fields as $field ) $languageCodes[] = $field->languageCode;
        return array_unique( $languageCodes );
    }

    /**
     *
     *
     * @param array $data
     */
    protected function assertCreateContentStructValuesNodeAssignments( array $data )
    {
        //@todo implement
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::createContent() is not implemented." );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateContentThrowsInvalidArgumentException()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'f5c88a2209584891056f987fd965b0ba';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because remoteId "f5c88a2209584891056f987fd965b0ba" already exists
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionFieldDefinitionUnexisting()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "humpty_dumpty", "no such field definition" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because field definition with identifier "humpty_dumpty" does not exist
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionUntranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because translation was given for a untranslatable field
        // Note that it is still permissible to set untranslatable field with main language
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleUntranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_untranslatable", "Jabberwock" );
        $contentCreate->setField( "test_untranslatable", "Bandersnatch" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because multiple fields are set in create struct for the same (untranslatable)
        // field definition
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationExceptionMultipleTranslatableField()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "value for field definition with empty default value" );
        $contentCreate->setField( "test_translatable", "Jabberwock" );
        $contentCreate->setField( "test_translatable", "Bandersnatch" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because multiple fields are set in create struct for the same (translatable)
        // field definition and language
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testCreateContentThrowsContentValidationRequiredFieldDefaultValueEmpty()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_translatable", "Jabberwock" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because required field is not set and its default value is empty
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testCreateContentThrowsContentFieldValidationException()
    {
        $testContentType = $this->createTestContentType();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField(
            "test_required_empty",
            "a string that is too long and will not validate 12345678901234567890123456789012345678901234567890"
        );
        $contentCreate->setField( "test_translatable", "and thumbs opposable", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        // Throws an exception because "test_required_empty" field value is too long and fails
        // field definition's string length validator
        $contentService->createContent( $contentCreate );
        /* END: Use Case */
    }

    /**
     * Test for the newContentMetadataUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentMetadataUpdateStruct
     */
    public function testNewContentMetadataUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentMetadataUpdateStruct',
            $contentMetadataUpdateStruct
        );

        foreach ( $contentMetadataUpdateStruct as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @depends testNewContentMetadataUpdateStruct
     *
     * @return array
     */
    public function testUpdateContentMetadata()
    {
        // Create one additional location for content to be set as main location
        $locationService = $this->repository->getLocationService();
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 12 );
        $locationCreateStruct = $locationService->newLocationCreateStruct( 44 );
        $locationCreateStruct->remoteId = "test-location-remote-id-1234";
        $newLocation = $locationService->createLocation(
            $contentInfo,
            $locationCreateStruct
        );
        $newSectionId = $this->repository->getContentService()->loadContentInfo(
            $locationService->loadLocation( $newLocation->parentLocationId )->contentId
        )->sectionId;
        // Change content section to be different from new main location parent location content
        $sectionService = $this->repository->getSectionService();
        $sectionService->assignSection(
            $contentInfo,
            $sectionService->loadSection( $newSectionId === 1 ? $newSectionId + 1 : $newSectionId - 1 )
        );

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 12 );

        $newMainLocationId = $newLocation->id;
        $time = time();
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->ownerId = 10;
        $contentMetadataUpdateStruct->publishedDate = $this->getDateTime( $time );
        $contentMetadataUpdateStruct->modificationDate = $this->getDateTime( $time );
        $contentMetadataUpdateStruct->mainLanguageCode = "eng-GB";
        $contentMetadataUpdateStruct->alwaysAvailable = false;
        $contentMetadataUpdateStruct->remoteId = "the-all-new-remoteid";
        $contentMetadataUpdateStruct->mainLocationId = $newMainLocationId;

        $content = $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $content );

        return array(
            "expected" => $contentMetadataUpdateStruct,
            "actual" => $content,
            "newSectionId" => $newSectionId
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @depends testUpdateContentMetadata
     *
     * @param array $data
     */
    public function testUpdateContentMetadataStructValues( array $data )
    {
        /** @var $updateStruct \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct */
        $updateStruct = $data['expected'];
        /** @var $content \eZ\Publish\API\Repository\Values\Content\Content */
        $content = $data['actual'];

        $this->assertPropertiesCorrect(
            array(
                "ownerId" => $updateStruct->ownerId,
                // @todo test name change after name scheme resolver is implemented
                //"name" => $updateStruct->name,
                "publishedDate" => $updateStruct->publishedDate,
                "modificationDate" => $updateStruct->modificationDate,
                "mainLanguageCode" => $updateStruct->mainLanguageCode,
                "alwaysAvailable" => $updateStruct->alwaysAvailable,
                "remoteId" => $updateStruct->remoteId,
                "mainLocationId" => $updateStruct->mainLocationId,
                "sectionId" => $data["newSectionId"]
            ),
            $content->contentInfo
        );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentMetadataThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::updateContentMetadata() is not implemented." );
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends testNewContentMetadataUpdateStruct
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();
        $contentMetadataUpdateStruct->remoteId = "9b47a45624b023b1a76c73b74d704acf";

        // Throws an exception because remoteId "9b47a45624b023b1a76c73b74d704acf" is already in use
        $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentMetadata() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContentMetadata
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends testNewContentMetadataUpdateStruct
     */
    public function testUpdateContentMetadataThrowsInvalidArgumentExceptionNoMetadataPropertiesSet()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( 4 );
        $contentMetadataUpdateStruct = $contentService->newContentMetadataUpdateStruct();

        // Throws an exception because no properties are set in $contentMetadataUpdateStruct
        $contentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the newContentUpdateStruct() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newContentUpdateStruct
     */
    public function testNewContentUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\ContentUpdateStruct',
            $contentUpdateStruct
        );

        $this->assertPropertiesCorrect(
            array(
                "initialLanguageCode" => null,
                "fields" => array()
            ),
            $contentUpdateStruct
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @depends testCreateContent
     * @depends testNewContentUpdateStruct
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     *
     * @return array
     */
    public function testUpdateContent()
    {
        $content = $this->createTestContent();
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-US";

        $contentUpdateStruct->setField( "test_required_empty", "new value for test_required_empty", "eng-GB" );
        $contentUpdateStruct->setField( "test_translatable", "new eng-US value for test_translatable" );
        $contentUpdateStruct->setField( "test_untranslatable", "new value for test_untranslatable", "eng-GB" );
        $contentUpdateStruct->setField( "test_translatable", "new eng-GB value for test_translatable", "eng-GB" );

        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $updatedContent );

        return array(
            "actual" => $updatedContent,
            "expected" => $contentUpdateStruct,
            "previous" => $content,
            "time" => $time
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @depends testUpdateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     *
     * @param array $data
     */
    public function testUpdateContentStructValues( array $data )
    {
        /** @var $updatedContentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $updatedContentDraft = $data['actual'];
        /** @var $contentUpdate \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct */
        $contentUpdate = $data['expected'];
        /** @var $contentDraft \eZ\Publish\API\Repository\Values\Content\Content */
        $contentDraft = $data['previous'];

        $this->assertCount( 8, $updatedContentDraft->getFields() );

        // Check field values
        $structFields = array();
        foreach ( $contentUpdate->fields as $field )
        {
            $structFields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }

        foreach ( $updatedContentDraft->contentType->fieldDefinitions as $fieldDefinition )
        {
            $this->assertArrayHasKey(
                $fieldDefinition->identifier,
                $updatedContentDraft->fields,
                "Field values are missing for field definition '{$fieldDefinition->identifier}'"
            );

            foreach ( $updatedContentDraft->getVersionInfo()->languageCodes as $languageCode )
            {
                $this->assertArrayHasKey(
                    $languageCode,
                    $updatedContentDraft->fields[$fieldDefinition->identifier],
                    "Field value is missing for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}'"
                );

                // If field is not set in update struct, it should retain its previous value
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentDraft->contentInfo->mainLanguageCode;
                if ( isset( $structFields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $this->assertEquals(
                        $structFields[$fieldDefinition->identifier][$valueLanguageCode]->value,
                        $updatedContentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' is not equal to update struct field value"
                    );
                }
                else
                {
                    $this->assertEquals(
                        $contentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        $updatedContentDraft->fields[$fieldDefinition->identifier][$languageCode],
                        "Non-updated field value for field definition '{$fieldDefinition->identifier}' in language '{$languageCode}' did not retain its previous value"
                    );
                }
            }
        }

        $this->assertEquals(
            $contentUpdate->initialLanguageCode,
            $updatedContentDraft->versionInfo->initialLanguageCode
        );
        $this->assertGreaterThanOrEqual(
            $data["time"],
            $updatedContentDraft->versionInfo->modificationDate->getTimestamp()
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     */
    public function testUpdateContentWithNewLanguage()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "ger-DE";
        $contentUpdateStruct->setField( "test_required_empty", "new value for untranslatable field", "eng-GB" );
        $contentUpdateStruct->setField( "test_translatable", "Französisch frites", "ger-DE" );

        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */

        $fields = $updatedContent->fields;

        $this->assertCount( 3, $updatedContent->versionInfo->languageCodes );
        $this->assertCount( 12, $updatedContent->getFields() );
        $this->assertCount( 4, $updatedContent->fields );
        foreach ( $fields as $langFields )
        {
            $this->assertCount( 3, $langFields );
        }
        $this->assertEquals( "new value for untranslatable field", $fields["test_required_empty"]["eng-GB"] );
        $this->assertEquals( "new value for untranslatable field", $fields["test_required_empty"]["eng-US"] );
        $this->assertEquals( "new value for untranslatable field", $fields["test_required_empty"]["ger-DE"] );
        $this->assertEquals( $fields["test_required_not_empty"]["eng-GB"], $fields["test_required_not_empty"]["ger-DE"] );
        $this->assertEquals( "Französisch frites", $fields["test_translatable"]["ger-DE"] );
        $this->assertEquals( $fields["test_untranslatable"]["eng-GB"], $fields["test_untranslatable"]["ger-DE"] );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::testUpdateContent() is not implemented." );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUpdateContentThrowsBadStateException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4 );
        $contentUpdateStruct = $contentService->newContentUpdateStruct();

        // Throws an exception because version is not a draft
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     */
    public function testUpdateContentThrowsContentFieldValidationException()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-US";
        $contentUpdateStruct->setField(
            "test_required_empty",
            "a string that is too long and will not validate 12345678901234567890123456789012345678901234567890",
            "eng-GB"
        );

        // Throws an exception because "test_required_empty" field value is too long and fails
        // field definition's string length validator
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationExceptionRequiredFieldEmpty()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_required_empty", "" );

        // Throws an exception because required field is being updated with empty value
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationExceptionFieldDefinitionNonexistent()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "nonexistent_field_definition_identifier", "eng-GB" );

        // Throws an exception because field definition with identifier "nonexistent_field_definition_identifier"
        // does not exist in content draft content type
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationExceptionMultipleTranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_translatable", "Jabberwock" );
        $contentUpdateStruct->setField( "test_translatable", "Bandersnatch" );

        // Throws an exception because multiple fields are set in update struct for the same (translatable)
        // field definition and language
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationExceptionMultipleUntranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_untranslatable", "Jabberwock" );
        $contentUpdateStruct->setField( "test_untranslatable", "Bandersnatch" );

        // Throws an exception because multiple fields are set in update struct for the same (untranslatable)
        // field definition
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     *
     * @return array
     */
    public function testUpdateContentThrowsContentValidationExceptionUntranslatableField()
    {
        $content = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $content->id,
            $content->getVersionInfo()->versionNo
        );

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = "eng-GB";
        $contentUpdateStruct->setField( "test_untranslatable", "Jabberwock", "eng-US" );

        // Throws an exception because translation was given for a untranslatable field
        // Note that it is still permissible to set untranslatable field with main language
        $updatedContent = $contentService->updateContent( $versionInfo, $contentUpdateStruct );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @depends testCreateContent
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     */
    public function testPublishVersion()
    {
        $time = time();
        $draftContent = $this->createTestContent();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById(
            $draftContent->id,
            $draftContent->getVersionInfo()->versionNo
        );

        $publishedContent = $contentService->publishVersion( $versionInfo );
        /* END: Use Case */

        $this->assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content", $publishedContent );
        $this->assertTrue( $publishedContent->contentInfo->published );
        $this->assertEquals( VersionInfo::STATUS_PUBLISHED, $publishedContent->versionInfo->status );
        $this->assertGreaterThanOrEqual( $time, $publishedContent->contentInfo->publishedDate->getTimestamp() );
        $this->assertGreaterThanOrEqual( $time, $publishedContent->contentInfo->modificationDate->getTimestamp() );
    }

    /**
     * Test for the publishVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testPublishVersionThrowsBadStateException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4 );

        // Throws an exception because version is already published
        $publishedContent = $contentService->publishVersion( $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the publishVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::publishVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::publishVersion() is not implemented." );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @return array
     */
    public function testCreateContentDraft()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $draftContent = $contentService->createContentDraft( $contentInfo );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $draftContent
        );

        return array(
            "draftContent" => $draftContent,
            "time" => $time
        );
    }

    /**
     * @param array $data
     */
    protected function assertDraftContentValues( array $data )
    {
        /** @var $draftContent \eZ\Publish\API\Repository\Values\Content\Content */
        $draftContent = $data["draftContent"];
        $time = $data["time"];

        $this->assertContentValues( $data["draftContent"], null, true );
        $this->assertGreaterThanOrEqual(
            $this->getDateTime( $time ),
            $draftContent->getVersionInfo()->creationDate
        );
        $this->assertGreaterThanOrEqual(
            $this->getDateTime( $time ),
            $draftContent->getVersionInfo()->modificationDate
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @depends testCreateContentDraft
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @param array $data
     */
    public function testCreateContentDraftValues( array $data )
    {
        $this->assertDraftContentValues( $data );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @return array
     */
    public function testCreateContentDraftWithSecondArgument()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $content = $contentService->loadContent( 4 );

        $draftContent = $contentService->createContentDraft(
            $content->contentInfo,
            $content->getVersionInfo()
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $draftContent
        );

        return array(
            "draftContent" => $draftContent,
            "time" => $time
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @depends testCreateContentDraftWithSecondArgument
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @param array $data
     */
    public function testCreateContentDraftWithSecondArgumentValues( array $data )
    {
        $this->assertDraftContentValues( $data );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @return array
     */
    public function testCreateContentDraftWithThirdArgument()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $content = $contentService->loadContent( 4 );

        $draftContent = $contentService->createContentDraft(
            $content->contentInfo,
            $content->getVersionInfo(),
            $this->repository->getCurrentUser()
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Content',
            $draftContent
        );

        return array(
            "draftContent" => $draftContent,
            "time" => $time
        );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @depends testCreateContentDraftWithThirdArgument
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     *
     * @param array $data
     */
    public function testCreateContentDraftWithThirdArgumentValues( array $data )
    {
        $this->assertDraftContentValues( $data );
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @depends testCreateContentDraft
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     *
     * @param array $data
     */
    public function testCreateContentDraftThrowsBadStateException( array $data )
    {
        $draftContent = $data["draftContent"];

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        // Throws an exception because version status is not
        // VersionInfo::STATUS_PUBLISHED nor VersionInfo::STATUS_ARCHIVED
        $draftContent = $contentService->createContentDraft(
            $draftContent->contentInfo,
            $draftContent->getVersionInfo()
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContentDraft() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::createContentDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::createContentDraft() is not implemented." );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentDrafts
     */
    public function testLoadContentDrafts()
    {
        $contentService = $this->repository->getContentService();

        // delete existing drafts before we begin
        $draftedVersions = $contentService->loadContentDrafts();
        foreach ( $draftedVersions as $draftedVersion )
            $contentService->deleteVersion( $draftedVersion );

        /* BEGIN: Use Case */
        // Remote ids of the "Users" user group of a eZ Publish demo installation.
        $usersUserGroupRemoteId = 'f5c88a2209584891056f987fd965b0ba';
        $membersUserGroupRemoteId = '5f7f0bdb3381d6a461d8c29ff53d908f';

        // "Users" user group content object
        $usersUserGroupContentInfo = $contentService->loadContentInfoByRemoteId( $usersUserGroupRemoteId );
        $membersUserGroupContentInfo = $contentService->loadContentInfoByRemoteId( $membersUserGroupRemoteId );

        // Create some drafts
        $contentService->createContentDraft( $usersUserGroupContentInfo );
        $contentService->createContentDraft( $membersUserGroupContentInfo );

        // Now $contentDrafts should contain two drafted versions
        $draftedVersions = $contentService->loadContentDrafts();
        /* END: Use Case */

        $actual = array(
            $draftedVersions[0]->status,
            $draftedVersions[1]->status,
            count( $draftedVersions ),
            $draftedVersions[0]->getContentInfo()->remoteId,
            $draftedVersions[1]->getContentInfo()->remoteId,
        );

        $this->assertEquals(
            array(
                VersionInfo::STATUS_DRAFT,
                VersionInfo::STATUS_DRAFT,
                2,
                $usersUserGroupRemoteId,
                $membersUserGroupRemoteId
            ),
            $actual
        );
    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentDrafts
     */
    public function testLoadContentDraftsWithFirstArgument()
    {

    }

    /**
     * Test for the loadContentDrafts() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentDrafts
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadContentDraftsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadContentDrafts() is not implemented." );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @depends testLoadContentInfo
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public function testLoadVersions()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );
        $versions = $contentService->loadVersions( $contentInfo );
        /* END: Use Case */

        return $versions;
    }

    /**
     * Test for the loadVersions() method.
     *
     * @depends testLoadVersions
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     *
     * @param array $versions
     */
    public function testLoadVersionsValues( array $versions )
    {
        $versionInfoValues = $this->getVersionInfoExpectedValues();

        $this->assertPropertiesCorrect(
            $versionInfoValues,
            $versions[0]
        );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @depends testLoadContentInfo
     * @depends testCreateContentDraft
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[]
     */
    public function testLoadVersionsMultiple()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );
        // Create one additional version
        $draftContent = $contentService->createContentDraft( $contentInfo );
        $versions = $contentService->loadVersions( $contentInfo );
        /* END: Use Case */

        return array(
            "versions" => $versions,
            "time" => $time
        );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @depends testLoadVersionsMultiple
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     *
     * @param array $data
     */
    public function testLoadVersionsMultipleValues( array $data )
    {
        $versions = $data["versions"];
        $time = $data["time"];

        $versionInfoValues = $this->getVersionInfoExpectedValues();
        $this->assertPropertiesCorrect(
            $versionInfoValues,
            $versions[0]
        );

        $versionInfoValues = $this->getVersionInfoExpectedValues( true );
        $this->assertPropertiesCorrect(
            $versionInfoValues,
            $versions[1]
        );
        $this->assertGreaterThanOrEqual(
            $this->getDateTime( $time ),
            $versions[1]->creationDate
        );
        $this->assertGreaterThanOrEqual(
            $this->getDateTime( $time ),
            $versions[1]->modificationDate
        );
    }

    /**
     * Test for the loadVersions() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersions
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadVersionsThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::loadVersions() is not implemented." );
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteVersion
     */
    public function testDeleteVersion()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        // Create a version to delete
        $draftContent = $contentService->createContentDraft( $contentInfo );

        $contentService->deleteVersion( $draftContent->versionInfo );
        /* END: Use Case */

        try
        {
            $contentService->loadVersionInfo(
                $draftContent->contentInfo,
                $draftContent->versionInfo->versionNo
            );

            $this->fail( "Version was not successfully deleted!" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteVersionThrowsBadStateException()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $versionInfo = $contentService->loadVersionInfoById( 4 );

        // Throws an exception because version is published
        $contentService->deleteVersion( $versionInfo );
        /* END: Use Case */
    }

    /**
     * Test for the deleteVersion() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteVersion
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteVersionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::deleteVersion() is not implemented." );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @depends testLoadContent
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
     */
    public function testDeleteContent()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $contentService->deleteContent( $contentInfo );
        /* END: Use Case */

        try
        {
            $contentService->loadContent( $contentInfo->id );

            $this->fail( "Content was not successfully deleted!" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::deleteContent() is not implemented." );
    }

    /**
     * Test for the copyContent() method.
     *
     * @dep_ends testCreate
     * @dep_ends testLoadContentInfo
     * @dep_ends testLoadVersionInfoById
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     */
    public function testCopyContentSingleVersion()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( 11 );
        $versionInfo = $contentService->loadVersionInfoById( 11, 1 );
        $destinationLocationCreateStruct = $locationService->newLocationCreateStruct( 5 );

        $copiedContent = $contentService->copyContent(
            $contentInfo,
            $destinationLocationCreateStruct,
            $versionInfo
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            $copiedContent
        );

        $this->assertEquals( 1, $copiedContent->contentInfo->currentVersionNo );
        $this->assertGreaterThanOrEqual( $time, $copiedContent->contentInfo->modificationDate->getTimestamp() );
        $this->assertGreaterThanOrEqual( $time, $copiedContent->contentInfo->publishedDate->getTimestamp() );
        $this->assertCopyContentValues(
            $contentService->loadContent( 11, null, 1 ),
            $copiedContent
        );
    }

    /**
     * Test for the copyContent() method.
     *
     * @dep_ends testLoadVersions
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     */
    public function testCopyContentAllVersions()
    {
        $time = time();

        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( 11 );
        $destinationLocationCreateStruct = $locationService->newLocationCreateStruct( 5 );

        $copiedContent = $contentService->copyContent(
            $contentInfo,
            $destinationLocationCreateStruct
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            $copiedContent
        );

        $this->assertGreaterThanOrEqual( $time, $copiedContent->contentInfo->modificationDate->getTimestamp() );
        $this->assertGreaterThanOrEqual( $time, $copiedContent->contentInfo->publishedDate->getTimestamp() );

        $originalVersionInfos = $contentService->loadVersions( $contentInfo );
        $copiedVersionInfos = $contentService->loadVersions( $copiedContent->contentInfo );
        $sorter = function( $a, $b ) { return strcmp( $a->versionNo, $b->versionNo ); };
        usort( $originalVersionInfos, $sorter );
        usort( $copiedVersionInfos, $sorter );
        $this->assertCount(
            count( $originalVersionInfos ),
            $copiedVersionInfos,
            "Count of versions copied does not match the count of original versions"
        );
        $this->assertEquals( $contentInfo->currentVersionNo, $copiedContent->contentInfo->currentVersionNo );
        foreach ( $originalVersionInfos as $index => $versionInfo )
        {
            $this->assertEquals( $versionInfo->versionNo, $copiedVersionInfos[$index]->versionNo );
            $this->assertCopyContentValues(
                $contentService->loadContent(
                    $contentInfo->id,
                    null,
                    $versionInfo->versionNo
                ),
                $contentService->loadContent(
                    $copiedContent->id,
                    null,
                    $copiedVersionInfos[$index]->versionNo
                )
            );
        }
    }

    /**
     * Asserts that $copiedContent is valid copy of $originalContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $originalContent
     * @param \eZ\Publish\API\Repository\Values\Content\Content $copiedContent
     */
    protected function assertCopyContentValues( APIContent $originalContent, APIContent $copiedContent )
    {
        $this->assertEquals(
            $originalContent->contentType,
            $copiedContent->contentType,
            "Content type of content copy does not match the content type of original content"
        );
        $this->assertNotEquals(
            $originalContent->id,
            $copiedContent->id,
            "Id of content copy is equal to id od original content"
        );

        $this->assertSameClassPropertiesCorrect(
            array(
                //"name",
                "sectionId",
                //"currentVersionNo",
                "published",
                "ownerId",
                "alwaysAvailable",
                "mainLanguageCode",
                //"mainLocationId"
            ),
            $originalContent->contentInfo,
            $copiedContent->contentInfo
        );
        $this->assertNotEquals( $originalContent->contentInfo->id, $copiedContent->contentInfo->id );
        $this->assertNotEquals( $originalContent->contentInfo->remoteId, $copiedContent->contentInfo->remoteId );

        $this->assertSameClassPropertiesCorrect(
            array(
                "versionNo",
                //"contentId",
                "names",
                //"creationDate",
                //"modificationDate",
                "creatorId",
                //"status",
                "initialLanguageCode",
                "languageCodes"
            ),
            $originalContent->versionInfo,
            $copiedContent->versionInfo
        );
        $this->assertNotEquals( $originalContent->versionInfo->id, $copiedContent->versionInfo->id );

        $originalFields = $originalContent->getFields();
        $copiedFields = $copiedContent->getFields();
        $this->assertCount(
            count( $originalFields ),
            $copiedFields,
            "Count of fields copied does not match the count of original fields"
        );
        foreach ( $originalFields as $fieldIndex => $originalField )
        {
            $this->assertSameClassPropertiesCorrect(
                array(
                    "fieldDefIdentifier",
                    "value",
                    "languageCode"
                ),
                $originalField,
                $copiedFields[$fieldIndex]
            );
            $this->assertNotEquals(
                $originalField->id,
                $copiedFields[$fieldIndex]->id
            );
        }
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::copyContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentService::deleteContent() is not implemented." );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::findContent
     */
    public function testFindContentBehaviour()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        $contentService = $this->repository->getContentService();

        $refObject = new \ReflectionObject( $contentService );
        $refProperty = $refObject->getProperty( 'persistenceHandler' );
        $refProperty->setAccessible( true );
        $refProperty->setValue(
            $contentService,
            $this->getPersistenceHandlerMock()
        );

        $searchHandlerMock = $this->getSearchHandlerMock();
        $searchHandlerMock->expects(
            $this->once()
        )->method(
            "find"
        )->with(
            $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" ),
            $this->equalTo( 0 ),
            $this->equalTo( 10 ),
            $this->isType( "array" )
        )->will(
            $this->returnValue( new SPISearchResult() )
        );

        $contentService->findContent(
            new Query(
                array(
                    "criterion" => new Criterion\ContentId( 4 ),
                    "offset" => 0,
                    "limit" => 10,
                    "sortClauses" => array()
                )
            ),
            array( "languages" => array( "eng-GB" ) )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::findContent
     */
    public function testFindContent()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( 4 ) ),
                "offset" => 0
            )
        );

        $searchResult = $contentService->findContent(
            $query,
            array()
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\SearchResult",
            $searchResult
        );

        $this->assertEquals( $query, $searchResult->query );
        $this->assertEquals( 1, $searchResult->count );
        $this->assertCount( $searchResult->count, $searchResult->items );
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            reset( $searchResult->items )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @todo finish
     * @covers \eZ\Publish\Core\Repository\ContentService::findContent
     */
    public function testFindContentWithLanguageFilter()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( 4 ) ),
                "offset" => 0
            )
        );

        $searchResult = $contentService->findContent(
            $query,
            array( "languages" => array( "eng-US" ) )
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\API\\Repository\\Values\\Content\\SearchResult",
            $searchResult
        );

        $this->assertEquals( $query, $searchResult->query );
        $this->assertEquals( 1, $searchResult->count );
        $this->assertCount( $searchResult->count, $searchResult->items );
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            reset( $searchResult->items )
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindContent
     * @depends testFindContentWithLanguageFilter
     * @covers \eZ\Publish\Core\Repository\ContentService::findSingle
     */
    public function testFindSingle()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        $contentServiceMock = $this->getPartlyMockedService(
            array( "findContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "findContent"
        )->with(
            $this->isInstanceOf(
                "eZ\\Publish\\API\\Repository\\Values\\Content\\Query"
            ),
            $this->equalTo( array( "languages" => array( "eng-GB" ) ) ),
            $this->equalTo( true )
        )->will(
            $this->returnValue(
                new SearchResult(
                    array(
                        "count" => 1,
                        "items" => array(
                            new Content( array( "internalFields" => array() ) )
                        )
                    )
                )
            )
        );

        /* BEGIN: Use Case */
        $content = $contentServiceMock->findSingle(
            new Query(
                array(
                    "criterion" => new Criterion\ContentId( array( 42 ) ),
                    "offset" => 0
                )
            ),
            array( "languages" => array( "eng-GB" ) ),
            true
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\Repository\\Values\\Content\\Content",
            $content
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\ContentService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundException()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( PHP_INT_MAX ) ),
                "offset" => 0
            )
        );

        // Throws an exception because content with given id does not exist
        $searchResult = $contentService->findSingle(
            $query,
            array( "languages" => array( "eng-GB" ) )
        );
        /* END: Use Case */
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\ContentService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundExceptionDueToPermissions()
    {
        $this->markTestIncomplete( "Test for ContentService::findSingle() is not implemented." );
    }

    /**
     * Test for the findSingle() method.
     *
     * @depends testFindSingle
     * @covers \eZ\Publish\Core\Repository\ContentService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleThrowsInvalidArgumentException()
    {
        self::markTestIncomplete( "Move to Search Service tests" );
        /* BEGIN: Use Case */
        /*$contentService = $this->repository->getContentService();
        $query = new Query(
            array(
                "criterion" => new Criterion\ContentId( array( 1, 4 ) )
            )
        );

        // Throws an exception because more than one result was returned for the given query
        $searchResult = $contentService->findSingle(
            $query,
            array( "languages" => array( "eng-GB" ) )
        );*/
        /* END: Use Case */

        $contentServiceMock = $this->getPartlyMockedService(
            array( "findContent" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "findContent"
        )->with(
            $this->isInstanceOf(
                "eZ\\Publish\\API\\Repository\\Values\\Content\\Query"
            ),
            $this->equalTo( array( "languages" => array( "eng-GB" ) ) ),
            $this->equalTo( true )
        )->will(
            $this->returnValue(
                new SearchResult(
                    array(
                        "count" => 2,
                        "items" => array(
                            new Content( array( "internalFields" => array() ) ),
                            new Content( array( "internalFields" => array() ) )
                        )
                    )
                )
            )
        );

        // Throws an exception because more than one result was returned for the given query
        $content = $contentServiceMock->findSingle(
            new Query,
            array( "languages" => array( "eng-GB" ) ),
            true
        );
    }

    /**
     * Test for the newTranslationInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationInfo
     */
    public function testNewTranslationInfo()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $translationInfo = $contentService->newTranslationInfo();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\TranslationInfo',
            $translationInfo
        );

        foreach ( $translationInfo as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Test for the newTranslationValues() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::newTranslationValues
     */
    public function testNewTranslationValues()
    {
        /* BEGIN: Use Case */
        $contentService = $this->repository->getContentService();

        $translationValues = $contentService->newTranslationValues();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\TranslationValues',
            $translationValues
        );

        foreach ( $translationValues as $propertyName => $propertyValue )
            $this->assertNull( $propertyValue, "Property '{$propertyName}' initial value should be null'" );
    }

    /**
     * Returns a SearchHandler mock
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSearchHandlerMock()
    {
        if ( !isset( $this->searchHandlerMock ) )
        {
            $this->searchHandlerMock = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\Content\\Search\\Handler",
                array(),
                array(),
                '',
                false
            );
        }
        return $this->searchHandlerMock;
    }

    /**
     * Returns a persistence Handler mock
     *
     * @return \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPersistenceHandlerMock()
    {
        if ( !isset( $this->persistenceHandlerMock ) )
        {
            $this->persistenceHandlerMock = $this->getMock(
                "eZ\\Publish\\SPI\\Persistence\\Handler",
                array(),
                array(),
                '',
                false
            );
            $this->persistenceHandlerMock->expects(
                $this->any()
            )->method(
                "searchHandler"
            )->will(
                $this->returnValue(
                    $this->getSearchHandlerMock()
                )
            );
        }

        return $this->persistenceHandlerMock;
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * @param string[] $methods
     * @return \eZ\Publish\Core\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedService( array $methods = array() )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\ContentService",
            $methods,
            array(
                $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' ),
                $this->getPersistenceHandlerMock(),
                $this->getMock( 'eZ\\Publish\\Core\\Repository\\ObjectStorage' )
            )
        );
    }

    /**
     * Creates and returns content draft used in testing
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestContent()
    {
        $contentService = $this->repository->getContentService();
        $testContentType = $this->createTestContentType();

        $contentCreate = $contentService->newContentCreateStruct( $testContentType, 'eng-GB' );
        $contentCreate->setField( "test_required_empty", "val-11" );
        $contentCreate->setField( "test_required_not_empty", "val-12" );
        $contentCreate->setField( "test_translatable", "val-13" );
        $contentCreate->setField( "test_untranslatable", "val-14" );
        $contentCreate->setField( "test_translatable", "val-23", "eng-US" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        $contentCreate->alwaysAvailable = true;

        $locationCreates = array(
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "db787a9143f57828dd4331573466a013",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 2
                )
            ),
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => "a3dd7c1c9e04c89e446a70f647286e6b",
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => 5
                )
            ),
        );

        return $contentService->createContent( $contentCreate, $locationCreates );
    }

    /**
     * Returns ContentType used in testing
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        $contentTypeService = $this->repository->getContentTypeService();
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            "test-type"
        );
        $typeCreateStruct->names = array( "eng-US" => "Test type name" );
        $typeCreateStruct->descriptions = array( "eng-GB" => "Test type description" );
        $typeCreateStruct->remoteId = "test-type-remoteid";
        $typeCreateStruct->creatorId = $this->repository->getCurrentUser()->id;
        $typeCreateStruct->creationDate = $this->getDateTime( 0 );
        $typeCreateStruct->mainLanguageCode = "eng-GB";
        $typeCreateStruct->nameSchema = "<test_required_empty>";
        $typeCreateStruct->urlAliasSchema = "<test_required_empty>";

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_required_empty", "ezstring" );
        $fieldCreate->names = array( "eng-GB" => "Test required empty" );
        $fieldCreate->descriptions = array( "eng-GB" => "Required field with empty default value" );
        $fieldCreate->fieldGroup = "test-field-group";
        $fieldCreate->position = 0;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isRequired = true;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->defaultValue = "";
        //$validator = new StringLengthValidator();
        //$validator->maxStringLength = 64;
        //$fieldCreate->validatorConfiguration = array( $validator );
        $fieldCreate->validatorConfiguration = array(
            "StringLengthValidator" => array(
                "maxStringLength" => 64
            )
        );
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_required_not_empty", "ezstring" );
        $fieldCreate->names = array( "eng-GB" => "Test required not empty" );
        $fieldCreate->descriptions = array( "eng-GB" => "Required field with default value not empty" );
        $fieldCreate->fieldGroup = "test-field-group";
        $fieldCreate->position = 1;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isRequired = true;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->defaultValue = "dummy default data";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_translatable", "ezstring" );
        $fieldCreate->names = array( "eng-GB" => "Test translatable" );
        $fieldCreate->descriptions = array( "eng-GB" => "Translatable field" );
        $fieldCreate->fieldGroup = "test-field-group";
        $fieldCreate->position = 2;
        $fieldCreate->isTranslatable = true;
        $fieldCreate->isRequired = false;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->defaultValue = "";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $fieldCreate = $contentTypeService->newFieldDefinitionCreateStruct( "test_untranslatable", "ezstring" );
        $fieldCreate->names = array( "eng-GB" => "Test not translatable" );
        $fieldCreate->descriptions = array( "eng-GB" => "Untranslatable field" );
        $fieldCreate->fieldGroup = "test-field-group";
        $fieldCreate->position = 3;
        $fieldCreate->isTranslatable = false;
        $fieldCreate->isRequired = false;
        $fieldCreate->isInfoCollector = false;
        $fieldCreate->isSearchable = true;
        $fieldCreate->defaultValue = "";
        //$fieldCreate->validators
        //$fieldCreate->fieldSettings
        $typeCreateStruct->addFieldDefinition( $fieldCreate );

        $contentTypeDraft = $contentTypeService->createContentType(
            $typeCreateStruct,
            array( $contentTypeService->loadContentTypeGroup( 1 ) )
        );
        $contentTypeId = $contentTypeDraft->id;

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );

        return $contentTypeService->loadContentType( $contentTypeId );
    }
}
