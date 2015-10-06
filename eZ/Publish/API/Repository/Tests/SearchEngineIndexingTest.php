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
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use DateTime;

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

    /**
     * Testing that content is indexed even when containing only fields with values
     * considered to be empty by the search engine.
     */
    public function testIndexContentWithNullField()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $searchService = $repository->getSearchService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct('test-type');
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->names = array('eng-GB' => 'Test type');
        $createStruct->creatorId = 14;
        $createStruct->creationDate = new DateTime();

        $translatableFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'integer',
            'ezinteger'
        );
        $translatableFieldCreate->names = array('eng-GB' => 'Simple translatable integer field');
        $translatableFieldCreate->fieldGroup = 'main';
        $translatableFieldCreate->position = 1;
        $translatableFieldCreate->isTranslatable = true;
        $translatableFieldCreate->isSearchable = true;

        $createStruct->addFieldDefinition($translatableFieldCreate);

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType(
            $createStruct,
            array($contentGroup)
        );
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        $contentType = $contentTypeService->loadContentType($contentTypeDraft->id);

        $createStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $createStruct->alwaysAvailable = false;
        $createStruct->mainLanguageCode = 'eng-GB';

        $draft = $contentService->createContent($createStruct);
        $content = $contentService->publishVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        // Found
        $criterion = new Criterion\ContentId($content->id);
        $query = new Query(array('filter' => $criterion));
        $result = $searchService->findContent($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $content->id,
            $result->searchHits[0]->valueObject->id
        );
    }
}
