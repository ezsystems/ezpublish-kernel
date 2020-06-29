<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Filtering;

use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause;
use IteratorAggregate;

/**
 * @internal
 */
final class LocationFilteringTest extends BaseRepositoryFilteringTestCase
{
    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function compareWithSearchResults(
        Filter $filter,
        IteratorAggregate $filteredLocationList
    ): void {
        $query = $this->buildSearchQueryFromFilter($filter);
        $locationListFromSearch = $this->findUsingLocationSearch($query);
        self::assertEquals($locationListFromSearch, $filteredLocationList);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function findUsingLocationSearch(LocationQuery $query): LocationList
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();
        $searchResults = $searchService->findLocations($query);

        return new LocationList(
            [
                'totalCount' => $searchResults->totalCount,
                'locations' => array_map(
                    static function (SearchHit $searchHit) {
                        return $searchHit->valueObject;
                    },
                    $searchResults->searchHits
                ),
            ]
        );
    }

    protected function getDefaultSortClause(): FilteringSortClause
    {
        return new Query\SortClause\Location\Id();
    }

    public function getCriteriaForInitialData(): iterable
    {
        yield 'Location\\Depth=2' => new Criterion\Location\Depth(Criterion\Operator::EQ, 2);
        yield 'Location\\IsMainLocation' => new Criterion\Location\IsMainLocation(
            Criterion\Location\IsMainLocation::MAIN
        );
        yield 'Location\\Priority>0' => new Criterion\Location\Priority(Criterion\Operator::GT, 0);

        yield from parent::getCriteriaForInitialData();
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\LocationList
     */
    protected function find(Filter $filter, ?array $contextLanguages = null): iterable
    {
        $repository = $this->getRepository(false);
        $locationService = $repository->getLocationService();

        return $locationService->find($filter, $contextLanguages);
    }

    protected function assertFoundContentItemsByRemoteIds(
        iterable $list,
        array $expectedContentRemoteIds
    ): void {
        foreach ($list as $location) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Location $location */
            $contentInfo = $location->getContentInfo();
            self::assertContainsEquals(
                $contentInfo->remoteId,
                $expectedContentRemoteIds,
                sprintf(
                    'Content %d (%s) at Location %d was not supposed to be found',
                    $location->id,
                    $contentInfo->id,
                    $contentInfo->remoteId
                )
            );
        }
    }

    private function buildSearchQueryFromFilter(Filter $filter): LocationQuery
    {
        $limit = $filter->getLimit();

        return new LocationQuery(
            [
                'filter' => $filter->getCriterion(),
                'sortClauses' => $filter->getSortClauses(),
                'offset' => $filter->getOffset(),
                'limit' => $limit > 0 ? $limit : 999,
            ]
        );
    }
}
