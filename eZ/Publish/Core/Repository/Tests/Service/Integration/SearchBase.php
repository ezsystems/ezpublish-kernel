<?php

/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\SearchBase class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Tests\BaseTest as APIBaseTest;

/**
 * Test case for Content service.
 */
abstract class SearchBase extends BaseServiceTest
{
    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContent()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();
        $query = new Query(
            [
                'filter' => new Criterion\ContentId([4]),
                'offset' => 0,
            ]
        );

        $searchResult = $searchService->findContent(
            $query,
            [],
            false
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult',
            $searchResult
        );

        $this->assertEquals(1, $searchResult->totalCount);
        $this->assertCount($searchResult->totalCount, $searchResult->searchHits);
        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchHit',
            reset($searchResult->searchHits)
        );
    }

    /**
     * Test for the findContent() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findContent
     */
    public function testFindContentWithLanguageFilter()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();
        $query = new Query(
            [
                'filter' => new Criterion\ContentId([4]),
                'offset' => 0,
            ]
        );

        $searchResult = $searchService->findContent(
            $query,
            ['languages' => ['eng-US']],
            false
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchResult',
            $searchResult
        );

        $this->assertEquals(1, $searchResult->totalCount);
        $this->assertCount($searchResult->totalCount, $searchResult->searchHits);
        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\Content\\Search\\SearchHit',
            reset($searchResult->searchHits)
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
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();

        // Throws an exception because content with given id does not exist
        $searchResult = $searchService->findSingle(
            new Criterion\ContentId([APIBaseTest::DB_INT_MAX]),
            ['languages' => ['eng-US']],
            false
        );
        /* END: Use Case */
    }

    /**
     * Test for the findSingle() method.
     *
     * @covers \eZ\Publish\Core\Repository\SearchService::findSingle
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleThrowsInvalidArgumentException()
    {
        /* BEGIN: Use Case */
        $searchService = $this->repository->getSearchService();

        // Throws an exception because more than one result was returned for the given query
        $searchResult = $searchService->findSingle(
            new Criterion\ContentId([4, 10]),
            ['languages' => ['eng-US']]
        );
        /* END: Use Case */
    }
}
