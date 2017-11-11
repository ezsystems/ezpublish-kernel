<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\SearchTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\Helper\DomainMapper;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\Search\Common\BackgroundIndexer;
use eZ\Publish\Core\Search\Common\BackgroundIndexer\NullIndexer;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Exception;

/**
 * Mock test case for Search service.
 */
class SearchTest extends BaseServiceMockTest
{
    protected $repositoryMock;

    protected $domainMapperMock;

    protected $permissionsCriterionResolverMock;

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::__construct
     */
    public function testConstructor()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $settings = array('teh setting');

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            $settings
        );

        $this->assertAttributeSame(
            $repositoryMock,
            'repository',
            $service
        );

        $this->assertAttributeSame(
            $searchHandlerMock,
            'searchHandler',
            $service
        );

        $this->assertAttributeSame(
            $domainMapperMock,
            'domainMapper',
            $service
        );

        $this->assertAttributeSame(
            $permissionsCriterionResolverMock,
            'permissionCriterionResolver',
            $service
        );

        $this->assertAttributeSame(
            $settings,
            'settings',
            $service
        );
    }

    public function providerForFindContentValidatesLocationCriteriaAndSortClauses()
    {
        return array(
            array(
                new Query(array('filter' => new Criterion\Location\Depth(Criterion\Operator::LT, 2))),
                "Argument '\$query' is invalid: Location criterions cannot be used in Content search",
            ),
            array(
                new Query(array('query' => new Criterion\Location\Depth(Criterion\Operator::LT, 2))),
                "Argument '\$query' is invalid: Location criterions cannot be used in Content search",
            ),
            array(
                new Query(
                    array(
                        'query' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                            )
                        ),
                    )
                ),
                "Argument '\$query' is invalid: Location criterions cannot be used in Content search",
            ),
            array(
                new Query(array('sortClauses' => array(new SortClause\Location\Id()))),
                "Argument '\$query' is invalid: Location sort clauses cannot be used in Content search",
            ),
        );
    }

    /**
     * @dataProvider providerForFindContentValidatesLocationCriteriaAndSortClauses
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContentValidatesLocationCriteriaAndSortClauses($query, $exceptionMessage)
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        try {
            $service->findContent($query);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals($exceptionMessage, $e->getMessage());
            throw $e;
        }

        $this->fail('Expected exception was not thrown');
    }

    public function providerForFindSingleValidatesLocationCriteria()
    {
        return array(
            array(
                new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                "Argument '\$filter' is invalid: Location criterions cannot be used in Content search",
            ),
            array(
                new Criterion\LogicalAnd(
                    array(
                        new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                    )
                ),
                "Argument '\$filter' is invalid: Location criterions cannot be used in Content search",
            ),
        );
    }

    /**
     * @dataProvider providerForFindSingleValidatesLocationCriteria
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleValidatesLocationCriteria($criterion, $exceptionMessage)
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        try {
            $service->findSingle($criterion);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals($exceptionMessage, $e->getMessage());
            throw $e;
        }

        $this->fail('Expected exception was not thrown');
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testFindContentThrowsHandlerException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(array('filter' => $criterionMock));

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findContent($query, array(), true);
    }

    public function providerForFindContentWhenContentLoadThrowsException()
    {
        return [
            [
                new NotFoundException('content', 'id = 33'),
                true,
            ],
            [
                new UnauthorizedException('content', 'read', ['id' => 33]),
                false,
            ],
        ];
    }

    /**
     * Test for the findContent() method when search is out of sync with persistence.
     *
     * @dataProvider providerForFindContentWhenContentLoadThrowsException
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWhenContentLoadThrowsException($e, $index = true)
    {
        $indexer = $this->createMock(BackgroundIndexer::class);
        if ($index) {
            $indexer->expects($this->once())
                ->method('registerContent')
                ->with($this->isInstanceOf(SPIContentInfo::class));
        } else {
            $indexer->expects($this->never())->method($this->anything());
        }

        $service = $this->getMockBuilder(SearchService::class)
            ->setConstructorArgs([
                $repo = $this->getRepositoryMock(),
                $this->getSPIMockHandler('Search\\Handler'),
                $this->getDomainMapperMock(),
                $this->getPermissionCriterionResolverMock(),
                $indexer,
            ])->setMethods(['internalFindContentInfo'])
            ->getMock();

        $info = new SPIContentInfo(['id' => 33]);
        $result = new SearchResult(['searchHits' => [new SearchHit(['valueObject' => $info])], 'totalCount' => 2]);
        $service->expects($this->once())
            ->method('internalFindContentInfo')
            ->with($this->isInstanceOf(Query::class))
            ->willReturn($result);

        $contentService = $this->createMock(ContentService::class);
        $contentService->expects($this->once())
            ->method('internalLoadContent')
            ->with(33)
            ->willThrowException($e);

        $repo->expects($this->once())
            ->method('getContentService')
            ->willReturn($contentService);

        $finalResult = $service->findContent(new Query());

        $this->assertEmpty($finalResult->searchHits, 'Expected search hits to be empty');
        $this->assertEquals(1, $finalResult->totalCount, 'Expected total count to be 1');
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentNoPermissionsFilter()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        $repositoryMock->expects($this->never())->method('hasAccess');

        $repositoryMock
            ->expects($this->once())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentServiceMock = $this
                        ->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );

        $serviceQuery = new Query();
        $handlerQuery = new Query(array('filter' => new Criterion\MatchAll(), 'limit' => 25));
        $languageFilter = array();
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Content');

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findContent')
            ->with($this->equalTo($handlerQuery), $this->equalTo($languageFilter))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiContentInfo))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $contentServiceMock
            ->expects($this->once())
            ->method('internalLoadContent')
            ->will($this->returnValue($contentMock));

        $result = $service->findContent($serviceQuery, $languageFilter, false);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $contentMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        $repositoryMock
            ->expects($this->once())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentServiceMock = $this
                        ->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );

        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(array('filter' => $criterionMock, 'limit' => 10));
        $languageFilter = array();
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Content');

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findContent')
            ->with($this->equalTo($query), $this->equalTo($languageFilter))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiContentInfo))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $domainMapperMock->expects($this->never())
            ->method($this->anything());

        $contentServiceMock
            ->expects($this->once())
            ->method('internalLoadContent')
            ->will($this->returnValue($contentMock));

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        $result = $service->findContent($query, $languageFilter, true);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $contentMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithNoPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->never())->method('findContent');

        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(array('filter' => $criterionMock));

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(false));

        $result = $service->findContent($query, array(), true);

        $this->assertEquals(
            new SearchResult(array('time' => 0, 'totalCount' => 0)),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     */
    public function testFindContentWithDefaultQueryValues()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            array()
        );

        $repositoryMock
            ->expects($this->once())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentServiceMock = $this
                        ->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );

        $languageFilter = array();
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Content');
        $domainMapperMock->expects($this->never())
            ->method($this->anything());

        $contentServiceMock
            ->expects($this->once())
            ->method('internalLoadContent')
            ->will($this->returnValue($contentMock));

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects($this->once())
            ->method('findContent')
            ->with(
                new Query(
                    array(
                        'filter' => new Criterion\MatchAll(),
                        'limit' => 25,
                    )
                ),
                array()
            )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiContentInfo))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $result = $service->findContent(new Query(), $languageFilter, false);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $contentMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleThrowsNotFoundException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn(false);

        $service->findSingle($criterionMock, array(), true);
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testFindSingleThrowsHandlerException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findSingle($criterionMock, array(), true);
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingle()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        $repositoryMock
            ->expects($this->once())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentServiceMock = $this
                        ->getMockBuilder('eZ\\Publish\\Core\\Repository\\ContentService')
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        $languageFilter = array();
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Content');

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findSingle')
            ->with($this->equalTo($criterionMock), $this->equalTo($languageFilter))
            ->will($this->returnValue($spiContentInfo));

        $domainMapperMock->expects($this->never())
            ->method($this->anything());

        $contentServiceMock
            ->expects($this->once())
            ->method('internalLoadContent')
            ->will($this->returnValue($contentMock));

        $result = $service->findSingle($criterionMock, $languageFilter, true);

        $this->assertEquals($contentMock, $result);
    }

    /**
     * Test for the findLocations() method.
     */
    public function testFindLocationsWithPermission()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new LocationQuery(array('filter' => $criterionMock, 'limit' => 10));
        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Location');

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiLocation))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObject')
            ->with($this->equalTo($spiLocation))
            ->will($this->returnValue($locationMock));

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        $result = $service->findLocations($query, array(), true);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $locationMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    /**
     * Test for the findLocations() method.
     */
    public function testFindLocationsWithNoPermissionsFilter()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        $repositoryMock->expects($this->never())->method('hasAccess');

        $serviceQuery = new LocationQuery();
        $handlerQuery = new LocationQuery(array('filter' => new Criterion\MatchAll(), 'limit' => 25));
        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Location');

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($handlerQuery))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiLocation))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObject')
            ->with($this->equalTo($spiLocation))
            ->will($this->returnValue($locationMock));

        $result = $service->findLocations($serviceQuery, array(), false);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $locationMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    public function providerForFindLocationsWhenDomainMapperThrowsException()
    {
        return [
            [
                new NotFoundException('content', 'id = 33'),
                true,
            ],
            [
                new UnauthorizedException('content', 'read', ['id' => 33]),
                false,
            ],
        ];
    }

    /**
     * Test for the findLocations() method when search is out of sync with persistence.
     *
     * @dataProvider providerForFindLocationsWhenDomainMapperThrowsException
     * @covers \eZ\Publish\Core\Repository\SearchService::findLocations
     */
    public function testFindLocationsBackgroundIndexerWhenDomainMapperThrowsException($e, $index = true)
    {
        $indexer = $this->createMock(BackgroundIndexer::class);
        if ($index) {
            $indexer->expects($this->once())
                ->method('registerLocation')
                ->with($this->isInstanceOf(SPILocation::class));
        } else {
            $indexer->expects($this->never())->method($this->anything());
        }

        $service = $this->getMockBuilder(SearchService::class)
            ->setConstructorArgs([
                $this->getRepositoryMock(),
                $searchHandler = $this->getSPIMockHandler('Search\\Handler'),
                $mapper = $this->getDomainMapperMock(),
                $this->getPermissionCriterionResolverMock(),
                $indexer,
            ])->setMethods(['addPermissionsCriterion'])
            ->getMock();

        $location = new SPILocation(['id' => 44]);
        $service->expects($this->once())
            ->method('addPermissionsCriterion')
            ->with($this->isInstanceOf(Criterion::class))
            ->willReturn(true);

        $result = new SearchResult(['searchHits' => [new SearchHit(['valueObject' => $location])], 'totalCount' => 2]);
        $searchHandler->expects($this->once())
            ->method('findLocations')
            ->with($this->isInstanceOf(LocationQuery::class), $this->isType('array'))
            ->willReturn($result);

        $mapper->expects($this->once())
            ->method('buildLocationDomainObject')
            ->with($this->isInstanceOf(SPILocation::class))
            ->willThrowException($e);

        $finalResult = $service->findLocations(new LocationQuery());

        $this->assertEmpty($finalResult->searchHits, 'Expected search hits to be empty');
        $this->assertEquals(1, $finalResult->totalCount, 'Expected total count to be 1');
    }

    /**
     * Test for the findLocations() method.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Handler threw an exception
     */
    public function testFindLocationsThrowsHandlerException()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            array()
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder('eZ\\Publish\\API\\Repository\\Values\\Content\\Query\\Criterion')
            ->disableOriginalConstructor()
            ->getMock();
        $query = new LocationQuery(array('filter' => $criterionMock));

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findLocations($query, array(), true);
    }

    /**
     * Test for the findLocations() method.
     */
    public function testFindLocationsWithDefaultQueryValues()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getDomainMapperMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            array()
        );

        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass('eZ\\Publish\\API\\Repository\\Values\\Content\\Location');
        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObject')
            ->with($this->equalTo($spiLocation))
            ->will($this->returnValue($locationMock));

        /* @var \PHPUnit_Framework_MockObject_MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects($this->once())
            ->method('findLocations')
            ->with(
                new LocationQuery(
                    array(
                        'filter' => new Criterion\MatchAll(),
                        'limit' => 25,
                    )
                )
            )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        array(
                            'searchHits' => array(new SearchHit(array('valueObject' => $spiLocation))),
                            'totalCount' => 1,
                        )
                    )
                )
            );

        $result = $service->findLocations(new LocationQuery(), array(), false);

        $this->assertEquals(
            new SearchResult(
                array(
                    'searchHits' => array(new SearchHit(array('valueObject' => $locationMock))),
                    'totalCount' => 1,
                )
            ),
            $result
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected function getDomainMapperMock()
    {
        if (!isset($this->domainMapperMock)) {
            $this->domainMapperMock = $this
                ->getMockBuilder(DomainMapper::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->domainMapperMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\API\Repository\PermissionCriterionResolver
     */
    protected function getPermissionCriterionResolverMock()
    {
        if (!isset($this->permissionsCriterionResolverMock)) {
            $this->permissionsCriterionResolverMock = $this
                ->getMockBuilder(PermissionCriterionResolver::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return $this->permissionsCriterionResolverMock;
    }
}
