<?php

/**
 * File containing the ContentSearchAdapterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Tests;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;

class ContentSearchAdapterTest extends ContentSearchHitAdapterTest
{
    /**
     * @param Query $query
     * @param SearchService $searchService
     *
     * @return ContentSearchAdapter
     */
    protected function getAdapter(Query $query, SearchService $searchService)
    {
        return new ContentSearchAdapter($query, $searchService);
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
        $expectedResult = [];

        /** @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[] $hits */
        foreach ($hits as $hit) {
            $expectedResult[] = $hit->valueObject;
        }

        return $expectedResult;
    }
}
