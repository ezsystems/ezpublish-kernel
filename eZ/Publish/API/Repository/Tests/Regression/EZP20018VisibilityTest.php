<?php

/**
 * File containing the EZP20018VisibilityTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility;

/**
 * Test case for Visibility issues in EZP-20018.
 *
 * Issue EZP-20018
 */
class EZP20018VisibilityTest extends BaseTest
{
    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility
     */
    public function testSearchForHiddenContent()
    {
        $repository = $this->getRepository();

        $query = new Query();
        $query->filter = new Visibility(Visibility::HIDDEN);
        $results1 = $repository->getSearchService()->findContent($query);

        $this->assertEquals(0, $results1->totalCount);
        $this->assertCount(0, $results1->searchHits);

        // Hide "Images" Folder
        $locationService = $repository->getLocationService();
        $locationService->hideLocation($locationService->loadLocation(54));

        $this->refreshSearch($repository);

        // Assert updated values
        $results2 = $repository->getSearchService()->findContent($query);

        $this->assertEquals(1, $results2->totalCount);
        $this->assertCount(1, $results2->searchHits);
    }

    /**
     * @see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Visibility
     */
    public function testSearchForVisibleContent()
    {
        $repository = $this->getRepository();

        $query = new Query();
        $query->filter = new Visibility(Visibility::VISIBLE);
        $query->limit = 50;
        $results1 = $repository->getSearchService()->findContent($query);

        $this->assertEquals(18, $results1->totalCount);
        $this->assertEquals($results1->totalCount, count($results1->searchHits));

        // Hide "Images" Folder
        $locationService = $repository->getLocationService();
        $locationService->hideLocation($locationService->loadLocation(54));

        $this->refreshSearch($repository);

        // Assert updated values
        $results2 = $repository->getSearchService()->findContent($query);

        $this->assertEquals($results1->totalCount - 1, $results2->totalCount);
        $this->assertEquals($results2->totalCount, count($results2->searchHits));
    }
}
