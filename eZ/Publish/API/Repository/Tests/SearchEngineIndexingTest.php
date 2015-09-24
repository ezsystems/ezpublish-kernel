<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for indexing operations with a search engine.
 *
 * @group integration
 * @group search
 * @group indexing
 */
class SearchEngineIndexingTest extends BaseTest
{
    public function testCreateLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $searchService = $repository->getSearchService();

        $rootLocationId = 2;
        $membersContentId = 11;
        $membersContentInfo = $contentService->loadContentInfo($membersContentId);

        $locationCreateStruct = $locationService->newLocationCreateStruct($rootLocationId);
        $membersLocation = $locationService->createLocation($membersContentInfo, $locationCreateStruct);

        $this->refreshSearch($repository);

        // Found
        $criterion = new Criterion\LocationId($membersLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $membersLocation->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    public function testMoveSubtree()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $searchService = $repository->getSearchService();

        $rootLocationId = 2;
        $membersContentId = 11;
        $adminsContentId = 12;
        $editorsContentId = 13;
        $membersContentInfo = $contentService->loadContentInfo($membersContentId);
        $adminsContentInfo = $contentService->loadContentInfo($adminsContentId);
        $editorsContentInfo = $contentService->loadContentInfo($editorsContentId);

        $locationCreateStruct = $locationService->newLocationCreateStruct($rootLocationId);
        $membersLocation = $locationService->createLocation($membersContentInfo, $locationCreateStruct);
        $editorsLocation = $locationService->createLocation($editorsContentInfo, $locationCreateStruct);
        $adminsLocation = $locationService->createLocation(
            $adminsContentInfo,
            $locationService->newLocationCreateStruct($membersLocation->id)
        );

        $this->refreshSearch($repository);

        // Not found under Editors
        $criterion = new Criterion\ParentLocationId($editorsLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(0, $result->totalCount);

        // Found under Members
        $criterion = new Criterion\ParentLocationId($membersLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $adminsLocation->id,
            $result->searchHits[0]->valueObject->id
        );

        $locationService->moveSubtree($adminsLocation, $editorsLocation);
        $this->refreshSearch($repository);

        // Found under Editors
        $criterion = new Criterion\ParentLocationId($editorsLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $adminsLocation->id,
            $result->searchHits[0]->valueObject->id
        );

        // Not found under Members
        $criterion = new Criterion\ParentLocationId($membersLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(0, $result->totalCount);
    }
}
