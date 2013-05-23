<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\SearchTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Repository\Values\User\Policy;

/**
 * Mock test case for Search service
 */
class SearchTest extends BaseServiceMockTest
{
    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     * @expectedException \RuntimeException
     */
    public function testFindContentThrowsRuntimeException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( array() ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "criterion" => $criterionMock ) );

        $service->findContent( $query, array(), true );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentNoPermissionsFilter()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->never() )->method( "hasAccess" );

        $repositoryMock->expects( $this->once() )
            ->method( "getDomainMapper" )
            ->will( $this->returnValue( $domainMapperMock ) );

        $serviceQuery = new Query;
        $handlerQuery = new Query( array( "limit" => SearchService::MAX_LIMIT ) );
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
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( true ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getDomainMapper" )
            ->will( $this->returnValue( $domainMapperMock ) );

        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "criterion" => $criterionMock, "limit" => 10 ) );
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
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithNoPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( false ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->never() )->method( "findContent" );

        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query( array( "criterion" => $criterionMock ) );

        $result = $service->findContent( $query, array(), true );

        $this->assertEquals(
            new SearchResult( array( "time" => 0, "totalCount" => 0 ) ),
            $result
        );
    }

    public function criterionProvider()
    {
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(
                $criterionMock,
                new Criterion\LogicalAnd( array() ),
                new Criterion\LogicalAnd( array( $criterionMock ) )
            ),
            array(
                $criterionMock,
                $criterionMock,
                new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) )
            )
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     * @dataProvider criterionProvider
     */
    public function testFindContentWithLimitedPermission( $criterionMock, $serviceCriterion, $handlerCriterion )
    {
        $mockedService = $this->getPartlyMockedSearchService( array( "getPermissionsCriterion" ) );
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );

        $mockedService->expects( $this->once() )
            ->method( "getPermissionsCriterion" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( $criterionMock ) );

        $serviceQuery = new Query( array( "criterion" => $serviceCriterion, "limit" => 10 ) );
        $handlerQuery = new Query( array( "criterion" => $handlerCriterion, "limit" => 10 ) );
        $fieldFilters = array();

        $searchHandlerMock->expects( $this->once() )
            ->method( "findContent" )
            ->with( $this->equalTo( $handlerQuery ), $this->equalTo( $fieldFilters ) )
            ->will( $this->returnValue( new SearchResult() ) );

        $result = $mockedService->findContent( $serviceQuery, $fieldFilters, true );

        $this->assertEquals(
            new SearchResult(),
            $result
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( false ) );

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
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \RuntimeException
     */
    public function testFindSingleThrowsRuntimeException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( array() ) );

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
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingle()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $service = new SearchService( $repositoryMock, $searchHandlerMock );
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( true ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getDomainMapper" )
            ->will( $this->returnValue( $domainMapperMock ) );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
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
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @dataProvider criterionProvider
     */
    public function testFindSingleWithLimitedPermission( $criterionMock, $serviceCriterion, $handlerCriterion )
    {
        $repositoryMock = $this->getRepositoryMock();
        $mockedService = $this->getPartlyMockedSearchService( array( "getPermissionsCriterion" ) );
        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );
        $domainMapperMock = $this->getDomainMapperMock();

        $repositoryMock->expects( $this->once() )
            ->method( "getDomainMapper" )
            ->will( $this->returnValue( $domainMapperMock ) );

        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );

        $mockedService->expects( $this->once() )
            ->method( "getPermissionsCriterion" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( $criterionMock ) );

        $searchHandlerMock->expects( $this->once() )
            ->method( "findSingle" )
            ->with( $this->equalTo( $handlerCriterion ), $this->equalTo( $fieldFilters ) )
            ->will( $this->returnValue( $spiContent ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        $result = $mockedService->findSingle( $serviceCriterion, $fieldFilters, true );

        $this->assertEquals( $contentMock, $result );
    }

    public function providerForTestFindSingleWithPermissionsCriterion()
    {
        $criterionMock = $this
            ->getMockBuilder( "eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion" )
            ->disableOriginalConstructor()
            ->getMock();
        $limitationMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" );
        $limitationMock->expects( $this->any() )
            ->method( "getIdentifier" )
            ->will( $this->returnValue( "limitationIdentifier" ) );

        $policy1 = new Policy( array( 'limitations' => array( $limitationMock ) ) );
        $policy2 = new Policy( array( 'limitations' => array( $limitationMock, $limitationMock ) ) );

        return array(
            array(
                $criterionMock, 1, $criterionMock,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                )
            ),
            array(
                $criterionMock, 2, new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 1, $criterionMock,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ), $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 1, $criterionMock,
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( new Policy( array( 'limitations' => array() ) ), $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 2, new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy2 ),
                    ),
                )
            ),
            array(
                $criterionMock, 3,
                new Criterion\LogicalOr(
                    array(
                        $criterionMock,
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) )
                    )
                ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy2 ),
                    ),
                )
            ),
            array(
                $criterionMock, 2, new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 ),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                )
            ),
            array(
                $criterionMock, 3, new Criterion\LogicalOr( array( $criterionMock, $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 )
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1, $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 3,
                new Criterion\LogicalOr(
                    array(
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                        $criterionMock
                    )
                ),
                array(
                    array(
                        'limitation' => null,
                        'policies' => array( $policy2 ),
                    ),
                    array(
                        'limitation' => null,
                        'policies' => array( $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 2,
                new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                )
            ),
            array(
                $criterionMock, 4,
                new Criterion\LogicalOr(
                    array(
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                        new Criterion\LogicalAnd( array( $criterionMock, $criterionMock ) ),
                    )
                ),
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( $policy1 ),
                    ),
                )
            ),
        );
    }

    protected function setBaseExpectations( $criterionMock, $limitationCount, $permissionSets )
    {
        $repositoryMock = $this->getRepositoryMock();
        $roleServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\RoleService" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $limitationTypeMock->expects( $this->any() )
            ->method( "getCriterion" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" ),
                $this->equalTo( $userMock )
            )
            ->will( $this->returnValue( $criterionMock ) );

        $roleServiceMock->expects( $this->exactly( $limitationCount ) )
            ->method( "getLimitationType" )
            ->with( $this->equalTo( "limitationIdentifier" ) )
            ->will( $this->returnValue( $limitationTypeMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( $permissionSets ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $userMock ) );

        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );

        return array( $repositoryMock, $searchHandlerMock );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @dataProvider providerForTestFindSingleWithPermissionsCriterion
     */
    public function testFindSingleWithPermissionsCriterion( $criterionMock, $limitationCount, $criteria, $permissionSets )
    {
        list( $repositoryMock, $searchHandlerMock ) = $this->setBaseExpectations(
            $criterionMock, $limitationCount, $permissionSets
        );

        $serviceQuery = new Query( array( "criterion" => $criterionMock, "limit" => 10 ) );
        $handlerQuery = new Query(
            array(
                "criterion" => new Criterion\LogicalAnd( array( $criterionMock, $criteria ) ),
                "limit" => 10
            )
        );
        $fieldFilters = array();

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->once() )
            ->method( "findContent" )
            ->with( $this->equalTo( $handlerQuery ), $this->equalTo( $fieldFilters ) )
            ->will( $this->returnValue( new SearchResult() ) );

        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        /** @var \eZ\Publish\API\Repository\Repository */
        $service = new SearchService( $repositoryMock, $searchHandlerMock );
        $result = $service->findContent( $serviceQuery, $fieldFilters, true );

        $this->assertEquals( new SearchResult(), $result );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     * @dataProvider providerForTestFindSingleWithPermissionsCriterion
     */
    public function testFindContentWithPermissionsCriterion( $criterionMock, $limitationCount, $criteria, $permissionSets )
    {
        list( $repositoryMock, $searchHandlerMock ) = $this->setBaseExpectations(
            $criterionMock, $limitationCount, $permissionSets
        );

        $serviceCriterion = $criterionMock;
        $handlerCriterion = new Criterion\LogicalAnd( array( $criterionMock, $criteria ) );
        $fieldFilters = array();
        $spiContent = new SPIContent;
        $contentMock = $this->getMockForAbstractClass( "eZ\\Publish\\API\\Repository\\Values\\Content\\Content" );

        $domainMapperMock = $this->getDomainMapperMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        $repositoryMock->expects( $this->once() )
            ->method( "getDomainMapper" )
            ->will( $this->returnValue( $domainMapperMock ) );

        $domainMapperMock->expects( $this->once() )
            ->method( "buildContentDomainObject" )
            ->with( $this->equalTo( $spiContent ) )
            ->will( $this->returnValue( $contentMock ) );

        /** @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects( $this->once() )
            ->method( "findSingle" )
            ->with( $this->equalTo( $handlerCriterion ), $this->equalTo( $fieldFilters ) )
            ->will( $this->returnValue( $spiContent ) );

        /** @var \eZ\Publish\SPI\Persistence\Content\Search\Handler $searchHandlerMock */
        /** @var \eZ\Publish\API\Repository\Repository $repositoryMock */
        $service = new SearchService( $repositoryMock, $searchHandlerMock );
        $result = $service->findSingle( $serviceCriterion, $fieldFilters, true );

        $this->assertEquals( $contentMock, $result );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDomainMapperMock()
    {
        return $this
            ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\DomainMapper" )
            ->disableOriginalConstructor()
            ->getMock();
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
                $this->getPersistenceMock()->searchHandler()
            )
        );
    }

    protected $repositoryMock;

    /**
     *
     *
     * @return \eZ\Publish\API\Repository\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepositoryMock()
    {
        if ( !isset( $this->repositoryMock ) )
        {
            $this->repositoryMock = $this
                ->getMockBuilder( "eZ\\Publish\\Core\\Repository\\Repository" )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->repositoryMock;
    }
}
