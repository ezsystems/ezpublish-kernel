<?php
/**
 * File contains: eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\DomainLogic\ContentService;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Content;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct as SPIContentUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct as SPIContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Field as SPIField;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group as SPIObjectStateGroup;
use eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Exception;

/**
 * Mock test case for Content service
 */
class ContentTest extends BaseServiceMockTest
{
    /**
     * Represents empty Field Value
     */
    const EMPTY_FIELD_VALUE = "empty";

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::__construct
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandlerMock */
        $persistenceHandlerMock = $this->getPersistenceMockHandler( 'Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $settings = array( "settings" );

        $service = new ContentService(
            $repositoryMock,
            $persistenceHandlerMock,
            $domainMapperMock,
            $relationProcessorMock,
            $nameSchemaServiceMock,
            $settings
        );

        $this->assertAttributeSame(
            $repositoryMock,
            "repository",
            $service
        );

        $this->assertAttributeSame(
            $persistenceHandlerMock,
            "persistenceHandler",
            $service
        );

        $this->assertAttributeSame(
            $domainMapperMock,
            "domainMapper",
            $service
        );

        $this->assertAttributeSame(
            $relationProcessorMock,
            "relationProcessor",
            $service
        );

        $this->assertAttributeSame(
            $nameSchemaServiceMock,
            "nameSchemaService",
            $service
        );

        $this->assertAttributeSame(
            $settings,
            "settings",
            $service
        );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoById()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService( array( "loadContentInfo" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( APIVersionInfo::STATUS_PUBLISHED ) );

        $contentServiceMock->expects( $this->once() )
            ->method( "loadContentInfo" )
            ->with( $this->equalTo( 42 ) )
            ->will(
                $this->returnValue(
                    new ContentInfo( array( "currentVersionNo" => 24 ) )
                )
            );

        $contentHandler->expects( $this->once() )
            ->method( "loadVersionInfo" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )->will(
                $this->returnValue( new SPIVersionInfo() )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( new SPIVersionInfo() )
            ->will( $this->returnValue( $versionInfoMock ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "read" ),
                $this->equalTo( $versionInfoMock )
            )->will( $this->returnValue( true ) );

        $result = $contentServiceMock->loadVersionInfoById( 42 );

        $this->assertEquals( $versionInfoMock, $result );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadVersionInfoByIdThrowsNotFoundException()
    {
        $contentServiceMock = $this->getPartlyMockedContentService( array( "loadContentInfo" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();

        $contentHandler->expects( $this->once() )
            ->method( "loadVersionInfo" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )->will(
                $this->throwException(
                    new NotFoundException(
                        "Content",
                        array(
                            "contentId" => 42,
                            "versionNo" => 24
                        )
                    )
                )
            );

        $contentServiceMock->loadVersionInfoById( 42, 24 );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfoById
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadVersionInfoByIdThrowsUnauthorizedExceptionNonPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( APIVersionInfo::STATUS_DRAFT ) );

        $contentHandler->expects( $this->once() )
            ->method( "loadVersionInfo" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )->will(
                $this->returnValue( new SPIVersionInfo() )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( new SPIVersionInfo() )
            ->will( $this->returnValue( $versionInfoMock ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "versionread" ),
                $this->equalTo( $versionInfoMock )
            )->will( $this->returnValue( false ) );

        $contentServiceMock->loadVersionInfoById( 42, 24 );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoByIdPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( APIVersionInfo::STATUS_PUBLISHED ) );

        $contentHandler->expects( $this->once() )
            ->method( "loadVersionInfo" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )->will(
                $this->returnValue( new SPIVersionInfo() )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( new SPIVersionInfo() )
            ->will( $this->returnValue( $versionInfoMock ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "read" ),
                $this->equalTo( $versionInfoMock )
            )->will( $this->returnValue( true ) );

        $result = $contentServiceMock->loadVersionInfoById( 42, 24 );

        $this->assertEquals( $versionInfoMock, $result );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfoById
     */
    public function testLoadVersionInfoByIdNonPublishedVersion()
    {
        $repository = $this->getRepositoryMock();
        $contentServiceMock = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "status" )
            ->will( $this->returnValue( APIVersionInfo::STATUS_DRAFT ) );

        $contentHandler->expects( $this->once() )
            ->method( "loadVersionInfo" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )->will(
                $this->returnValue( new SPIVersionInfo() )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( new SPIVersionInfo() )
            ->will( $this->returnValue( $versionInfoMock ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "versionread" ),
                $this->equalTo( $versionInfoMock )
            )->will( $this->returnValue( true ) );

        $result = $contentServiceMock->loadVersionInfoById( 42, 24 );

        $this->assertEquals( $versionInfoMock, $result );
    }

    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadVersionInfo
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest::testLoadVersionInfoById
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdThrowsNotFoundException
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdThrowsUnauthorizedExceptionNonPublishedVersion
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdPublishedVersion
     * @depends eZ\Publish\Core\Repository\DomainLogic\Tests\Service\Mock\ContentTest::testLoadVersionInfoByIdNonPublishedVersion
     */
    public function testLoadVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
            array( "loadVersionInfoById" )
        );
        $contentServiceMock->expects(
            $this->once()
        )->method(
            "loadVersionInfoById"
        )->with(
            $this->equalTo( 42 ),
            $this->equalTo( 7 )
        )->will(
            $this->returnValue( "result" )
        );

        $result = $contentServiceMock->loadVersionInfo(
            new ContentInfo( array( "id" => 42 ) ),
            7
        );

        $this->assertEquals( "result", $result );
    }

    public function testLoadContent()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( 'internalLoadContent' ) );
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $versionInfo = $this
            ->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\VersionInfo' )
            ->setConstructorArgs( array( array( 'status' => APIVersionInfo::STATUS_PUBLISHED ) ) )
            ->getMockForAbstractClass();
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );
        $contentId = 123;
        $contentService
            ->expects( $this->once() )
            ->method( 'internalLoadContent' )
            ->with( $contentId )
            ->will( $this->returnValue( $content ) );

        $repository
            ->expects( $this->once() )
            ->method( 'canUser' )
            ->with( 'content', 'read', $content )
            ->will( $this->returnValue( true ) );

        $this->assertSame( $content, $contentService->loadContent( $contentId ) );
    }

    public function testLoadContentNonPublished()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( 'internalLoadContent' ) );
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $versionInfo = $this
            ->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\VersionInfo' )
            ->setConstructorArgs( array( array( 'status' => APIVersionInfo::STATUS_DRAFT ) ) )
            ->getMockForAbstractClass();
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );
        $contentId = 123;
        $contentService
            ->expects( $this->once() )
            ->method( 'internalLoadContent' )
            ->with( $contentId )
            ->will( $this->returnValue( $content ) );

        $repository
            ->expects( $this->exactly( 2 ) )
            ->method( 'canUser' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'content', 'read', $content, null, true ),
                        array( 'content', 'versionread', $content, null, true ),
                    )
                )
            );

        $this->assertSame( $content, $contentService->loadContent( $contentId ) );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadContentUnauthorized()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( 'internalLoadContent' ) );
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $contentId = 123;
        $contentService
            ->expects( $this->once() )
            ->method( 'internalLoadContent' )
            ->with( $contentId )
            ->will( $this->returnValue( $content ) );

        $repository
            ->expects( $this->once() )
            ->method( 'canUser' )
            ->with( 'content', 'read', $content )
            ->will( $this->returnValue( false ) );

        $contentService->loadContent( $contentId );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testLoadContentNotPublishedStatusUnauthorized()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( 'internalLoadContent' ) );
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $versionInfo = $this
            ->getMockBuilder( 'eZ\Publish\API\Repository\Values\Content\VersionInfo' )
            ->setConstructorArgs( array( array( 'status' => APIVersionInfo::STATUS_DRAFT ) ) )
            ->getMockForAbstractClass();
        $content
            ->expects( $this->once() )
            ->method( 'getVersionInfo' )
            ->will( $this->returnValue( $versionInfo ) );
        $contentId = 123;
        $contentService
            ->expects( $this->once() )
            ->method( 'internalLoadContent' )
            ->with( $contentId )
            ->will( $this->returnValue( $content ) );

        $repository
            ->expects( $this->exactly( 2 ) )
            ->method( 'canUser' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'content', 'read', $content, null, true ),
                        array( 'content', 'versionread', $content, null, false ),
                    )
                )
            );

        $contentService->loadContent( $contentId );
    }

    /**
     * @dataProvider internalLoadContentProvider
     */
    public function testInternalLoadContent( $id, $languages, $versionNo, $isRemoteId )
    {
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $realVersionNo = $versionNo;
        $realId = $id;

        if ( $isRemoteId )
        {
            $realVersionNo = $versionNo ?: 7;
            $realId = 123;
            $spiContentInfo = new SPIContentInfo( array( 'currentVersionNo' => $realVersionNo, 'id' => $realId ) );
            $contentHandler
                ->expects( $this->once() )
                ->method( 'loadContentInfoByRemoteId' )
                ->with( $id )
                ->will( $this->returnValue( $spiContentInfo ) );
        }
        else if ( $versionNo === null )
        {
            $realVersionNo = 7;
            $spiContentInfo = new SPIContentInfo( array( 'currentVersionNo' => $realVersionNo ) );
            $contentHandler
                ->expects( $this->once() )
                ->method( 'loadContentInfo' )
                ->with( $id )
                ->will( $this->returnValue( $spiContentInfo ) );
        }

        $spiContent = new SPIContent();
        $contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $realId, $realVersionNo, $languages )
            ->will( $this->returnValue( $spiContent ) );
        $content = $this->getMock( 'eZ\Publish\API\Repository\Values\Content\Content' );
        $this->getDomainMapperMock()
            ->expects( $this->once() )
            ->method( 'buildContentDomainObject' )
            ->with( $spiContent )
            ->will( $this->returnValue( $content ) );

        $this->assertSame(
            $content,
            $contentService->internalLoadContent( $id, $languages, $versionNo, $isRemoteId )
        );
    }

    public function internalLoadContentProvider()
    {
        return array(
            array( 123, null, null, false ),
            array( 123, null, 456, false ),
            array( 456, null, 123, false ),
            array( 456, null, 2, false ),
            array( 456, array( 'eng-GB' ), 2, false ),
            array( 456, array( 'eng-GB', 'fre-FR' ), null, false ),
            array( 456, array( 'eng-GB', 'fre-FR', 'nor-NO' ), 2, false ),
            // With remoteId
            array( 123, null, null, true ),
            array( 'someRemoteId', null, 456, true ),
            array( 456, null, 123, true ),
            array( 'someRemoteId', null, 2, true ),
            array( 'someRemoteId', array( 'eng-GB' ), 2, true ),
            array( 456, array( 'eng-GB', 'fre-FR' ), null, true ),
            array( 'someRemoteId', array( 'eng-GB', 'fre-FR', 'nor-NO' ), 2, true ),
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testInternalLoadContentNotFound()
    {
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();
        $id = 123;
        $versionNo = 7;
        $languages = null;
        $contentHandler
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $id, $versionNo, $languages )
            ->will(
                $this->throwException(
                    $this->getMock( 'eZ\Publish\API\Repository\Exceptions\NotFoundException' )
                )
            );

        $contentService->internalLoadContent( $id, $languages, $versionNo );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadContentByContentInfo
     */
    public function testLoadContentByContentInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
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
        )->will(
            $this->returnValue( "result" )
        );

        $result = $contentServiceMock->loadContentByContentInfo(
            new ContentInfo( array( "id" => 42 ) ),
            array( "cro-HR" ),
            7
        );

        $this->assertEquals( "result", $result );
    }

    /**
     * Test for the loadContentByVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::loadContentByVersionInfo
     */
    public function testLoadContentByVersionInfo()
    {
        $contentServiceMock = $this->getPartlyMockedContentService(
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
        )->will(
            $this->returnValue( "result" )
        );

        $result = $contentServiceMock->loadContentByVersionInfo(
            new VersionInfo(
                array(
                    "contentInfo" => new ContentInfo( array( "id" => 42 ) ),
                    "versionNo" => 7
                )
            ),
            array( "cro-HR" )
        );

        $this->assertEquals( "result", $result );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::deleteContent
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testDeleteContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );

        $contentInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $contentService->expects( $this->once() )
            ->method( "internalLoadContentInfo" )
            ->with( 42 )
            ->will( $this->returnValue( $contentInfo ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with( "content", "remove" )
            ->will( $this->returnValue( false ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent( $contentInfo );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::deleteContent
     */
    public function testDeleteContent()
    {
        $repository = $this->getRepositoryMock();

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with( "content", "remove" )
            ->will( $this->returnValue( true ) );

        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandler */
        $urlAliasHandler = $this->getPersistenceMock()->urlAliasHandler();
        /** @var \PHPUnit_Framework_MockObject_MockObject $locationHandler */
        $locationHandler = $this->getPersistenceMock()->locationHandler();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandler */
        $contentHandler = $this->getPersistenceMock()->contentHandler();

        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );

        $contentService->expects( $this->once() )
            ->method( "internalLoadContentInfo" )
            ->with( 42 )
            ->will( $this->returnValue( $contentInfo ) );

        $contentInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $spiLocations = array(
            new SPILocation( array( "id" => 1 ) ),
            new SPILocation( array( "id" => 2 ) ),
        );
        $locationHandler->expects( $this->once() )
            ->method( "loadLocationsByContent" )
            ->with( 42 )
            ->will( $this->returnValue( $spiLocations ) );

        $contentHandler->expects( $this->once() )
            ->method( "deleteContent" )
            ->with( 42 );

        foreach ( $spiLocations as $index => $spiLocation )
        {
            $urlAliasHandler->expects( $this->at( $index ) )
                ->method( "locationDeleted" )
                ->with( $spiLocation->id );
        }

        $repository->expects( $this->once() )->method( "commit" );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent( $contentInfo );
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::deleteContent
     * @expectedException \Exception
     */
    public function testDeleteContentWithRollback()
    {
        $repository = $this->getRepositoryMock();

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with( "content", "remove" )
            ->will( $this->returnValue( true ) );

        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $locationHandler */
        $locationHandler = $this->getPersistenceMock()->locationHandler();

        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );

        $contentService->expects( $this->once() )
            ->method( "internalLoadContentInfo" )
            ->with( 42 )
            ->will( $this->returnValue( $contentInfo ) );

        $contentInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $repository->expects( $this->once() )->method( "beginTransaction" );

        $locationHandler->expects( $this->once() )
            ->method( "loadLocationsByContent" )
            ->with( 42 )
            ->will( $this->throwException( new \Exception ) );

        $repository->expects( $this->once() )->method( "rollback" );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->deleteContent( $contentInfo );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$contentCreateStruct' is invalid: 'mainLanguageCode' property must be set
     */
    public function testCreateContentThrowsInvalidArgumentExceptionMainLanguageCodeNotSet()
    {
        $mockedService = $this->getPartlyMockedContentService();
        $mockedService->createContent( new ContentCreateStruct(), array() );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Argument '$contentCreateStruct' is invalid: 'contentType' property must be set
     */
    public function testCreateContentThrowsInvalidArgumentExceptionContentTypeNotSet()
    {
        $mockedService = $this->getPartlyMockedContentService();
        $mockedService->createContent(
            new ContentCreateStruct( array( "mainLanguageCode" => "eng-US" )  ),
            array()
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentThrowsUnauthorizedException()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => array()
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "ownerId" => 169,
                "alwaysAvailable" => false,
                "mainLanguageCode" => "eng-US",
                "contentType" => $contentType
            )
        );

        $repositoryMock->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 169 ) ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 123 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( $contentCreateStruct ),
                $this->equalTo( array() )
            )->will( $this->returnValue( false ) );

        $mockedService->createContent(
            new ContentCreateStruct(
                array(
                    "mainLanguageCode" => "eng-US",
                    "contentType" => $contentType
                )
            ),
            array()
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @exceptionMessage Argument '$contentCreateStruct' is invalid: Another content with remoteId 'faraday' exists
     */
    public function testCreateContentThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContentByRemoteId" ) );
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => array()
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "ownerId" => 169,
                "alwaysAvailable" => false,
                "remoteId" => "faraday",
                "mainLanguageCode" => "eng-US",
                "contentType" => $contentType
            )
        );

        $repositoryMock->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 169 ) ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 123 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( $contentCreateStruct ),
                $this->equalTo( array() )
            )->will( $this->returnValue( true ) );

        $mockedService->expects( $this->once() )
            ->method( "loadContentByRemoteId" )
            ->with( $contentCreateStruct->remoteId )
            ->will( $this->returnValue( "Hello..." ) );

        $mockedService->createContent(
            new ContentCreateStruct(
                array(
                    "remoteId" => "faraday",
                    "mainLanguageCode" => "eng-US",
                    "contentType" => $contentType
                )
            ),
            array()
        );
    }

    /**
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    protected function mapStructFieldsForCreate( $mainLanguageCode, $structFields, $fieldDefinitions )
    {
        $mappedFieldDefinitions = array();
        foreach ( $fieldDefinitions as $fieldDefinition )
        {
            $mappedFieldDefinitions[$fieldDefinition->identifier] = $fieldDefinition;
        }

        $mappedStructFields = array();
        foreach ( $structFields as $structField )
        {
            if ( $structField->languageCode === null )
            {
                $languageCode = $mainLanguageCode;
            }
            else
            {
                $languageCode = $structField->languageCode;
            }

            $mappedStructFields[$structField->fieldDefIdentifier][$languageCode] = (string)$structField->value;
        }

        return $mappedStructFields;
    }

    /**
     * Returns full, possibly redundant array of field values, indexed by field definition
     * identifier and language code.
     *
     * @throws \RuntimeException Method is intended to be used only with consistent fixtures
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param array $languageCodes
     *
     * @return array
     */
    protected function determineValuesForCreate(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions,
        array $languageCodes
    )
    {
        $mappedStructFields = $this->mapStructFieldsForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );

        $values = array();

        foreach ( $fieldDefinitions as $fieldDefinition )
        {
            $identifier = $fieldDefinition->identifier;
            foreach ( $languageCodes as $languageCode )
            {
                if ( !$fieldDefinition->isTranslatable )
                {
                    if ( isset( $mappedStructFields[$identifier][$mainLanguageCode] ) )
                    {
                        $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$mainLanguageCode];
                    }
                    else
                    {
                        $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
                    }
                    continue;
                }

                if ( isset( $mappedStructFields[$identifier][$languageCode] ) )
                {
                    $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$languageCode];
                    continue;
                }

                $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
            }
        }

        return $this->stubValues( $values );
    }

    /**
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     *
     * @return string[]
     */
    protected function determineLanguageCodesForCreate( $mainLanguageCode, array $structFields )
    {
        $languageCodes = array();

        foreach ( $structFields as $field )
        {
            if ( $field->languageCode === null || isset( $languageCodes[$field->languageCode] ) )
            {
                continue;
            }

            $languageCodes[$field->languageCode] = true;
        }

        $languageCodes[$mainLanguageCode] = true;

        return array_keys( $languageCodes );
    }

    /**
     * Asserts that calling createContent() with given API field set causes calling
     * Handler::createContent() with given SPI field set.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs
     * @param \eZ\Publish\SPI\Persistence\Content\ObjectState\Group[] $objectStateGroups
     * @param boolean $execute
     *
     * @return mixed
     */
    protected function assertForTestCreateContentNonRedundantFieldSet(
        $mainLanguageCode,
        array $structFields,
        array $spiFields,
        array $fieldDefinitions,
        array $locationCreateStructs = array(),
        $withObjectStates = false,
        $execute = true
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        /** @var \PHPUnit_Framework_MockObject_MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $languageCodes = $this->determineLanguageCodesForCreate( $mainLanguageCode, $structFields );
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => $fieldDefinitions,
                "nameSchema" => "<nameSchema>"
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "fields" => $structFields,
                "mainLanguageCode" => $mainLanguageCode,
                "contentType" => $contentType,
                "alwaysAvailable" => false,
                "ownerId" => 169,
                "sectionId" => 1
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( $contentType->id ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $that = $this;
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ),
                $this->equalTo( $locationCreateStructs )
            )->will(
                $this->returnCallback(
                    function () use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, func_get_arg( 2 ) );
                        return true;
                    }
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "getUniqueHash" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ) )
            ->will(
                $this->returnCallback(
                    function ( $object ) use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, $object );
                        return "hash";
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "acceptValue" )
            ->will(
                $this->returnCallback(
                    function ( $valueString )
                    {
                        return new ValueStub( $valueString );
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "toPersistenceValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value )
                    {
                        return (string)$value;
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects( $this->any() )
            ->method( "isEmptyValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value ) use ( $emptyValue )
                    {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "validate" )
            ->will( $this->returnValue( array() ) );

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $relationProcessorMock
            ->expects( $this->exactly( count( $fieldDefinitions ) * count( $languageCodes ) ) )
            ->method( "appendFieldRelations" )
            ->with(
                $this->isType( "array" ),
                $this->isType( "array" ),
                $this->isInstanceOf( "eZ\\Publish\\SPI\\FieldType\\FieldType" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\FieldType\\Value" ),
                $this->anything()
            );

        $values = $this->determineValuesForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions,
            $languageCodes
        );
        $nameSchemaServiceMock->expects( $this->once() )
            ->method( "resolve" )
            ->with(
                $this->equalTo( $contentType->nameSchema ),
                $this->equalTo( $contentType ),
                $this->equalTo( $values ),
                $this->equalTo( $languageCodes )
            )->will( $this->returnValue( array() ) );

        $relationProcessorMock->expects( $this->any() )
            ->method( "processFieldRelations" )
            ->with(
                $this->isType( "array" ),
                $this->equalTo( 42 ),
                $this->isType( "int" ),
                $this->equalTo( $contentType ),
                $this->equalTo( array() )
            );

        if ( !$withObjectStates )
        {
            $objectStateHandlerMock->expects( $this->once() )
                ->method( "loadAllGroups" )
                ->will( $this->returnValue( array() ) );
        }

        if ( $execute )
        {
            $spiContentCreateStruct = new SPIContentCreateStruct(
                array(
                    "name" => array(),
                    "typeId" => 123,
                    "sectionId" => 1,
                    "ownerId" => 169,
                    "remoteId" => "hash",
                    "fields" => $spiFields,
                    "modified" => time(),
                    "initialLanguageId" => 4242
                )
            );
            $spiContentCreateStruct2 = clone $spiContentCreateStruct;
            $spiContentCreateStruct2->modified++;

            $spiContent = new SPIContent(
                array(
                    "versionInfo" => new SPIContent\VersionInfo(
                        array(
                            "contentInfo" => new SPIContent\ContentInfo( array( "id" => 42 ) ),
                            "versionNo" => 7
                        )
                    )
                )
            );

            $contentHandlerMock->expects( $this->once() )
                ->method( "create" )
                ->with( $this->logicalOr( $spiContentCreateStruct, $spiContentCreateStruct2 ) )
                ->will( $this->returnValue( $spiContent ) );

            $repositoryMock->expects( $this->once() )->method( "commit" );
            $domainMapperMock->expects( $this->once() )
                ->method( "buildContentDomainObject" )
                ->with(
                    $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content" ),
                    $this->equalTo( null )
                );

            $mockedService->createContent( $contentCreateStruct, array() );
        }

        return $contentCreateStruct;
    }

    public function providerForTestCreateContentNonRedundantFieldSet1()
    {
        $spiFields = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue",
                    "languageCode" => "eng-US"
                )
            )
        );

        return array(
            // 0. Without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-US"
                        )
                    )
                ),
                $spiFields
            ),
            // 1. Without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => null
                        )
                    )
                ),
                $spiFields
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * Testing the simplest use case.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSet1
     */
    public function testCreateContentNonRedundantFieldSet1( $mainLanguageCode, $structFields, $spiFields )
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentNonRedundantFieldSet2()
    {
        $spiFields = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US"
                )
            ),
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "ger-DE"
                )
            ),
        );

        return array(
            // 0. With language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "ger-DE"
                        )
                    )
                ),
                $spiFields
            ),
            // 1. Without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "ger-DE"
                        )
                    )
                ),
                $spiFields
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * Testing multiple languages with multiple translatable fields with empty default value.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSet2
     */
    public function testCreateContentNonRedundantFieldSet2( $mainLanguageCode, $structFields, $spiFields )
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier1",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId2",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier2",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            )
        );

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentNonRedundantFieldSetComplex()
    {
        $spiFields0 = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue2",
                    "languageCode" => "eng-US"
                )
            ),
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue4",
                    "languageCode" => "eng-US"
                )
            ),
        );
        $spiFields1 = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "ger-DE"
                )
            ),
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue2",
                    "languageCode" => "ger-DE"
                )
            ),
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-US"
                )
            ),
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue4",
                    "languageCode" => "eng-US"
                )
            ),
        );

        return array(
            // 0. Creating by default values only
            array(
                "eng-US",
                array(),
                $spiFields0
            ),
            // 1. Multiple languages with language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "ger-DE"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier4",
                            'value' => "newValue4",
                            'languageCode' => "eng-US"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 2. Multiple languages without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "ger-DE"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier4",
                            'value' => "newValue4",
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields1
            ),
        );
    }

    protected function fixturesForTestCreateContentNonRedundantFieldSetComplex()
    {
        return array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier1",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId2",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier2",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue2",
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId3",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier3",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId4",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier4",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue4",
                )
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * Testing multiple languages with multiple translatable fields with empty default value.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::cloneField
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentNonRedundantFieldSetComplex
     */
    public function testCreateContentNonRedundantFieldSetComplex( $mainLanguageCode, $structFields, $spiFields )
    {
        $fieldDefinitions = $this->fixturesForTestCreateContentNonRedundantFieldSetComplex();

        $this->assertForTestCreateContentNonRedundantFieldSet(
            $mainLanguageCode,
            $structFields,
            $spiFields,
            $fieldDefinitions
        );
    }

    public function providerForTestCreateContentWithInvalidLanguage()
    {
        return array(
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "Klingon"
                        )
                    ),
                ),
            ),
            array(
                "Klingon",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentWithInvalidLanguage
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'Language' with identifier 'Klingon'
     */
    public function testCreateContentWithInvalidLanguage( $mainLanguageCode, $structFields )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => array()
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "fields" => $structFields,
                "mainLanguageCode" => $mainLanguageCode,
                "contentType" => $contentType,
                "alwaysAvailable" => false,
                "ownerId" => 169,
                "sectionId" => 1
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ( $languageCode )
                    {
                        if ( $languageCode === "Klingon" )
                        {
                            throw new NotFoundException( "Language", "Klingon" );
                        }

                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( $contentType->id ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $that = $this;
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ),
                $this->equalTo( array() )
            )->will(
                $this->returnCallback(
                    function () use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, func_get_arg( 2 ) );
                        return true;
                    }
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "getUniqueHash" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ) )
            ->will(
                $this->returnCallback(
                    function ( $object ) use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, $object );
                        return "hash";
                    }
                )
            );

        $mockedService->createContent( $contentCreateStruct, array() );
    }

    protected function assertForCreateContentContentValidationException(
        $mainLanguageCode,
        $structFields,
        $fieldDefinitions = array()
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContentByRemoteId" ) );
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => $fieldDefinitions
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "ownerId" => 169,
                "alwaysAvailable" => false,
                "remoteId" => "faraday",
                "mainLanguageCode" => $mainLanguageCode,
                "fields" => $structFields,
                "contentType" => $contentType
            )
        );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 123 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( $contentCreateStruct ),
                $this->equalTo( array() )
            )->will( $this->returnValue( true ) );

        $mockedService->expects( $this->once() )
            ->method( "loadContentByRemoteId" )
            ->with( $contentCreateStruct->remoteId )
            ->will(
                $this->throwException( new NotFoundException( "Content", "faraday" ) )
            );

        $mockedService->createContent( $contentCreateStruct, array() );
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionFieldDefinition()
    {
        return array(
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage Field definition 'identifier' does not exist in given ContentType
     */
    public function testCreateContentThrowsContentValidationExceptionFieldDefinition( $mainLanguageCode, $structFields )
    {
        $this->assertForCreateContentContentValidationException(
            $mainLanguageCode,
            $structFields,
            array()
        );
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionTranslation()
    {
        return array(
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-US"
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage A value is set for non translatable field definition 'identifier' with language 'eng-US'
     */
    public function testCreateContentThrowsContentValidationExceptionTranslation( $mainLanguageCode, $structFields )
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
        );

        $this->assertForCreateContentContentValidationException(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );
    }

    /**
     * Asserts behaviour necessary for testing ContentValidationException because of required
     * field being empty.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return mixed
     */
    protected function assertForTestCreateContentThrowsContentValidationExceptionRequiredField(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => $fieldDefinitions,
                "nameSchema" => "<nameSchema>"
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "fields" => $structFields,
                "mainLanguageCode" => $mainLanguageCode,
                "contentType" => $contentType,
                "alwaysAvailable" => false,
                "ownerId" => 169,
                "sectionId" => 1
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( $contentType->id ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $that = $this;
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ),
                $this->equalTo( array() )
            )->will(
                $this->returnCallback(
                    function () use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, func_get_arg( 2 ) );
                        return true;
                    }
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "getUniqueHash" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ) )
            ->will(
                $this->returnCallback(
                    function ( $object ) use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, $object );
                        return "hash";
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "acceptValue" )
            ->will(
                $this->returnCallback(
                    function ( $valueString )
                    {
                        return new ValueStub( $valueString );
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects( $this->any() )
            ->method( "isEmptyValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value ) use ( $emptyValue )
                    {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "validate" )
            ->will( $this->returnValue( array() ) );

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        return $contentCreateStruct;
    }

    public function providerForTestCreateContentThrowsContentValidationExceptionRequiredField()
    {
        return array(
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            "fieldDefIdentifier" => "identifier",
                            "value" => self::EMPTY_FIELD_VALUE,
                            "languageCode" => null
                        )
                    )
                ),
                "identifier",
                "eng-US"
            ),
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionRequiredField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testCreateContentThrowsContentValidationExceptionRequiredField(
        $mainLanguageCode,
        $structFields,
        $identifier,
        $languageCode
    )
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier",
                    "isRequired" => true,
                    "defaultValue" => "defaultValue",
                )
            )
        );
        $contentCreateStruct = $this->assertForTestCreateContentThrowsContentValidationExceptionRequiredField(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions
        );

        $mockedService = $this->getPartlyMockedContentService();

        try
        {
            $mockedService->createContent( $contentCreateStruct, array() );
        }
        catch ( ContentValidationException $e )
        {
            $this->assertEquals(
                "Value for required field definition '{$identifier}' with language '{$languageCode}' is empty",
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Asserts behaviour necessary for testing ContentFieldValidationException because of
     * field not being valid.
     *
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return mixed
     */
    protected function assertForTestCreateContentThrowsContentFieldValidationException(
        $mainLanguageCode,
        array $structFields,
        array $fieldDefinitions
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $languageCodes = $this->determineLanguageCodesForCreate( $mainLanguageCode, $structFields );
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => $fieldDefinitions,
                "nameSchema" => "<nameSchema>"
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "fields" => $structFields,
                "mainLanguageCode" => $mainLanguageCode,
                "contentType" => $contentType,
                "alwaysAvailable" => false,
                "ownerId" => 169,
                "sectionId" => 1
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( $contentType->id ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $that = $this;
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ),
                $this->equalTo( array() )
            )->will(
                $this->returnCallback(
                    function () use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, func_get_arg( 2 ) );
                        return true;
                    }
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "getUniqueHash" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ) )
            ->will(
                $this->returnCallback(
                    function ( $object ) use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, $object );
                        return "hash";
                    }
                )
            );

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $relationProcessorMock
            ->expects( $this->any() )
            ->method( "appendFieldRelations" )
            ->with(
                $this->isType( "array" ),
                $this->isType( "array" ),
                $this->isInstanceOf( "eZ\\Publish\\SPI\\FieldType\\FieldType" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\FieldType\\Value" ),
                $this->anything()
            );

        $fieldValues = $this->determineValuesForCreate(
            $mainLanguageCode,
            $structFields,
            $fieldDefinitions,
            $languageCodes
        );
        $allFieldErrors = array();
        $validateCount = 0;
        $emptyValue = self::EMPTY_FIELD_VALUE;
        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            foreach ( $fieldValues[$fieldDefinition->identifier] as $languageCode => $value )
            {
                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "acceptValue" )
                    ->will(
                        $this->returnCallback(
                            function ( $valueString )
                            {
                                return new ValueStub( $valueString );
                            }
                        )
                    );

                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "isEmptyValue" )
                    ->will(
                        $this->returnCallback(
                            function ( ValueStub $value ) use ( $emptyValue )
                            {
                                return $emptyValue === (string)$value;
                            }
                        )
                    );

                if ( self::EMPTY_FIELD_VALUE === (string)$value )
                {
                    continue;
                }

                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "validate" )
                    ->with(
                        $this->equalTo( $fieldDefinition ),
                        $this->equalTo( $value )
                    )->will( $this->returnArgument( 1 ) );

                $allFieldErrors[$fieldDefinition->id][$languageCode] = $value;
            }
        }

        return array( $contentCreateStruct, $allFieldErrors );
    }

    public function providerForTestCreateContentThrowsContentFieldValidationException()
    {
        return $this->providerForTestCreateContentNonRedundantFieldSetComplex();
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @expectedExceptionMessage Content fields did not validate
     */
    public function testCreateContentThrowsContentFieldValidationException( $mainLanguageCode, $structFields )
    {
        $fieldDefinitions = $this->fixturesForTestCreateContentNonRedundantFieldSetComplex();
        list( $contentCreateStruct, $allFieldErrors ) =
            $this->assertForTestCreateContentThrowsContentFieldValidationException(
                $mainLanguageCode,
                $structFields,
                $fieldDefinitions
            );

        $mockedService = $this->getPartlyMockedContentService();

        try
        {
            $mockedService->createContent( $contentCreateStruct );
        }
        catch ( ContentFieldValidationException $e )
        {
            $this->assertEquals( $allFieldErrors, $e->getFieldErrors() );
            throw $e;
        }
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::buildSPILocationCreateStructs
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     */
    public function testCreateContentWithLocations()
    {
        $spiFields = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue",
                    "languageCode" => "eng-US"
                )
            )
        );
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        // Set up a simple case that will pass
        $locationCreateStruct1 = new LocationCreateStruct( array( "parentLocationId" => 321 ) );
        $locationCreateStruct2 = new LocationCreateStruct( array( "parentLocationId" => 654 ) );
        $locationCreateStructs = array( $locationCreateStruct1, $locationCreateStruct2 );
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            "eng-US",
            array(),
            $spiFields,
            $fieldDefinitions,
            $locationCreateStructs,
            false,
            // Do not execute
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $locationServiceMock = $this->getLocationServiceMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();
        $spiLocationCreateStruct = new SPILocation\CreateStruct();
        $parentLocation = new Location( array( 'contentInfo' => new ContentInfo( array( 'sectionId' => 1 ) ) ) );

        $locationServiceMock->expects( $this->at( 0 ) )
            ->method( "loadLocation" )
            ->with( $this->equalTo( 321 ) )
            ->will( $this->returnValue( $parentLocation ) );

        $locationServiceMock->expects( $this->at( 1 ) )
            ->method( "loadLocation" )
            ->with( $this->equalTo( 654 ) )
            ->will( $this->returnValue( $parentLocation ) );

        $repositoryMock->expects( $this->atLeastOnce() )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $domainMapperMock->expects( $this->at( 1 ) )
            ->method( "buildSPILocationCreateStruct" )
            ->with(
                $this->equalTo( $locationCreateStruct1 ),
                $this->equalTo( $parentLocation ),
                $this->equalTo( true ),
                $this->equalTo( null ),
                $this->equalTo( null )
            )->will( $this->returnValue( $spiLocationCreateStruct ) );

        $domainMapperMock->expects( $this->at( 2 ) )
            ->method( "buildSPILocationCreateStruct" )
            ->with(
                $this->equalTo( $locationCreateStruct2 ),
                $this->equalTo( $parentLocation ),
                $this->equalTo( false ),
                $this->equalTo( null ),
                $this->equalTo( null )
            )->will( $this->returnValue( $spiLocationCreateStruct ) );

        $spiContentCreateStruct = new SPIContentCreateStruct(
            array(
                "name" => array(),
                "typeId" => 123,
                "sectionId" => 1,
                "ownerId" => 169,
                "remoteId" => "hash",
                "fields" => $spiFields,
                "modified" => time(),
                "initialLanguageId" => 4242,
                "locations" => array( $spiLocationCreateStruct, $spiLocationCreateStruct )
            )
        );
        $spiContentCreateStruct2 = clone $spiContentCreateStruct;
        $spiContentCreateStruct2->modified++;

        $spiContent = new SPIContent(
            array(
                "versionInfo" => new SPIContent\VersionInfo(
                    array(
                        "contentInfo" => new SPIContent\ContentInfo( array( "id" => 42 ) ),
                        "versionNo" => 7
                    )
                )
            )
        );

        $handlerMock->expects( $this->once() )
            ->method( "create" )
            ->with( $this->logicalOr( $spiContentCreateStruct, $spiContentCreateStruct2 ) )
            ->will( $this->returnValue( $spiContent ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content" ),
                $this->equalTo( null )
            );

        $repositoryMock->expects( $this->once() )->method( "commit" );

        // Execute
        $mockedService->createContent( $contentCreateStruct, $locationCreateStructs );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::buildSPILocationCreateStructs
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Multiple LocationCreateStructs with the same parent Location '321' are given
     */
    public function testCreateContentWithLocationsDuplicateUnderParent()
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        $locationServiceMock = $this->getLocationServiceMock();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $spiLocationCreateStruct = new SPILocation\CreateStruct();
        $parentLocation = new Location( array( "id" => 321 ) );
        $locationCreateStruct = new LocationCreateStruct( array( "parentLocationId" => 321 ) );
        $locationCreateStructs = array( $locationCreateStruct, clone $locationCreateStruct );
        $contentType = new ContentType(
            array(
                "id" => 123,
                "fieldDefinitions" => $fieldDefinitions,
                "nameSchema" => "<nameSchema>"
            )
        );
        $contentCreateStruct = new ContentCreateStruct(
            array(
                "fields" => array(),
                "mainLanguageCode" => "eng-US",
                "contentType" => $contentType,
                "alwaysAvailable" => false,
                "ownerId" => 169,
                "sectionId" => 1
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( $contentType->id ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $that = $this;
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "create" ),
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ),
                $this->equalTo( $locationCreateStructs )
            )->will(
                $this->returnCallback(
                    function () use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, func_get_arg( 2 ) );
                        return true;
                    }
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "getUniqueHash" )
            ->with( $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentCreateStruct" ) )
            ->will(
                $this->returnCallback(
                    function ( $object ) use ( $that, $contentCreateStruct )
                    {
                        $that->assertEquals( $contentCreateStruct, $object );
                        return "hash";
                    }
                )
            );

        $locationServiceMock->expects( $this->once() )
            ->method( "loadLocation" )
            ->with( $this->equalTo( 321 ) )
            ->will( $this->returnValue( $parentLocation ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $domainMapperMock->expects( $this->any() )
            ->method( "buildSPILocationCreateStruct" )
            ->with(
                $this->equalTo( $locationCreateStruct ),
                $this->equalTo( $parentLocation ),
                $this->equalTo( true ),
                $this->equalTo( null ),
                $this->equalTo( null )
            )->will( $this->returnValue( $spiLocationCreateStruct ) );

        $mockedService->createContent(
            $contentCreateStruct,
            $locationCreateStructs
        );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     */
    public function testCreateContentObjectStates()
    {
        $spiFields = array(
            new SPIField(
                array(
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue",
                    "languageCode" => "eng-US"
                )
            )
        );
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );
        $objectStateGroups = array(
            new SPIObjectStateGroup( array( "id" => 10 ) ),
            new SPIObjectStateGroup( array( "id" => 20 ) )
        );

        // Set up a simple case that will pass
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            "eng-US",
            array(),
            $spiFields,
            $fieldDefinitions,
            array(),
            true,
            // Do not execute
            false
        );
        $timestamp = time();
        $contentCreateStruct->modificationDate = new \DateTime( "@{$timestamp}" );

        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $handlerMock */
        $handlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $spiContentCreateStruct = new SPIContentCreateStruct(
            array(
                "name" => array(),
                "typeId" => 123,
                "sectionId" => 1,
                "ownerId" => 169,
                "remoteId" => "hash",
                "fields" => $spiFields,
                "modified" => $timestamp,
                "initialLanguageId" => 4242,
                "locations" => array()
            )
        );
        $spiContentCreateStruct2 = clone $spiContentCreateStruct;
        $spiContentCreateStruct2->modified++;

        $spiContent = new SPIContent(
            array(
                "versionInfo" => new SPIContent\VersionInfo(
                    array(
                        "contentInfo" => new SPIContent\ContentInfo( array( "id" => 42 ) ),
                        "versionNo" => 7
                    )
                )
            )
        );

        $handlerMock->expects( $this->once() )
            ->method( "create" )
            ->with( $this->equalTo( $spiContentCreateStruct ) )
            ->will( $this->returnValue( $spiContent ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content" ),
                $this->equalTo( null )
            );

        $repositoryMock->expects( $this->once() )->method( "commit" );

        // Execute
        $mockedService->createContent( $contentCreateStruct, array() );
    }

    /**
     * Test for the createContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForCreate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::createContent
     * @dataProvider providerForTestCreateContentThrowsContentValidationExceptionTranslation
     * @expectedException \Exception
     * @expectedExceptionMessage Store failed
     */
    public function testCreateContentWithRollback()
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        // Setup a simple case that will pass
        $contentCreateStruct = $this->assertForTestCreateContentNonRedundantFieldSet(
            "eng-US",
            array(),
            array(),
            $fieldDefinitions,
            array(),
            false,
            // Do not execute test
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects( $this->never() )->method( "commit" );
        $repositoryMock->expects( $this->once() )->method( "rollback" );

        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $contentHandlerMock->expects( $this->once() )
            ->method( "create" )
            ->with( $this->anything() )
            ->will( $this->throwException( new \Exception( "Store failed" ) ) );

        // Execute
        $this->partlyMockedContentService->createContent( $contentCreateStruct, array() );
    }

    public function providerForTestUpdateContentThrowsBadStateException()
    {
        return array(
            array( VersionInfo::STATUS_PUBLISHED ),
            array( VersionInfo::STATUS_ARCHIVED )
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @dataProvider providerForTestUpdateContentThrowsBadStateException
     */
    public function testUpdateContentThrowsBadStateException( $status )
    {
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        $contentUpdateStruct = new ContentUpdateStruct();
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo( array( "id" => 42 ) ),
                "versionNo" => 7,
                "status" => $status
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => array()
            )
        );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $mockedService->updateContent( $versionInfo, $contentUpdateStruct );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentThrowsUnauthorizedException()
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        $contentUpdateStruct = new ContentUpdateStruct();
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo( array( "id" => 42 ) ),
                "versionNo" => 7,
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => array()
            )
        );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( false ) );

        $mockedService->updateContent( $versionInfo, $contentUpdateStruct );
    }

    /**
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param string[] $existingLanguages
     *
     * @return string[]
     */
    protected function determineLanguageCodesForUpdate( $initialLanguageCode, array $structFields, $existingLanguages )
    {
        $languageCodes = array_fill_keys( $existingLanguages, true );
        if ( $initialLanguageCode !== null )
        {
            $languageCodes[$initialLanguageCode] = true;
        }

        foreach ( $structFields as $field )
        {
            if ( $field->languageCode === null || isset( $languageCodes[$field->languageCode] ) )
            {
                continue;
            }

            $languageCodes[$field->languageCode] = true;
        }

        return array_keys( $languageCodes );
    }

    /**
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param string $mainLanguageCode
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     *
     * @return array
     */
    protected function mapStructFieldsForUpdate( $initialLanguageCode, $structFields, $mainLanguageCode, $fieldDefinitions )
    {
        $initialLanguageCode = $initialLanguageCode ?: $mainLanguageCode;

        $mappedFieldDefinitions = array();
        foreach ( $fieldDefinitions as $fieldDefinition )
        {
            $mappedFieldDefinitions[$fieldDefinition->identifier] = $fieldDefinition;
        }

        $mappedStructFields = array();
        foreach ( $structFields as $structField )
        {
            $identifier = $structField->fieldDefIdentifier;

            if ( $structField->languageCode !== null )
            {
                $languageCode = $structField->languageCode;
            }
            else if ( $mappedFieldDefinitions[$identifier]->isTranslatable )
            {
                $languageCode = $initialLanguageCode;
            }
            else
            {
                $languageCode = $mainLanguageCode;
            }

            $mappedStructFields[$identifier][$languageCode] = (string)$structField->value;
        }

        return $mappedStructFields;
    }

    /**
     * Returns full, possibly redundant array of field values, indexed by field definition
     * identifier and language code.
     *
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\Core\Repository\DomainLogic\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param array $languageCodes
     *
     * @return array
     */
    protected function determineValuesForUpdate(
        $initialLanguageCode,
        array $structFields,
        Content $content,
        array $fieldDefinitions,
        array $languageCodes
    )
    {
        $mainLanguageCode = $content->versionInfo->contentInfo->mainLanguageCode;

        $mappedStructFields = $this->mapStructFieldsForUpdate(
            $initialLanguageCode,
            $structFields,
            $mainLanguageCode,
            $fieldDefinitions
        );

        $values = array();

        foreach ( $fieldDefinitions as $fieldDefinition )
        {
            $identifier = $fieldDefinition->identifier;
            foreach ( $languageCodes as $languageCode )
            {
                if ( !$fieldDefinition->isTranslatable )
                {
                    if ( isset( $mappedStructFields[$identifier][$mainLanguageCode] ) )
                    {
                        $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$mainLanguageCode];
                    }
                    else
                    {
                        $values[$identifier][$languageCode] = (string)$content->fields[$identifier][$mainLanguageCode];
                    }
                    continue;
                }

                if ( isset( $mappedStructFields[$identifier][$languageCode] ) )
                {
                    $values[$identifier][$languageCode] = $mappedStructFields[$identifier][$languageCode];
                    continue;
                }

                if ( isset( $content->fields[$identifier][$languageCode] ) )
                {
                    $values[$identifier][$languageCode] = (string)$content->fields[$identifier][$languageCode];
                    continue;
                }

                $values[$identifier][$languageCode] = (string)$fieldDefinition->defaultValue;
            }
        }

        return $this->stubValues( $values );
    }

    protected function stubValues( array $fieldValues )
    {
        foreach ( $fieldValues as &$languageValues )
        {
            foreach ( $languageValues as &$value )
            {
                $value = new ValueStub( $value );
            }
        }

        return $fieldValues;
    }

    /**
     * Asserts that calling updateContent() with given API field set causes calling
     * Handler::updateContent() with given SPI field set.
     *
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $structFields
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $existingFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $fieldDefinitions
     * @param boolean $execute
     *
     * @return mixed
     */
    protected function assertForTestUpdateContentNonRedundantFieldSet(
        $initialLanguageCode,
        array $structFields,
        array $spiFields,
        array $existingFields,
        array $fieldDefinitions,
        $execute = true
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent", "loadRelations" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $domainMapperMock = $this->getDomainMapperMock();
        $relationProcessorMock = $this->getRelationProcessorMock();
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $existingLanguageCodes = array_map(
            function ( Field $field )
            {
                return $field->languageCode;
            },
            $existingFields
        );
        $languageCodes = $this->determineLanguageCodesForUpdate(
            $initialLanguageCode,
            $structFields,
            $existingLanguageCodes
        );
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo(
                    array(
                        "id" => 42,
                        "contentTypeId" => 24,
                        "mainLanguageCode" => "eng-GB"
                    )
                ),
                "versionNo" => 7,
                "languageCodes" => $existingLanguageCodes,
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => $existingFields
            )
        );
        $contentType = new ContentType( array( "fieldDefinitions" => $fieldDefinitions ) );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( true ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $this->getStubbedUser( 169 ) ) );

        $fieldTypeMock->expects( $this->any() )
            ->method( "acceptValue" )
            ->will(
                $this->returnCallback(
                    function ( $valueString )
                    {
                        return new ValueStub( $valueString );
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects( $this->any() )
            ->method( "toPersistenceValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value )
                    {
                        return (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "isEmptyValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value ) use ( $emptyValue )
                    {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "validate" )
            ->will( $this->returnValue( array() ) );

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $relationProcessorMock
            ->expects( $this->exactly( count( $fieldDefinitions ) * count( $languageCodes ) ) )
            ->method( "appendFieldRelations" )
            ->with(
                $this->isType( "array" ),
                $this->isType( "array" ),
                $this->isInstanceOf( "eZ\\Publish\\SPI\\FieldType\\FieldType" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\FieldType\\Value" ),
                $this->anything()
            );

        $values = $this->determineValuesForUpdate(
            $initialLanguageCode,
            $structFields,
            $content,
            $fieldDefinitions,
            $languageCodes
        );
        $nameSchemaServiceMock->expects( $this->once() )
            ->method( "resolveNameSchema" )
            ->with(
                $this->equalTo( $content ),
                $this->equalTo( $values ),
                $this->equalTo( $languageCodes ),
                $this->equalTo( $contentType )
            )->will( $this->returnValue( array() ) );

        $existingRelations = array( "RELATIONS!!!" );
        $mockedService->expects( $this->once() )
            ->method( "loadRelations" )
            ->with( $content->versionInfo )
            ->will( $this->returnValue( $existingRelations ) );
        $relationProcessorMock->expects( $this->any() )
            ->method( "processFieldRelations" )
            ->with(
                $this->isType( "array" ),
                $this->equalTo( 42 ),
                $this->isType( "int" ),
                $this->equalTo( $contentType ),
                $this->equalTo( $existingRelations )
            );

        $contentUpdateStruct = new ContentUpdateStruct(
            array(
                "fields" => $structFields,
                "initialLanguageCode" => $initialLanguageCode
            )
        );

        if ( $execute )
        {
            $spiContentUpdateStruct = new SPIContentUpdateStruct(
                array(
                    "creatorId" => 169,
                    "fields" => $spiFields,
                    "modificationDate" => time(),
                    "initialLanguageId" => 4242
                )
            );

            // During code coverage runs, timestamp might differ 1-3 seconds
            $spiContentUpdateStructTs1 = clone $spiContentUpdateStruct;
            $spiContentUpdateStructTs1->modificationDate++;

            $spiContentUpdateStructTs2 = clone $spiContentUpdateStructTs1;
            $spiContentUpdateStructTs2->modificationDate++;

            $spiContentUpdateStructTs3 = clone $spiContentUpdateStructTs2;
            $spiContentUpdateStructTs3->modificationDate++;

            $spiContent = new SPIContent(
                array(
                    "versionInfo" => new SPIContent\VersionInfo(
                        array(
                            "contentInfo" => new SPIContent\ContentInfo( array( "id" => 42 ) ),
                            "versionNo" => 7
                        )
                    )
                )
            );

            $contentHandlerMock->expects( $this->once() )
                ->method( "updateContent" )
                ->with(
                    42,
                    7,
                    $this->logicalOr( $spiContentUpdateStruct, $spiContentUpdateStructTs1, $spiContentUpdateStructTs2, $spiContentUpdateStructTs3 )
                )
                ->will( $this->returnValue( $spiContent ) );

            $repositoryMock->expects( $this->once() )->method( "commit" );
            $domainMapperMock->expects( $this->once() )
                ->method( "buildContentDomainObject" )
                ->with(
                    $this->isInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content" ),
                    $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" )
                );

            $mockedService->updateContent( $content->versionInfo, $contentUpdateStruct );
        }

        return array( $content->versionInfo, $contentUpdateStruct );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet1()
    {
        $spiFields = array(
            new SPIField(
                array(
                    "id" => "100",
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            )
        );

        return array(
            // With languages set
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
                $spiFields
            ),
            // Without languages set
            array(
                null,
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => null
                        )
                    )
                ),
                $spiFields
            ),
            // Adding new language without fields
            array(
                "eng-US",
                array(),
                array(),
            )
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing the simplest use case.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet1
     */
    public function testUpdateContentNonRedundantFieldSet1( $initialLanguageCode, $structFields, $spiFields )
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier",
                    "value" => "initialValue",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet2()
    {
        $spiFields0 = array(
            new SPIField(
                array(
                    "id" => "100",
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            )
        );
        $spiFields1 = array(
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            )
        );
        $spiFields2 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );

        return array(
            // 0. With languages set
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
                $spiFields0
            ),
            // 1. Without languages set
            array(
                null,
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => null
                        )
                    )
                ),
                $spiFields0
            ),
            // 2. New language with language set
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-US"
                        )
                    )
                ),
                $spiFields1
            ),
            // 3. New language without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => null
                        )
                    )
                ),
                $spiFields1
            ),
            // 4. New language and existing language with language set
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
                $spiFields2
            ),
            // 5. New language and existing language without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
                $spiFields2
            ),
            // 6. Adding new language without fields
            array(
                "eng-US",
                array(),
                array(
                    new SPIField(
                        array(
                            "id" => null,
                            "fieldDefinitionId" => "fieldDefinitionId",
                            "type" => "fieldTypeIdentifier",
                            "value" => "defaultValue",
                            "languageCode" => "eng-US",
                            "versionNo" => 7
                        )
                    )
                )
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with translatable field.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet2
     */
    public function testUpdateContentNonRedundantFieldSet2( $initialLanguageCode, $structFields, $spiFields )
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier",
                    "value" => "initialValue",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet3()
    {
        $spiFields0 = array(
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields1 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields2 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => 101,
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue3",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields3 = array(
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );

        return array(
            // 0. ew language with language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                ),
                $spiFields0
            ),
            // 1. New language without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields0
            ),
            // 2. New language and existing language with language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 3. New language and existing language without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 4. New language and existing language with untranslatable field, with language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue3",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields2
            ),
            // 5. New language and existing language with untranslatable field, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue2",
                            'languageCode' => "eng-GB"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue3",
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields2
            ),
            // 6. Adding new language without fields
            array(
                "eng-US",
                array(),
                $spiFields3
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with new language and untranslatable field.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet3
     */
    public function testUpdateContentNonRedundantFieldSet3( $initialLanguageCode, $structFields, $spiFields )
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier1",
                    "value" => "initialValue1",
                    "languageCode" => "eng-GB"
                )
            ),
            new Field(
                array(
                    "id" => "101",
                    "fieldDefIdentifier" => "identifier2",
                    "value" => "initialValue2",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier1",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue1",
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId2",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier2",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue2",
                )
            )
        );

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentNonRedundantFieldSet4()
    {
        $spiFields0 = array(
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields1 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => self::EMPTY_FIELD_VALUE,
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields2 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => self::EMPTY_FIELD_VALUE,
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
        );

        return array(
            // 0. New translation with empty field by default
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                ),
                $spiFields0
            ),
            // 1. New translation with empty field by default, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields0
            ),
            // 2. New translation with empty field given
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-US"
                        )
                    ),
                ),
                $spiFields0
            ),
            // 3. New translation with empty field given, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields0
            ),
            // 4. Updating existing language with empty value
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 5. Updating existing language with empty value, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 6. Updating existing language with empty value and adding new language with empty value
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields2
            ),
            // 7. Updating existing language with empty value and adding new language with empty value,
            // without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields2
            ),
            // 8. Adding new language with no fields given
            array(
                "eng-US",
                array(),
                array()
            ),
            // 9. Adding new language with fields
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => "eng-US"
                        )
                    ),
                ),
                array()
            ),
            // 10. Adding new language with fields, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => self::EMPTY_FIELD_VALUE,
                            'languageCode' => null
                        )
                    ),
                ),
                array()
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing with empty values.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSet4
     */
    public function testUpdateContentNonRedundantFieldSet4( $initialLanguageCode, $structFields, $spiFields )
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier1",
                    "value" => "initialValue1",
                    "languageCode" => "eng-GB"
                )
            ),
            new Field(
                array(
                    "id" => "101",
                    "fieldDefIdentifier" => "identifier2",
                    "value" => "initialValue2",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier1",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId2",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier2",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            )
        );

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    /**
     * @todo add first field empty
     * @return array
     */
    public function providerForTestUpdateContentNonRedundantFieldSetComplex()
    {
        $spiFields0 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1-eng-GB",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue4",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields1 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1-eng-GB",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue4",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );
        $spiFields2 = array(
            new SPIField(
                array(
                    "id" => 100,
                    "fieldDefinitionId" => "fieldDefinitionId1",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue1-eng-GB",
                    "languageCode" => "eng-GB",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId2",
                    "type" => "fieldTypeIdentifier",
                    "value" => "newValue2",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue4",
                    "languageCode" => "ger-DE",
                    "versionNo" => 7
                )
            ),
            new SPIField(
                array(
                    "id" => null,
                    "fieldDefinitionId" => "fieldDefinitionId4",
                    "type" => "fieldTypeIdentifier",
                    "value" => "defaultValue4",
                    "languageCode" => "eng-US",
                    "versionNo" => 7
                )
            ),
        );

        return array(
            // 0. Add new language and update existing
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier4",
                            'value' => "newValue4",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields0
            ),
            // 1. Add new language and update existing, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier4",
                            'value' => "newValue4",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields0
            ),
            // 2. Add new language and update existing variant
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 3. Add new language and update existing variant, without language set
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => null
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields1
            ),
            // 4. Update with multiple languages
            array(
                "ger-DE",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => "eng-GB"
                        )
                    ),
                ),
                $spiFields2
            ),
            // 5. Update with multiple languages without language set
            array(
                "ger-DE",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier2",
                            'value' => "newValue2",
                            'languageCode' => "eng-US"
                        )
                    ),
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier1",
                            'value' => "newValue1-eng-GB",
                            'languageCode' => null
                        )
                    ),
                ),
                $spiFields2
            ),
        );
    }

    protected function fixturesForTestUpdateContentNonRedundantFieldSetComplex()
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier1",
                    "value" => "initialValue1",
                    "languageCode" => "eng-GB"
                )
            ),
            new Field(
                array(
                    "id" => "101",
                    "fieldDefIdentifier" => "identifier2",
                    "value" => "initialValue2",
                    "languageCode" => "eng-GB"
                )
            ),
            new Field(
                array(
                    "id" => "102",
                    "fieldDefIdentifier" => "identifier3",
                    "value" => "initialValue3",
                    "languageCode" => "eng-GB"
                )
            ),
            new Field(
                array(
                    "id" => "103",
                    "fieldDefIdentifier" => "identifier4",
                    "value" => "initialValue4",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier1",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId2",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier2",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId3",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier3",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue3",
                )
            ),
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId4",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier4",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue4",
                )
            )
        );

        return array( $existingFields, $fieldDefinitions );
    }

    /**
     * Test for the updateContent() method.
     *
     * Testing more complex cases.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentNonRedundantFieldSetComplex
     */
    public function testUpdateContentNonRedundantFieldSetComplex( $initialLanguageCode, $structFields, $spiFields )
    {
        list( $existingFields, $fieldDefinitions ) = $this->fixturesForTestUpdateContentNonRedundantFieldSetComplex();

        $this->assertForTestUpdateContentNonRedundantFieldSet(
            $initialLanguageCode,
            $structFields,
            $spiFields,
            $existingFields,
            $fieldDefinitions
        );
    }

    public function providerForTestUpdateContentWithInvalidLanguage()
    {
        return array(
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "Klingon"
                        )
                    )
                ),
            ),
            array(
                "Klingon",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentWithInvalidLanguage
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @expectedExceptionMessage Could not find 'Language' with identifier 'Klingon'
     */
    public function testUpdateContentWithInvalidLanguage( $initialLanguageCode, $structFields )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo(
                    array(
                        "id" => 42,
                        "contentTypeId" => 24,
                        "mainLanguageCode" => "eng-GB"
                    )
                ),
                "versionNo" => 7,
                "languageCodes" => array( "eng-GB" ),
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => array()
            )
        );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ( $languageCode )
                    {
                        if ( $languageCode === "Klingon" )
                        {
                            throw new NotFoundException( "Language", "Klingon" );
                        }

                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( true ) );

        $contentUpdateStruct = new ContentUpdateStruct(
            array(
                "fields" => $structFields,
                "initialLanguageCode" => $initialLanguageCode
            )
        );

        $mockedService->updateContent( $content->versionInfo, $contentUpdateStruct );
    }

    protected function assertForUpdateContentContentValidationException(
        $initialLanguageCode,
        $structFields,
        $fieldDefinitions = array()
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo(
                    array(
                        "id" => 42,
                        "contentTypeId" => 24,
                        "mainLanguageCode" => "eng-GB"
                    )
                ),
                "versionNo" => 7,
                "languageCodes" => array( "eng-GB" ),
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => array()
            )
        );
        $contentType = new ContentType( array( "fieldDefinitions" => $fieldDefinitions ) );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ( $languageCode )
                    {
                        if ( $languageCode === "Klingon" )
                        {
                            throw new NotFoundException( "Language", "Klingon" );
                        }

                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( true ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $contentUpdateStruct = new ContentUpdateStruct(
            array(
                "fields" => $structFields,
                "initialLanguageCode" => $initialLanguageCode
            )
        );

        $mockedService->updateContent( $content->versionInfo, $contentUpdateStruct );
    }

    public function providerForTestUpdateContentThrowsContentValidationExceptionFieldDefinition()
    {
        return array(
            array(
                "eng-GB",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-GB"
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentValidationExceptionFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage Field definition 'identifier' does not exist in given ContentType
     */
    public function testUpdateContentThrowsContentValidationExceptionFieldDefinition( $initialLanguageCode, $structFields )
    {
        $this->assertForUpdateContentContentValidationException(
            $initialLanguageCode,
            $structFields,
            array()
        );
    }

    public function providerForTestUpdateContentThrowsContentValidationExceptionTranslation()
    {
        return array(
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            'fieldDefIdentifier' => "identifier",
                            'value' => "newValue",
                            'languageCode' => "eng-US"
                        )
                    )
                ),
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentValidationExceptionTranslation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @expectedExceptionMessage A value is set for non translatable field definition 'identifier' with language 'eng-US'
     */
    public function testUpdateContentThrowsContentValidationExceptionTranslation( $initialLanguageCode, $structFields )
    {
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId1",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => self::EMPTY_FIELD_VALUE,
                )
            ),
        );

        $this->assertForUpdateContentContentValidationException(
            $initialLanguageCode,
            $structFields,
            $fieldDefinitions
        );
    }

    public function assertForTestUpdateContentThrowsContentValidationExceptionRequiredField(
        $initialLanguageCode,
        $structFields,
        $existingFields,
        $fieldDefinitions
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $existingLanguageCodes = array_map(
            function ( Field $field )
            {
                return $field->languageCode;
            },
            $existingFields
        );
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo(
                    array(
                        "id" => 42,
                        "contentTypeId" => 24,
                        "mainLanguageCode" => "eng-GB"
                    )
                ),
                "versionNo" => 7,
                "languageCodes" => $existingLanguageCodes,
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => $existingFields
            )
        );
        $contentType = new ContentType( array( "fieldDefinitions" => $fieldDefinitions ) );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( true ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $fieldTypeMock->expects( $this->any() )
            ->method( "acceptValue" )
            ->will(
                $this->returnCallback(
                    function ( $valueString )
                    {
                        return new ValueStub( $valueString );
                    }
                )
            );

        $emptyValue = self::EMPTY_FIELD_VALUE;
        $fieldTypeMock->expects( $this->any() )
            ->method( "isEmptyValue" )
            ->will(
                $this->returnCallback(
                    function ( ValueStub $value ) use ( $emptyValue )
                    {
                        return $emptyValue === (string)$value;
                    }
                )
            );

        $fieldTypeMock->expects( $this->any() )
            ->method( "validate" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinition" ),
                $this->isInstanceOf( "eZ\\Publish\\Core\\FieldType\\Value" )
            );

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $contentUpdateStruct = new ContentUpdateStruct(
            array(
                "fields" => $structFields,
                "initialLanguageCode" => $initialLanguageCode
            )
        );

        return array( $content->versionInfo, $contentUpdateStruct );
    }

    public function providerForTestUpdateContentThrowsContentValidationExceptionRequiredField()
    {
        return array(
            array(
                "eng-US",
                array(
                    new Field(
                        array(
                            "fieldDefIdentifier" => "identifier",
                            "value" => self::EMPTY_FIELD_VALUE,
                            "languageCode" => null
                        )
                    )
                ),
                "identifier",
                "eng-US"
            ),
        );
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentValidationExceptionRequiredField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     */
    public function testUpdateContentThrowsContentValidationExceptionRequiredField(
        $initialLanguageCode,
        $structFields,
        $identifier,
        $languageCode
    )
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier",
                    "value" => "initialValue",
                    "languageCode" => "eng-GB"
                )
            )
        );
        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => true,
                    "identifier" => "identifier",
                    "isRequired" => true,
                    "defaultValue" => "defaultValue",
                )
            )
        );
        list( $versionInfo, $contentUpdateStruct ) =
            $this->assertForTestUpdateContentThrowsContentValidationExceptionRequiredField(
                $initialLanguageCode,
                $structFields,
                $existingFields,
                $fieldDefinitions
            );

        try
        {
            $this->partlyMockedContentService->updateContent( $versionInfo, $contentUpdateStruct );
        }
        catch ( ContentValidationException $e )
        {
            $this->assertEquals(
                "Value for required field definition '{$identifier}' with language '{$languageCode}' is empty",
                $e->getMessage()
            );

            throw $e;
        }
    }

    public function assertForTestUpdateContentThrowsContentFieldValidationException(
        $initialLanguageCode,
        $structFields,
        $existingFields,
        $fieldDefinitions
    )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedContentService( array( "loadContent" ) );
        /** @var \PHPUnit_Framework_MockObject_MockObject $languageHandlerMock */
        $languageHandlerMock = $this->getPersistenceMock()->contentLanguageHandler();
        $contentTypeServiceMock = $this->getContentTypeServiceMock();
        $fieldTypeServiceMock = $this->getFieldTypeServiceMock();
        $fieldTypeMock = $this->getMock( "eZ\\Publish\\SPI\\FieldType\\FieldType" );
        $existingLanguageCodes = array_map(
            function ( Field $field )
            {
                return $field->languageCode;
            },
            $existingFields
        );
        $languageCodes = $this->determineLanguageCodesForUpdate(
            $initialLanguageCode,
            $structFields,
            $existingLanguageCodes
        );
        $versionInfo = new VersionInfo(
            array(
                "contentInfo" => new ContentInfo(
                    array(
                        "id" => 42,
                        "contentTypeId" => 24,
                        "mainLanguageCode" => "eng-GB"
                    )
                ),
                "versionNo" => 7,
                "languageCodes" => $existingLanguageCodes,
                "status" => VersionInfo::STATUS_DRAFT
            )
        );
        $content = new Content(
            array(
                "versionInfo" => $versionInfo,
                "internalFields" => $existingFields
            )
        );
        $contentType = new ContentType( array( "fieldDefinitions" => $fieldDefinitions ) );

        $languageHandlerMock->expects( $this->any() )
            ->method( "loadByLanguageCode" )
            ->with( $this->isType( "string" ) )
            ->will(
                $this->returnCallback(
                    function ()
                    {
                        return new Language( array( "id" => 4242 ) );
                    }
                )
            );

        $mockedService->expects( $this->once() )
            ->method( "loadContent" )
            ->with(
                $this->equalTo( 42 ),
                $this->equalTo( null ),
                $this->equalTo( 7 )
            )->will(
                $this->returnValue( $content )
            );

        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                $this->equalTo( "content" ),
                $this->equalTo( "edit" ),
                $this->equalTo( $content )
            )->will( $this->returnValue( true ) );

        $contentTypeServiceMock->expects( $this->once() )
            ->method( "loadContentType" )
            ->with( $this->equalTo( 24 ) )
            ->will( $this->returnValue( $contentType ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $fieldValues = $this->determineValuesForUpdate(
            $initialLanguageCode,
            $structFields,
            $content,
            $fieldDefinitions,
            $languageCodes
        );
        $allFieldErrors = array();
        $validateCount = 0;
        $emptyValue = self::EMPTY_FIELD_VALUE;
        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            foreach ( $fieldValues[$fieldDefinition->identifier] as $languageCode => $value )
            {
                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "acceptValue" )
                    ->will(
                        $this->returnCallback(
                            function ( $valueString )
                            {
                                return new ValueStub( $valueString );
                            }
                        )
                    );

                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "isEmptyValue" )
                    ->will(
                        $this->returnCallback(
                            function ( ValueStub $value ) use ( $emptyValue )
                            {
                                return $emptyValue === (string)$value;
                            }
                        )
                    );

                if ( self::EMPTY_FIELD_VALUE === (string)$value )
                {
                    continue;
                }

                $fieldTypeMock->expects( $this->at( $validateCount++ ) )
                    ->method( "validate" )
                    ->with(
                        $this->equalTo( $fieldDefinition ),
                        $this->equalTo( $value )
                    )->will( $this->returnArgument( 1 ) );

                $allFieldErrors[$fieldDefinition->id][$languageCode] = $value;
            }
        }

        $fieldTypeServiceMock->expects( $this->any() )
            ->method( "buildFieldType" )
            ->will( $this->returnValue( $fieldTypeMock ) );

        $repositoryMock->expects( $this->any() )
            ->method( "getFieldTypeService" )
            ->will( $this->returnValue( $fieldTypeServiceMock ) );

        $contentUpdateStruct = new ContentUpdateStruct(
            array(
                "fields" => $structFields,
                "initialLanguageCode" => $initialLanguageCode
            )
        );

        return array( $content->versionInfo, $contentUpdateStruct, $allFieldErrors );
    }

    public function providerForTestUpdateContentThrowsContentFieldValidationException()
    {
        return $this->providerForTestUpdateContentNonRedundantFieldSetComplex();
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @dataProvider providerForTestUpdateContentThrowsContentFieldValidationException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @expectedExceptionMessage Content fields did not validate
     */
    public function testUpdateContentThrowsContentFieldValidationException( $initialLanguageCode, $structFields )
    {
        list( $existingFields, $fieldDefinitions ) = $this->fixturesForTestUpdateContentNonRedundantFieldSetComplex();
        list( $versionInfo, $contentUpdateStruct, $allFieldErrors ) =
            $this->assertForTestUpdateContentThrowsContentFieldValidationException(
                $initialLanguageCode,
                $structFields,
                $existingFields,
                $fieldDefinitions
            );

        try
        {
            $this->partlyMockedContentService->updateContent( $versionInfo, $contentUpdateStruct );
        }
        catch ( ContentFieldValidationException $e )
        {
            $this->assertEquals( $allFieldErrors, $e->getFieldErrors() );
            throw $e;
        }
    }

    /**
     * Test for the updateContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getLanguageCodesForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::mapFieldsForUpdate
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::updateContent
     * @expectedException \Exception
     * @expectedExceptionMessage Store failed
     */
    public function testUpdateContentTransactionRollback()
    {
        $existingFields = array(
            new Field(
                array(
                    "id" => "100",
                    "fieldDefIdentifier" => "identifier",
                    "value" => "initialValue",
                    "languageCode" => "eng-GB"
                )
            )
        );

        $fieldDefinitions = array(
            new FieldDefinition(
                array(
                    "id" => "fieldDefinitionId",
                    "fieldTypeIdentifier" => "fieldTypeIdentifier",
                    "isTranslatable" => false,
                    "identifier" => "identifier",
                    "isRequired" => false,
                    "defaultValue" => "defaultValue",
                )
            )
        );

        // Setup a simple case that will pass
        list( $versionInfo, $contentUpdateStruct ) = $this->assertForTestUpdateContentNonRedundantFieldSet(
            "eng-US",
            array(),
            array(),
            $existingFields,
            $fieldDefinitions,
            // Do not execute test
            false
        );

        $repositoryMock = $this->getRepositoryMock();
        $repositoryMock->expects( $this->never() )->method( "commit" );
        $repositoryMock->expects( $this->once() )->method( "rollback" );

        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $contentHandlerMock->expects( $this->once() )
            ->method( "updateContent" )
            ->with(
                $this->anything(),
                $this->anything(),
                $this->anything()
            )->will( $this->throwException( new \Exception( "Store failed" ) ) );

        // Execute
        $this->partlyMockedContentService->updateContent( $versionInfo, $contentUpdateStruct );
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::copyContent
     * @expectedException \eZ\Publish\Core\Base\Exceptions\UnauthorizedException
     */
    public function testCopyContentThrowsUnauthorizedException()
    {
        $repository = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        $contentInfo = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $locationCreateStruct = new LocationCreateStruct();

        $contentInfo->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $repository->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                "content",
                "create",
                $contentInfo,
                $locationCreateStruct
            )
            ->will( $this->returnValue( false ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo */
        $contentService->copyContent( $contentInfo, $locationCreateStruct  );
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::internalPublishVersion
     */
    public function testCopyContent()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        $locationServiceMock = $this->getLocationServiceMock();
        $contentInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $locationCreateStruct = new LocationCreateStruct();

        $repositoryMock->expects( $this->exactly( 2 ) )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $contentInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "versionNo", 123 ),
                        array( "status", VersionInfo::STATUS_DRAFT ),
                    )
                )
            );
        $versionInfoMock->expects( $this->once() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfoMock ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "commit" );
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                "content",
                "create",
                $contentInfoMock,
                $locationCreateStruct
            )
            ->will( $this->returnValue( true ) );

        $spiContentInfo = new SPIContentInfo( array( "id" => 42 ) );
        $spiVersionInfo = new SPIVersionInfo(
            array(
                "contentInfo" => $spiContentInfo,
                "creationDate" => 123456
            )
        );
        $spiContent = new SPIContent( array( "versionInfo" => $spiVersionInfo ) );
        $contentHandlerMock->expects( $this->once() )
            ->method( "copy" )
            ->with( 42, null )
            ->will( $this->returnValue( $spiContent ) );

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( $spiVersionInfo )
            ->will( $this->returnValue( $versionInfoMock ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfoMock */
        $content = $this->mockPublishVersion( 123456 );
        $locationServiceMock->expects( $this->once() )
            ->method( "createLocation" )
            ->with(
                $content->getVersionInfo()->getContentInfo(),
                $locationCreateStruct
            );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent( $contentInfoMock, $locationCreateStruct, null );
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::internalPublishVersion
     */
    public function testCopyContentWithVersionInfo()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService( array( "internalLoadContentInfo" ) );
        $locationServiceMock = $this->getLocationServiceMock();
        $contentInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $locationCreateStruct = new LocationCreateStruct();

        $repositoryMock->expects( $this->exactly( 2 ) )
            ->method( "getLocationService" )
            ->will( $this->returnValue( $locationServiceMock ) );

        $contentInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );

        $versionInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "versionNo", 123 ),
                        array( "status", VersionInfo::STATUS_DRAFT ),
                    )
                )
            );
        $versionInfoMock->expects( $this->once() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfoMock ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "commit" );
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                "content",
                "create",
                $contentInfoMock,
                $locationCreateStruct
            )
            ->will( $this->returnValue( true ) );

        $spiContentInfo = new SPIContentInfo( array( "id" => 42 ) );
        $spiVersionInfo = new SPIVersionInfo(
            array(
                "contentInfo" => $spiContentInfo,
                "creationDate" => 123456
            )
        );
        $spiContent = new SPIContent( array( "versionInfo" => $spiVersionInfo ) );
        $contentHandlerMock->expects( $this->once() )
            ->method( "copy" )
            ->with( 42, 123 )
            ->will( $this->returnValue( $spiContent ) );

        $this->mockGetDefaultObjectStates();
        $this->mockSetDefaultObjectStates();

        $domainMapperMock->expects( $this->once() )
            ->method( "buildVersionInfoDomainObject" )
            ->with( $spiVersionInfo )
            ->will( $this->returnValue( $versionInfoMock ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfoMock */
        $content = $this->mockPublishVersion( 123456 );
        $locationServiceMock->expects( $this->once() )
            ->method( "createLocation" )
            ->with(
                $content->getVersionInfo()->getContentInfo(),
                $locationCreateStruct
            );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent( $contentInfoMock, $locationCreateStruct, $versionInfoMock );
    }

    /**
     * Test for the copyContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::copyContent
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::getDefaultObjectStates
     * @covers \eZ\Publish\Core\Repository\DomainLogic\ContentService::internalPublishVersion
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testCopyContentWithRollback()
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentService = $this->getPartlyMockedContentService();
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $locationCreateStruct = new LocationCreateStruct();
        $contentInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $contentInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 42 ) );

        $this->mockGetDefaultObjectStates();

        $repositoryMock->expects( $this->once() )->method( "beginTransaction" );
        $repositoryMock->expects( $this->once() )->method( "rollback" );
        $repositoryMock->expects( $this->once() )
            ->method( "canUser" )
            ->with(
                "content",
                "create",
                $contentInfoMock,
                $locationCreateStruct
            )
            ->will( $this->returnValue( true ) );

        $contentHandlerMock->expects( $this->once() )
            ->method( "copy" )
            ->with( 42, null )
            ->will( $this->throwException( new Exception( "Handler threw an exception" ) ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfoMock */
        $contentService->copyContent( $contentInfoMock, $locationCreateStruct, null );
    }

    /**
     * @return void
     */
    protected function mockGetDefaultObjectStates()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();

        $objectStateGroups = array(
            new SPIObjectStateGroup( array( "id" => 10 ) ),
            new SPIObjectStateGroup( array( "id" => 20 ) )
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject $objectStateHandlerMock */
        $objectStateHandlerMock->expects( $this->once() )
            ->method( "loadAllGroups" )
            ->will( $this->returnValue( $objectStateGroups ) );

        $objectStateHandlerMock->expects( $this->at( 1 ) )
            ->method( "loadObjectStates" )
            ->with( $this->equalTo( 10 ) )
            ->will(
                $this->returnValue(
                    array(
                        new SPIObjectState( array( "id" => 11, "groupId" => 10 ) ),
                        new SPIObjectState( array( "id" => 12, "groupId" => 10 ) )
                    )
                )
            );

        $objectStateHandlerMock->expects( $this->at( 2 ) )
            ->method( "loadObjectStates" )
            ->with( $this->equalTo( 20 ) )
            ->will(
                $this->returnValue(
                    array(
                        new SPIObjectState( array( "id" => 21, "groupId" => 20 ) ),
                        new SPIObjectState( array( "id" => 22, "groupId" => 20 ) )
                    )
                )
            );
    }

    /**
     * @return void
     */
    protected function mockSetDefaultObjectStates()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $objectStateHandlerMock */
        $objectStateHandlerMock = $this->getPersistenceMock()->objectStateHandler();

        $defaultObjectStates = array(
            new SPIObjectState( array( "id" => 11, "groupId" => 10 ) ),
            new SPIObjectState( array( "id" => 21, "groupId" => 20 ) )
        );
        foreach ( $defaultObjectStates as $index => $objectState )
        {
            $objectStateHandlerMock->expects( $this->at( $index + 3 ) )
                ->method( "setContentState" )
                ->with(
                    42,
                    $objectState->groupId,
                    $objectState->id
                );
        }

    }

    /**
     * @param int|null $publicationDate
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function mockPublishVersion( $publicationDate = null )
    {
        $domainMapperMock = $this->getDomainMapperMock();
        $contentMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );
        /** @var \PHPUnit_Framework_MockObject_MockObject $contentHandlerMock */
        $versionInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\VersionInfo" );
        $contentInfoMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo" );
        $contentHandlerMock = $this->getPersistenceMock()->contentHandler();
        $metadataUpdateStruct = new SPIMetadataUpdateStruct();

        $contentMock->expects( $this->any() )
            ->method( "__get" )
            ->with( "contentInfo" )
            ->will( $this->returnValue( $contentInfoMock ) );
        $contentMock->expects( $this->any() )
            ->method( "getVersionInfo" )
            ->will( $this->returnValue( $versionInfoMock ) );
        $versionInfoMock->expects( $this->any() )
            ->method( "getContentInfo" )
            ->will( $this->returnValue( $contentInfoMock ) );
        $contentInfoMock->expects( $this->any() )
            ->method( "__get" )
            ->will(
                $this->returnValueMap(
                    array(
                        array( "alwaysAvailable", true ),
                        array( "mainLanguageCode", "eng-GB" ),
                    )
                )
            );

        // Account for 1 second of test execution time
        $metadataUpdateStruct->publicationDate = isset( $publicationDate ) ? $publicationDate : time();
        $metadataUpdateStruct->modificationDate = $metadataUpdateStruct->publicationDate;
        $metadataUpdateStruct2 = clone $metadataUpdateStruct;
        $metadataUpdateStruct2->publicationDate++;
        $metadataUpdateStruct2->modificationDate++;

        $spiContent = new SPIContent();
        $contentHandlerMock->expects( $this->once() )
            ->method( "publish" )
            ->with(
                42,
                123,
                $this->logicalOr( $metadataUpdateStruct, $metadataUpdateStruct2 )
            )
            ->will( $this->returnValue( $spiContent ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $spiContent )
            ->will( $this->returnValue( $contentMock ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\Content $contentMock */
        $this->mockPublishUrlAliasesForContent( $contentMock );

        return $contentMock;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     */
    protected function mockPublishUrlAliasesForContent( APIContent $content )
    {
        $nameSchemaServiceMock = $this->getNameSchemaServiceMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlAliasHandlerMock */
        $urlAliasHandlerMock = $this->getPersistenceMock()->urlAliasHandler();
        $locationServiceMock = $this->getLocationServiceMock();
        $location = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location" );

        $location->expects( $this->at( 0 ) )
            ->method( "__get" )
            ->with( "id" )
            ->will( $this->returnValue( 123 ) );
        $location->expects( $this->at( 1 ) )
            ->method( "__get" )
            ->with( "parentLocationId" )
            ->will( $this->returnValue( 456 ) );

        $urlAliasNames = array( "eng-GB" => "hello" );
        $nameSchemaServiceMock->expects( $this->once() )
            ->method( "resolveUrlAliasSchema" )
            ->with( $content )
            ->will( $this->returnValue( $urlAliasNames ) );

        $locationServiceMock->expects( $this->once() )
            ->method( "loadLocations" )
            ->with( $content->getVersionInfo()->getContentInfo() )
            ->will( $this->returnValue( array( $location ) ) );

        $urlAliasHandlerMock->expects( $this->once() )
            ->method( "publishUrlAliasForLocation" )
            ->with( 123, 456, "hello", "eng-GB", true, true );
    }

    protected $domainMapperMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\DomainLogic\DomainMapper
     */
    protected function getDomainMapperMock()
    {
        if ( !isset( $this->domainMapperMock ) )
        {
            $this->domainMapperMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\DomainMapper" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->domainMapperMock;
    }

    protected $relationProcessorMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\DomainLogic\RelationProcessor
     */
    protected function getRelationProcessorMock()
    {
        if ( !isset( $this->relationProcessorMock ) )
        {
            $this->relationProcessorMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\RelationProcessor" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->relationProcessorMock;
    }

    protected $nameSchemaServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\DomainLogic\NameSchemaService
     */
    protected function getNameSchemaServiceMock()
    {
        if ( !isset( $this->nameSchemaServiceMock ) )
        {
            $this->nameSchemaServiceMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\NameSchemaService" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->nameSchemaServiceMock;
    }

    protected $fieldTypeServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\FieldTypeService
     */
    protected function getFieldTypeServiceMock()
    {
        if ( !isset( $this->fieldTypeServiceMock ) )
        {
            $this->fieldTypeServiceMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainLogic\\FieldTypeService" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->fieldTypeServiceMock;
    }

    protected $contentTypeServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\ContentTypeService
     */
    protected function getContentTypeServiceMock()
    {
        if ( !isset( $this->contentTypeServiceMock ) )
        {
            $this->contentTypeServiceMock = $this
                ->getMockBuilder( "eZ\\Publish\\API\\Repository\\ContentTypeService" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->contentTypeServiceMock;
    }

    protected $locationServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\LocationService
     */
    protected function getLocationServiceMock()
    {
        if ( !isset( $this->locationServiceMock ) )
        {
            $this->locationServiceMock = $this
                ->getMockBuilder( "eZ\\Publish\\API\\Repository\\LocationService" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->locationServiceMock;
    }

    /**
     * @var \eZ\Publish\Core\Repository\DomainLogic\ContentService
     */
    protected $partlyMockedContentService;

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\DomainLogic\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedContentService( array $methods = null )
    {
        if ( !isset( $this->partlyMockedContentService ) )
        {
            $this->partlyMockedContentService = $this->getMock(
                "eZ\\Publish\\Core\\Repository\\DomainLogic\\ContentService",
                $methods,
                array(
                    $this->getRepositoryMock(),
                    $this->getPersistenceMock(),
                    $this->getDomainMapperMock(),
                    $this->getRelationProcessorMock(),
                    $this->getNameSchemaServiceMock(),
                    array()
                )
            );
        }

        return $this->partlyMockedContentService;
    }
}
