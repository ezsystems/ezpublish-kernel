<?php
/**
 * File containing the ContentSearchAdapterTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
    protected function getAdapter( Query $query, SearchService $searchService )
    {
        return new ContentSearchAdapter( $query, $searchService );
    }

    /**
     * Returns expected result from adapter from search hits.
     *
     * @param $hits
     *
     * @return mixed
     */
    protected function getExpectedFinalResultFromHits( $hits )
    {
        $expectedResult = array();

        /** @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[] $hits */
        foreach ( $hits as $hit )
        {
            $expectedResult[] = $hit->valueObject;
        }

        return $expectedResult;
    }
}
