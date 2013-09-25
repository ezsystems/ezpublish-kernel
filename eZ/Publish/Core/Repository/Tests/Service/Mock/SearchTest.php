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
use eZ\Publish\Core\Repository\PermissionsCriterionHandler;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Repository\Values\User\Policy;
use RuntimeException;

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
     * @expectedException \RuntimeException
     */
    public function testFindContentThrowsRuntimeException()
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
        $query = new Query( array( "criterion" => $criterionMock ) );

        $permissionsCriterionHandlerMock->expects( $this->once() )
            ->method( "addPermissionsCriterion" )
            ->with( $criterionMock )
            ->will( $this->throwException( new RuntimeException() ) );

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
        $query = new Query( array( "criterion" => $criterionMock ) );

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
     * @expectedException \RuntimeException
     */
    public function testFindSingleThrowsRuntimeException()
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
            ->will( $this->throwException( new RuntimeException() ) );

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
            array(
                $criterionMock, 1,
                $criterionMock,
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                )
            ),
            array(
                $criterionMock, 2,
                new Criterion\LogicalOr( array( $criterionMock, $criterionMock ) ),
                array(
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                    array(
                        'limitation' => $limitationMock,
                        'policies' => array( new Policy( array( 'limitations' => "*" ) ) ),
                    ),
                )
            ),
        );
    }

    protected function setBaseExpectations( $criterionMock, $limitationCount, $permissionSets )
    {
        $repositoryMock = $this->getRepositoryMock();
        //$roleServiceMock = $this->getMock( "eZ\\Publish\\API\\Repository\\RoleService" );
        $userMock = $this->getMock( "eZ\\Publish\\API\\Repository\\Values\\User\\User" );
        $limitationTypeMock = $this->getMock( "eZ\\Publish\\SPI\\Limitation\\Type" );

        $limitationTypeMock->expects( $this->any() )
            ->method( "getCriterion" )
            ->with(
                $this->isInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\User\\Limitation" ),
                $this->equalTo( $userMock )
            )
            ->will( $this->returnValue( $criterionMock ) );

        /*$roleServiceMock->expects( $this->exactly( $limitationCount ) )
            ->method( "getLimitationType" )
            ->with( $this->equalTo( "limitationIdentifier" ) )
            ->will( $this->returnValue( $limitationTypeMock ) );*/

        /*$repositoryMock->expects( $this->once() )
            ->method( "hasAccess" )
            ->with( $this->equalTo( "content" ), $this->equalTo( "read" ) )
            ->will( $this->returnValue( $permissionSets ) );*/

        /*$repositoryMock->expects( $this->once() )
            ->method( "getRoleService" )
            ->will( $this->returnValue( $roleServiceMock ) );

        $repositoryMock->expects( $this->once() )
            ->method( "getCurrentUser" )
            ->will( $this->returnValue( $userMock ) );*/

        $searchHandlerMock = $this->getPersistenceMockHandler( 'Content\\Search\\Handler' );

        return array( $repositoryMock, $searchHandlerMock );
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
