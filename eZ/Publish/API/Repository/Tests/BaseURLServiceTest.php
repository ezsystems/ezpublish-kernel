<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;

/**
 * Base class for URLService tests.
 */
abstract class BaseURLServiceTest extends BaseTest
{
    protected function doTestFindUrls(URLQuery $query, array $expectedUrls, $exectedTotalCount = null, $ignoreOrder = true)
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $searchResult = $repository->getURLService()->findUrls($query);
        /* END: Use Case */

        if ($exectedTotalCount === null) {
            $exectedTotalCount = count($expectedUrls);
        }

        $this->assertInstanceOf(SearchResult::class, $searchResult);
        $this->assertEquals($exectedTotalCount, $searchResult->totalCount);
        $this->assertSearchResultItems($searchResult, $expectedUrls, $ignoreOrder);
    }

    protected function assertSearchResultItems(SearchResult $searchResult, array $expectedUrls, $ignoreOrder)
    {
        $this->assertCount(count($expectedUrls), $searchResult->items);

        foreach ($searchResult->items as $i => $item) {
            if ($ignoreOrder) {
                $this->assertContains($item->url, $expectedUrls);
            } else {
                $this->assertEquals($expectedUrls[$i], $item->url);
            }
        }
    }

    protected function assertUsagesSearchResultItems(UsageSearchResult $searchResult, array $expectedContentInfoIds)
    {
        $this->assertCount(count($expectedContentInfoIds), $searchResult->items);
        foreach ($searchResult->items as $contentInfo) {
            $this->assertContains($contentInfo->id, $expectedContentInfoIds);
        }
    }
}
