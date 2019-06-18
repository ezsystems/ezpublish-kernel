<?php

/**
 * File containing the ContentSearchHitAdapterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Tests;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchHitAdapter;
use PHPUnit\Framework\TestCase;

class LocationSearchHitAdapterTest extends TestCase
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchService;

    protected function setUp()
    {
        parent::setUp();
        $this->searchService = $this->getMock('eZ\Publish\API\Repository\SearchService');
    }

    /**
     * Returns the adapter to test.
     *
     * @param LocationQuery $query
     * @param SearchService $searchService
     *
     * @return LocationSearchHitAdapter
     */
    protected function getAdapter(LocationQuery $query, SearchService $searchService)
    {
        return new LocationSearchHitAdapter($query, $searchService);
    }

    public function testGetNbResults()
    {
        $nbResults = 123;
        $query = new LocationQuery();
        $query->filter = $this->getMock('eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface');
        $query->sortClauses = $this
            ->getMockBuilder('eZ\Publish\API\Repository\Values\Content\Query\SortClause')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        // Count query will necessarily have a 0 limit.
        $countQuery = clone $query;
        $countQuery->limit = 0;

        $searchResult = new SearchResult(['totalCount' => $nbResults]);
        $this->searchService
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($countQuery))
            ->will($this->returnValue($searchResult));

        $adapter = $this->getAdapter($query, $this->searchService);
        $this->assertSame($nbResults, $adapter->getNbResults());
        // Running a 2nd time to ensure SearchService::findContent() is called only once.
        $this->assertSame($nbResults, $adapter->getNbResults());
    }

    public function testGetSlice()
    {
        $offset = 20;
        $limit = 25;
        $nbResults = 123;

        $query = new LocationQuery();
        $query->filter = $this->getMock('eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface');
        $query->sortClauses = $this
            ->getMockBuilder('eZ\Publish\API\Repository\Values\Content\Query\SortClause')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        // Injected query is being cloned to modify offset/limit,
        // so we need to do the same here for our assertions.
        $searchQuery = clone $query;
        $searchQuery->offset = $offset;
        $searchQuery->limit = $limit;
        $searchQuery->performCount = false;

        $hits = [];
        for ($i = 0; $i < $limit; ++$i) {
            $location = $this->getMockForAbstractClass('eZ\Publish\API\Repository\Values\Content\Location');
            $hits[] = new SearchHit(['valueObject' => $location]);
        }
        $finalResult = $this->getExpectedFinalResultFromHits($hits);
        $searchResult = new SearchResult(['searchHits' => $hits, 'totalCount' => $nbResults]);
        $this
            ->searchService
            ->expects($this->once())
            ->method('findLocations')
            ->with($this->equalTo($searchQuery))
            ->will($this->returnValue($searchResult));

        $adapter = $this->getAdapter($query, $this->searchService);
        $this->assertSame($finalResult, $adapter->getSlice($offset, $limit));
        $this->assertSame($nbResults, $adapter->getNbResults());
    }

    /**
     * Returns expected result from adapter from search hits.
     *
     * @param $hits
     *
     * @return mixed
     */
    protected function getExpectedFinalResultFromHits($hits)
    {
        return $hits;
    }
}
