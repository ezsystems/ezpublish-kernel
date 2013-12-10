<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\SearchTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Exception;

/**
 * Mock test case for Search service
 */
class SearchTest extends BaseServiceMockTest
{
    protected $repositoryMock;

    protected $domainMapperMock;

    protected $permissionsCriterionHandlerMock;

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::__construct
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $settings = array( "teh setting" );

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionHandlerMock,
            $settings
        );

        $this->assertAttributeSame(
            $repositoryMock,
            "repository",
            $service
        );

        $this->assertAttributeSame(
            $searchHandlerMock,
            "searchHandler",
            $service
        );

        $this->assertAttributeSame(
            $domainMapperMock,
            "domainMapper",
            $service
        );

        $this->assertAttributeSame(
            $permissionsCriterionHandlerMock,
            "permissionsCriterionHandler",
            $service
        );

        $this->assertAttributeSame(
            $settings,
            "settings",
            $service
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testFindContentThrowsHandlerException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionHandlerMock,
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "filter" => $criterionMock ) );

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->throwException( new Exception( "Handler threw an exception" ) ) );

        $service->findContent( $query, array(), true );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentNoPermissionsFilter()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionHandlerMock,
            array()
        );

        $repositoryMock->expects( $this->never() )->method( "hasAccess" );

        $serviceQuery = new Query;
        $handlerQuery = new Query( array( "filter" => new Criterion\MatchAll(), "limit" => SearchService::MAX_LIMIT ) );
        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->once() )
            ->method( "findContent" )
            ->with( $this->equalTo( $handlerQuery ), $this->equalTo( $fieldFilters ) )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            "searchHits" => array( new SearchHit( array( "valueObject" => $spiContent ) ) ),
                            "totalCount" => 1
                        )
                    )
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        $result = $service->findContent( $serviceQuery, $fieldFilters, false );

        $this->assertEquals(
            new SearchResult(
                array(
                    "searchHits" => array( new SearchHit( array( "valueObject" => $contentMock ) ) ),
                    "totalCount" => 1
                )
            ),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionHandlerMock,
            array()
        );

        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "filter" => $criterionMock, "limit" => 10 ) );
        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->once() )
            ->method( "findContent" )
            ->with( $this->equalTo( $query ), $this->equalTo( $fieldFilters ) )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            "searchHits" => array( new SearchHit( array( "valueObject" => $spiContent ) ) ),
                            "totalCount" => 1
                        )
                    )
                )
            );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->returnValue( true ) );

        $result = $service->findContent( $query, $fieldFilters, true );

        $this->assertEquals(
            new SearchResult(
                array(
                    "searchHits" => array( new SearchHit( array( "valueObject" => $contentMock ) ) ),
                    "totalCount" => 1
                )
            ),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithNoPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionHandlerMock,
            array()
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->never() )->method( "findContent" );

        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "filter" => $criterionMock ) );

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->returnValue( false ) );

        $result = $service->findContent( $query, array(), true );

        $this->assertEquals(
            new SearchResult( array( "time" => 0, "totalCount" => 0 ) ),
            $result
        );
    }

    public function providerForTestFindContentValidatesFieldSortClauses()
    {
        $fieldSortClause1 = new SortClause\Field(
            "testContentTypeIdentifier",
            "testFieldDefinitionIdentifier",
            Query::SORT_ASC
        );
        $fieldSortClause2 = new SortClause\Field(
            "testContentTypeIdentifier",
            "testFieldDefinitionIdentifier",
            Query::SORT_ASC,
            "eng-GB"
        );

        return array(
            array(
                array( new SortClause\ContentId(), $fieldSortClause1 ),
                true,
                false,
                "Argument '\$query->sortClauses[1]' is invalid: No language is specified for translatable field"
            ),
            array(
                array( $fieldSortClause2 ),
                false,
                false,
                "Argument '\$query->sortClauses[0]' is invalid: Language is specified for non-translatable field," .
                " null should be used instead"
            ),
            array(
                array( $fieldSortClause1 ),
                false,
                true
            ),
            array(
                array( $fieldSortClause2 ),
                true,
                true
            ),
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @dataProvider providerForTestFindContentValidatesFieldSortClauses
     */
    public function testFindContentValidatesFieldSortClauses( $sortClauses, $isTranslatable, $isValid, $message = null )
    {
        $repositoryMock = $this->getRepositoryMock();
        $contentTypeServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\ContentTypeService" );
        $contentTypeMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType" );
        $fieldDefinitionMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinition" );
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionHandlerMock,
            array()
        );

        $permissionsCriterionHandlerMock
            ->expects( $this->any() )
            ->method( "addPermissionsCriterion" )
            ->will( $this->returnValue( false ) );

        $repositoryMock
            ->expects( $this->once() )
            ->method( "getContentTypeService" )
            ->will( $this->returnValue( $contentTypeServiceMock ) );

        $contentTypeServiceMock
            ->expects( $this->once() )
            ->method( "loadContentTypeByIdentifier" )
            ->with( "testContentTypeIdentifier" )
            ->will( $this->returnValue( $contentTypeMock ) );

        $contentTypeMock
            ->expects( $this->once() )
            ->method( "getFieldDefinition" )
            ->with( "testFieldDefinitionIdentifier" )
            ->will( $this->returnValue( $fieldDefinitionMock ) );

        $fieldDefinitionMock
            ->expects( $this->once() )
            ->method( "__get" )
            ->with( "isTranslatable" )
            ->will( $this->returnValue( $isTranslatable ) );

        try
        {
            $result = $service->findContent(
                new Query( array( "sortClauses" => $sortClauses ) ),
                array(),
                true
            );
        }
        catch ( InvalidArgumentException $e )
        {
            $this->assertFalse( $isValid, "Invalid sort clause expected" );
            $this->assertEquals( $message, $e->getMessage() );
        }

        if ( $isValid )
        {
            $this->assertTrue( isset( $result ) );
        }
    }

    /**
     * Test for the findContent() method.
     */
    public function testFindContentWithDefaultQueryValues()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $this->getPermissionsCriterionHandlerMock(),
            array()
        );

        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );
        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects( $this->once() )
            ->method( "findContent" )
            ->with(
                new Query(
                    array(
                        "filter" => new Criterion\MatchAll(),
                        "limit" => 1073741824
                    )
                ),
                array()
            )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            "searchHits" => array( new SearchHit( array( "valueObject" => $spiContent ) ) ),
                            "totalCount" => 1
                        )
                    )
                )
            );

        $result = $service->findContent( new Query(), $fieldFilters, false );

        $this->assertEquals(
            new SearchResult(
                array(
                    "searchHits" => array( new SearchHit( array( "valueObject" => $contentMock ) ) ),
                    "totalCount" => 1
                )
            ),
            $result
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $this->getPermissionsCriterionHandlerMock(),
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        $service->findSingle( $criterionMock, array(), true );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testFindSingleThrowsHandlerException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionHandlerMock,
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->throwException( new Exception( "Handler threw an exception" ) ) );

        $service->findSingle( $criterionMock, array(), true );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\PermissionsCriterionHandler::getPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingle()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionHandlerMock = $this->getPermissionsCriterionHandlerMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionHandlerMock,
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->returnValue( true ) );

        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->once() )
            ->method( "findSingle" )
            ->with( $this->equalTo( $criterionMock ), $this->equalTo( $fieldFilters ) )
            ->will( $this->returnValue( $spiContent ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        $result = $service->findSingle( $criterionMock, $fieldFilters, true );

        $this->assertEquals( $contentMock, $result );
    }

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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected function getPermissionsCriterionHandlerMock()
    {
        if ( !isset( $this->permissionsCriterionHandlerMock ) )
        {
            $this->permissionsCriterionHandlerMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\PermissionsCriterionHandler" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->permissionsCriterionHandlerMock;
    }

    /**
     * Returns the content service to test with $methods mocked
     *
     * Injected Repository comes from {@see getRepositoryMock()} and persistence handler from {@see getPersistenceMock()}
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedSearchService( array $methods = array() )
    {
        return $this->getMock(
            "eZ\\Publish\\Core\\Repository\\SearchService",
            $methods,
            array(
                $this->getRepositoryMock(),
                $this->getPersistenceMock()->searchHandler(),
                $this->getDomainMapperMock(),
                $this->getPermissionsCriterionHandlerMock(),
                array()
            )
        );
    }
}
