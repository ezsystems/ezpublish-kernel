<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Mock\SearchTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Mock;

use eZ\Publish\Core\Repository\ContentService;
use eZ\Publish\Core\Repository\Tests\Service\Mock\Base as BaseServiceMockTest;
use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
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

    protected $contentDomainMapperMock;

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
        $contentDomainMapperMock = $this->getContentDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $settings = ['teh setting'];

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $contentDomainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            $settings
        );
    }

    public function providerForFindContentValidatesLocationCriteriaAndSortClauses()
    {
        return [
            [
                new Query(['filter' => new Criterion\Location\Depth(Criterion\Operator::LT, 2)]),
                "Argument '\$query' is invalid: Location Criteria cannot be used in Content search",
            ],
            [
                new Query(['query' => new Criterion\Location\Depth(Criterion\Operator::LT, 2)]),
                "Argument '\$query' is invalid: Location Criteria cannot be used in Content search",
            ],
            [
                new Query(
                    [
                        'query' => new Criterion\LogicalAnd(
                            [
                                new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                            ]
                        ),
                    ]
                ),
                "Argument '\$query' is invalid: Location Criteria cannot be used in Content search",
            ],
            [
                new Query(['sortClauses' => [new SortClause\Location\Id()]]),
                "Argument '\$query' is invalid: Location Sort Clauses cannot be used in Content search",
            ],
        ];
    }

    /**
     * @dataProvider providerForFindContentValidatesLocationCriteriaAndSortClauses
     */
    public function testFindContentValidatesLocationCriteriaAndSortClauses($query, $exceptionMessage)
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
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
        return [
            [
                new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                "Argument '\$filter' is invalid: Location Criteria cannot be used in Content search",
            ],
            [
                new Criterion\LogicalAnd(
                    [
                        new Criterion\Location\Depth(Criterion\Operator::LT, 2),
                    ]
                ),
                "Argument '\$filter' is invalid: Location Criteria cannot be used in Content search",
            ],
        ];
    }

    /**
     * @dataProvider providerForFindSingleValidatesLocationCriteria
     */
    public function testFindSingleValidatesLocationCriteria($criterion, $exceptionMessage)
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
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
     */
    public function testFindContentThrowsHandlerException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler threw an exception');

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(['filter' => $criterionMock]);

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findContent($query, [], true);
    }

    /**
     * Test for the findContent() method when search is out of sync with persistence.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWhenDomainMapperThrowsException()
    {
        $indexer = $this->createMock(BackgroundIndexer::class);
        $indexer->expects($this->once())
            ->method('registerContent')
            ->with($this->isInstanceOf(SPIContentInfo::class));

        $service = $this->getMockBuilder(SearchService::class)
            ->setConstructorArgs([
                $this->getRepositoryMock(),
                $this->getSPIMockHandler('Search\\Handler'),
                $mapper = $this->getContentDomainMapperMock(),
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

        $mapper->expects($this->once())
            ->method('buildContentDomainObjectsOnSearchResult')
            ->with($this->equalTo($result), $this->equalTo([]))
            ->willReturnCallback(function (SearchResult $spiResult) use ($info) {
                unset($spiResult->searchHits[0]);
                --$spiResult->totalCount;

                return [$info];
            });

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
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $repositoryMock = $this->getRepositoryMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $mapper = $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            []
        );

        $repositoryMock->expects($this->never())->method('getPermissionResolver');

        $serviceQuery = new Query();
        $handlerQuery = new Query(['filter' => new Criterion\MatchAll(), 'limit' => 25]);
        $languageFilter = [];
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass(Content::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findContent')
            ->with($this->equalTo($handlerQuery), $this->equalTo($languageFilter))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiContentInfo])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $mapper->expects($this->once())
            ->method('buildContentDomainObjectsOnSearchResult')
            ->with($this->isInstanceOf(SearchResult::class), $this->equalTo([]))
            ->willReturnCallback(function (SearchResult $spiResult) use ($contentMock) {
                $spiResult->searchHits[0]->valueObject = $contentMock;

                return [];
            });

        $result = $service->findContent($serviceQuery, $languageFilter, false);

        $this->assertEquals(
            new SearchResult(
                [
                    'searchHits' => [new SearchHit(['valueObject' => $contentMock])],
                    'totalCount' => 1,
                ]
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
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getContentDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $this->getRepositoryMock(),
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(['filter' => $criterionMock, 'limit' => 10]);
        $languageFilter = [];
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass(Content::class);

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findContent')
            ->with($this->equalTo($query), $this->equalTo($languageFilter))
            ->will(
                $this->returnValue(
                    new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiContentInfo])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $domainMapperMock
            ->expects($this->once())
            ->method('buildContentDomainObjectsOnSearchResult')
            ->with($this->isInstanceOf(SearchResult::class), $this->equalTo([]))
            ->willReturnCallback(function (SearchResult $spiResult) use ($contentMock) {
                $spiResult->searchHits[0]->valueObject = $contentMock;

                return [];
            });

        $result = $service->findContent($query, $languageFilter, true);

        $this->assertEquals(
            new SearchResult(
                [
                    'searchHits' => [new SearchHit(['valueObject' => $contentMock])],
                    'totalCount' => 1,
                ]
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
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $this->getRepositoryMock(),
            $searchHandlerMock,
            $mapper = $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->never())->method('findContent');

        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = new Query(['filter' => $criterionMock]);

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(false));

        $mapper->expects($this->once())
            ->method('buildContentDomainObjectsOnSearchResult')
            ->with($this->isInstanceOf(SearchResult::class), $this->equalTo([]))
            ->willReturn([]);

        $result = $service->findContent($query, [], true);

        $this->assertEquals(
            new SearchResult(['time' => 0, 'totalCount' => 0]),
            $result
        );
    }

    /**
     * Test for the findContent() method.
     */
    public function testFindContentWithDefaultQueryValues()
    {
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getContentDomainMapperMock();
        $service = new SearchService(
            $this->getRepositoryMock(),
            $searchHandlerMock,
            $domainMapperMock,
            $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            []
        );

        $languageFilter = [];
        $spiContentInfo = new SPIContentInfo();
        $contentMock = $this->getMockForAbstractClass(Content::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects($this->once())
            ->method('findContent')
            ->with(
                new Query(
                    [
                        'filter' => new Criterion\MatchAll(),
                        'limit' => 25,
                    ]
                ),
                []
            )
            ->will(
                $this->returnValue(
                    new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiContentInfo])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $domainMapperMock
            ->expects($this->once())
            ->method('buildContentDomainObjectsOnSearchResult')
            ->with($this->isInstanceOf(SearchResult::class), $this->equalTo([]))
            ->willReturnCallback(function (SearchResult $spiResult) use ($contentMock) {
                $spiResult->searchHits[0]->valueObject = $contentMock;

                return [];
            });

        $result = $service->findContent(new Query(), $languageFilter, false);

        $this->assertEquals(
            new SearchResult(
                [
                    'searchHits' => [new SearchHit(['valueObject' => $contentMock])],
                    'totalCount' => 1,
                ]
            ),
            $result
        );
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingleThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            []
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->willReturn(false);

        $service->findSingle($criterionMock, [], true);
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::addPermissionsCriterion
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     */
    public function testFindSingleThrowsHandlerException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler threw an exception');

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findSingle($criterionMock, [], true);
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
        $domainMapperMock = $this->getContentDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        $repositoryMock
            ->expects($this->once())
            ->method('getContentService')
            ->will(
                $this->returnValue(
                    $contentServiceMock = $this
                        ->getMockBuilder(ContentService::class)
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        $languageFilter = [];
        $spiContentInfo = new SPIContentInfo(['id' => 123]);
        $contentMock = $this->getMockForAbstractClass(Content::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects($this->once())
            ->method('findSingle')
            ->with($this->equalTo($criterionMock), $this->equalTo($languageFilter))
            ->will($this->returnValue($spiContentInfo));

        $domainMapperMock->expects($this->never())
            ->method($this->anything());

        $contentServiceMock
            ->expects($this->once())
            ->method('internalLoadContentById')
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
        $domainMapperMock = $this->getContentDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = new LocationQuery(['filter' => $criterionMock, 'limit' => 10]);
        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass(Location::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($query))
            ->will(
                $this->returnValue(
                    $spiResult = new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiLocation])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->returnValue(true));

        $endResult = new SearchResult(
            [
                'searchHits' => [new SearchHit(['valueObject' => $locationMock])],
                'totalCount' => 1,
            ]
        );

        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObjectsOnSearchResult')
            ->with($this->equalTo($spiResult))
            ->willReturnCallback(function (SearchResult $spiResult) use ($endResult) {
                $spiResult->searchHits[0] = $endResult->searchHits[0];

                return [];
            });

        $result = $service->findLocations($query, [], true);

        $this->assertEquals(
            $endResult,
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
        $domainMapperMock = $this->getContentDomainMapperMock();
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        $repositoryMock->expects($this->never())->method('getPermissionResolver');

        $serviceQuery = new LocationQuery();
        $handlerQuery = new LocationQuery(['filter' => new Criterion\MatchAll(), 'limit' => 25]);
        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass(Location::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($handlerQuery))
            ->will(
                $this->returnValue(
                    $spiResult = new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiLocation])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $permissionsCriterionResolverMock->expects($this->never())->method($this->anything());

        $endResult = new SearchResult(
            [
                'searchHits' => [new SearchHit(['valueObject' => $locationMock])],
                'totalCount' => 1,
            ]
        );

        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObjectsOnSearchResult')
            ->with($this->equalTo($spiResult))
            ->willReturnCallback(function (SearchResult $spiResult) use ($endResult) {
                $spiResult->searchHits[0] = $endResult->searchHits[0];

                return [];
            });

        $result = $service->findLocations($serviceQuery, [], false);

        $this->assertEquals(
            $endResult,
            $result
        );
    }

    /**
     * Test for the findLocations() method when search is out of sync with persistence.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findLocations
     */
    public function testFindLocationsBackgroundIndexerWhenDomainMapperThrowsException()
    {
        $indexer = $this->createMock(BackgroundIndexer::class);
        $indexer->expects($this->once())
            ->method('registerLocation')
            ->with($this->isInstanceOf(SPILocation::class));

        $service = $this->getMockBuilder(SearchService::class)
            ->setConstructorArgs([
                $this->getRepositoryMock(),
                $searchHandler = $this->getSPIMockHandler('Search\\Handler'),
                $mapper = $this->getContentDomainMapperMock(),
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
            ->method('buildLocationDomainObjectsOnSearchResult')
            ->with($this->equalTo($result))
            ->willReturnCallback(function (SearchResult $spiResult) use ($location) {
                unset($spiResult->searchHits[0]);
                --$spiResult->totalCount;

                return [$location];
            });

        $finalResult = $service->findLocations(new LocationQuery());

        $this->assertEmpty($finalResult->searchHits, 'Expected search hits to be empty');
        $this->assertEquals(1, $finalResult->totalCount, 'Expected total count to be 1');
    }

    /**
     * Test for the findLocations() method.
     */
    public function testFindLocationsThrowsHandlerException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler threw an exception');

        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $permissionsCriterionResolverMock = $this->getPermissionCriterionResolverMock();

        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $this->getContentDomainMapperMock(),
            $permissionsCriterionResolverMock,
            new NullIndexer(),
            []
        );

        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterionMock */
        $criterionMock = $this
            ->getMockBuilder(Criterion::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = new LocationQuery(['filter' => $criterionMock]);

        $permissionsCriterionResolverMock->expects($this->once())
            ->method('getPermissionsCriterion')
            ->with('content', 'read')
            ->will($this->throwException(new Exception('Handler threw an exception')));

        $service->findLocations($query, [], true);
    }

    /**
     * Test for the findLocations() method.
     */
    public function testFindLocationsWithDefaultQueryValues()
    {
        $repositoryMock = $this->getRepositoryMock();
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandlerMock */
        $searchHandlerMock = $this->getSPIMockHandler('Search\\Handler');
        $domainMapperMock = $this->getContentDomainMapperMock();
        $service = new SearchService(
            $repositoryMock,
            $searchHandlerMock,
            $domainMapperMock,
            $this->getPermissionCriterionResolverMock(),
            new NullIndexer(),
            []
        );

        $spiLocation = new SPILocation();
        $locationMock = $this->getMockForAbstractClass(Location::class);

        /* @var \PHPUnit\Framework\MockObject\MockObject $searchHandlerMock */
        $searchHandlerMock
            ->expects($this->once())
            ->method('findLocations')
            ->with(
                new LocationQuery(
                    [
                        'filter' => new Criterion\MatchAll(),
                        'limit' => 25,
                    ]
                )
            )
            ->will(
                $this->returnValue(
                    $spiResult = new SearchResult(
                        [
                            'searchHits' => [new SearchHit(['valueObject' => $spiLocation])],
                            'totalCount' => 1,
                        ]
                    )
                )
            );

        $endResult = new SearchResult(
            [
                'searchHits' => [new SearchHit(['valueObject' => $locationMock])],
                'totalCount' => 1,
            ]
        );

        $domainMapperMock->expects($this->once())
            ->method('buildLocationDomainObjectsOnSearchResult')
            ->with($this->equalTo($spiResult))
            ->willReturnCallback(function (SearchResult $spiResult) use ($endResult) {
                $spiResult->searchHits[0] = $endResult->searchHits[0];

                return [];
            });

        $result = $service->findLocations(new LocationQuery(), [], false);

        $this->assertEquals(
            $endResult,
            $result
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\API\Repository\PermissionCriterionResolver
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
