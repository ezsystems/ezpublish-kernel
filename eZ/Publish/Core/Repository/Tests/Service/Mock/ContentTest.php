<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\ContentTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * Mock test case for Content service
 */
class ContentTest extends BaseServiceMockTest
{
    /**
     * Test for the loadVersionInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadVersionInfo
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
        );

        $contentServiceMock->loadVersionInfo(
            new ContentInfo( array( "id" => 42 ) ),
            7
        );
    }

    /**
     * Test for the loadContentByContentInfo() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByContentInfo
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
     * @covers \eZ\Publish\Core\Repository\ContentService::loadContentByVersionInfo
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
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
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
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
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
     * @covers \eZ\Publish\Core\Repository\ContentService::deleteContent
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

    protected $domainMapperMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\DomainMapper
     */
    protected function getDomainMapperMock()
    {
        if ( !isset( $this->domainMapperMock ) )
        {
            $this->domainMapperMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainMapper" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->domainMapperMock;
    }

    protected $relationProcessorMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\RelationProcessor
     */
    protected function getRelationProcessorMock()
    {
        if ( !isset( $this->relationProcessorMock ) )
        {
            $this->relationProcessorMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\RelationProcessor" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->relationProcessorMock;
    }

    protected $nameSchemaServiceMock;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\NameSchemaService
     */
    protected function getNameSchemaServiceMock()
    {
        if ( !isset( $this->nameSchemaServiceMock ) )
        {
            $this->nameSchemaServiceMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\NameSchemaService" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->nameSchemaServiceMock;
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedContentService( array $methods = null )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\ContentService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock(),
                array(),
                $this->getDomainMapperMock(),
                $this->getRelationProcessorMock(),
                $this->getNameSchemaServiceMock()
            )
        );
    }
}
