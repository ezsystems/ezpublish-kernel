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

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
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
    /**
     * EZP-26186: Make sure index is NOT deleted on removal of version draft (affected Solr & content index on Elastic).
     */
    public function testDeleteVersion()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $searchService = $repository->getSearchService();

        $membersContentId = $this->generateId('content', 11);
        $contentInfo = $contentService->loadContentInfo($membersContentId);

        $draft = $contentService->createContentDraft($contentInfo);
        $contentService->deleteVersion($draft->getVersionInfo());

        $this->refreshSearch($repository);

        // Found
        $criterion = new Criterion\LocationId($contentInfo->mainLocationId);
        $query = new Query(array('filter' => $criterion));
        $result = $searchService->findContentInfo($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $contentInfo->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * EZP-26186: Make sure affected child locations are deleted on content deletion (affected Solr & Elastic).
     */
    public function testDeleteContent()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $searchService = $repository->getSearchService();

        $anonymousUsersContentId = $this->generateId('content', 42);
        $contentInfo = $contentService->loadContentInfo($anonymousUsersContentId);

        $contentService->deleteContent($contentInfo);

        $this->refreshSearch($repository);

        // Should not be found
        $criterion = new Criterion\ParentLocationId($contentInfo->mainLocationId);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(0, $result->totalCount);
    }

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

    public function testUpdateLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $searchService = $repository->getSearchService();

        $rootLocationId = 2;
        $locationToUpdate = $locationService->loadLocation($rootLocationId);

        $criterion = new Criterion\LogicalAnd([
            new Criterion\LocationId($rootLocationId),
            new Criterion\Location\Priority(Criterion\Operator::GT, 0),
        ]);

        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);

        $this->assertEquals(0, $result->totalCount);

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->priority = 4;
        $locationService->updateLocation($locationToUpdate, $locationUpdateStruct);

        $this->refreshSearch($repository);

        $result = $searchService->findLocations($query);

        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $locationToUpdate->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Testing that content will be deleted with all of its subitems but subitems with additional location will stay as
     * they are.
     */
    public function testDeleteLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $treeContainerContent = $this->createContentWithName('Tree Container', [2]);
        $supposeBeDeletedSubItem = $this->createContentWithName(
            'Suppose to be deleted sub-item',
            [$treeContainerContent->contentInfo->mainLocationId]
        );
        $supposeSurviveSubItem = $this->createContentWithName(
            'Suppose to Survive Item',
            [2, $treeContainerContent->contentInfo->mainLocationId]
        );

        $treeContainerLocation = $locationService->loadLocation($treeContainerContent->contentInfo->mainLocationId);

        $this->refreshSearch($repository);

        $this->assertContentIdSearch($treeContainerContent->id, 1);
        $this->assertContentIdSearch($supposeSurviveSubItem->id, 1);
        $this->assertContentIdSearch($supposeBeDeletedSubItem->id, 1);

        $locationService->deleteLocation($treeContainerLocation);

        $this->refreshSearch($repository);

        $this->assertContentIdSearch($supposeSurviveSubItem->id, 1);
        $this->assertContentIdSearch($treeContainerContent->id, 0);
        $this->assertContentIdSearch($supposeBeDeletedSubItem->id, 0);
    }

    /**
     * Will create if not exists an simple content type for deletion test purpose with just and required field name.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createDeletionTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        try {
            return $contentTypeService->loadContentTypeByIdentifier('deletion-test');
        } catch (NotFoundException $e) {
            // continue creation process
        }

        $nameField = $contentTypeService->newFieldDefinitionCreateStruct('name', 'ezstring');
        $nameField->fieldGroup = 'main';
        $nameField->position = 1;
        $nameField->isTranslatable = true;
        $nameField->isSearchable = true;
        $nameField->isRequired = true;

        $contentTypeStruct = $contentTypeService->newContentTypeCreateStruct('deletion-test');
        $contentTypeStruct->mainLanguageCode = 'eng-GB';
        $contentTypeStruct->creatorId = 14;
        $contentTypeStruct->creationDate = new DateTime();
        $contentTypeStruct->names = ['eng-GB' => 'Deletion test'];
        $contentTypeStruct->addFieldDefinition($nameField);

        $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');

        $contentTypeDraft = $contentTypeService->createContentType($contentTypeStruct, [$contentTypeGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier('deletion-test');
    }

    /**
     * Will create and publish an content with a filed with a given content name in location provided into
     * $parentLocationIdList.
     *
     * @param string $contentName
     * @param array $parentLocationIdList
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentWithName($contentName, array $parentLocationIdList = array())
    {
        $contentService = $this->getRepository()->getContentService();
        $locationService = $this->getRepository()->getLocationService();

        $testableContentType = $this->createDeletionTestContentType();

        $rootContentStruct = $contentService->newContentCreateStruct($testableContentType, 'eng-GB');
        $rootContentStruct->setField('name', $contentName);

        $parentLocationList = [];
        foreach ($parentLocationIdList as $locationID) {
            $parentLocationList[] = $locationService->newLocationCreateStruct($locationID);
        }

        $contentDraft = $contentService->createContent($rootContentStruct, $parentLocationList);
        $publishedContent = $contentService->publishVersion($contentDraft->getVersionInfo());

        return $publishedContent;
    }

    /**
     * Asserts an content id if it exists still in the solr core.
     *
     * @param int $contentId
     * @param int $expectedCount
     */
    protected function assertContentIdSearch($contentId, $expectedCount)
    {
        $searchService = $this->getRepository()->getSearchService();

        $criterion = new Criterion\ContentId($contentId);
        $query = new Query(array('filter' => $criterion));
        $result = $searchService->findContent($query);

        $this->assertEquals($expectedCount, $result->totalCount);
        if ($expectedCount == 0) {
            return;
        }

        $this->assertEquals(
            $contentId,
            $result->searchHits[0]->valueObject->id
        );
    }
}
