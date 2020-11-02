<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll;
use eZ\Publish\API\Repository\Values\Content\Search\AggregationResult;

abstract class AbstractAggregationTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipIfAggregationsAreNotSupported();
    }

    protected function skipIfAggregationsAreNotSupported(): void
    {
        $searchService = $this->getRepository()->getSearchService();
        if (!$searchService->supports(SearchService::CAPABILITY_AGGREGATIONS)) {
            self::markTestSkipped("Search engine doesn't support aggregations");
        }
    }

    /**
     * @dataProvider dataProviderForTestFindContentWithAggregation
     */
    public function testFindContentWithAggregation(
        Aggregation $aggregation,
        AggregationResult $expectedResult
    ): void {
        $this->createFixturesForAggregation($aggregation);

        $searchService = $this->getRepository()->getSearchService();

        self::assertEquals(
            $expectedResult,
            $searchService->findContent(
                $this->createContentQuery($aggregation)
            )->aggregations->first()
        );
    }

    /**
     * @dataProvider dataProviderForTestFindLocationWithAggregation
     */
    public function testFindLocationWithAggregation(
        Aggregation $aggregation,
        AggregationResult $expectedResult
    ): void {
        $this->createFixturesForAggregation($aggregation);

        $searchService = $this->getRepository()->getSearchService();

        self::assertEquals(
            $expectedResult,
            $searchService->findLocations(
                $this->createLocationQuery($aggregation)
            )->aggregations->first()
        );
    }

    abstract public function dataProviderForTestFindContentWithAggregation(): iterable;

    /**
     * Overwrite if results for location query are different then content query.
     *
     * @return iterable
     */
    public function dataProviderForTestFindLocationWithAggregation(): iterable
    {
        yield from $this->dataProviderForTestFindContentWithAggregation();
    }

    protected function createFixturesForAggregation(Aggregation $aggregation): void
    {
    }

    protected function createContentQuery(Aggregation $aggregation): Query
    {
        $query = new Query();
        $query->aggregations[] = $aggregation;
        $query->filter = new MatchAll();
        $query->limit = 0;

        return $query;
    }

    protected function createLocationQuery(Aggregation $aggregation): LocationQuery
    {
        $query = new LocationQuery();
        $query->aggregations[] = $aggregation;
        $query->filter = new MatchAll();
        $query->limit = 0;

        return $query;
    }
}
