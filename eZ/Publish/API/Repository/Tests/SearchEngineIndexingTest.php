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

    /**
     * Test that a newly created user is available for search.
     */
    public function testCreateUser()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $searchService = $repository->getSearchService();

        // ID of the "Editors" user group
        $editorsGroupId = 13;
        $userCreate = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        $userCreate->enabled = true;
        $userCreate->setField('first_name', 'Example');
        $userCreate->setField('last_name', 'User');

        // Load parent group for the user
        $group = $userService->loadUserGroup($editorsGroupId);

        // Create a new user instance.
        $user = $userService->createUser($userCreate, array($group));

        $this->refreshSearch($repository);

        // Should be found
        $criterion = new Criterion\ContentId($user->id);
        $query = new Query(array('filter' => $criterion));
        $result = $searchService->findContentInfo($query);
        $this->assertEquals(1, $result->totalCount);
    }

    /**
     * Test that a newly created user group is available for search.
     */
    public function testCreateUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        $searchService = $repository->getSearchService();
        $mainGroupId = $this->generateId('group', 4);

        $parentUserGroup = $userService->loadUserGroup($mainGroupId);
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct('eng-GB');
        $userGroupCreateStruct->setField('name', 'Example Group');

        // Create a new user group
        $userGroup = $userService->createUserGroup(
            $userGroupCreateStruct,
            $parentUserGroup
        );

        $this->refreshSearch($repository);

        // Should be found
        $criterion = new Criterion\ContentId($userGroup->id);
        $query = new Query(array('filter' => $criterion));
        $result = $searchService->findContentInfo($query);
        $this->assertEquals(1, $result->totalCount);
    }

    /**
     * Test that a newly created Location is available for search.
     */
    public function testCreateLocation()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $membersLocation = $this->createNewTestLocation();

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

    /**
     * Test that hiding a Location makes it unavailable for search.
     */
    public function testHideSubtree()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        // 5 is the ID of an existing location
        $locationId = $this->generateId('location', 5);
        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocation($locationId);
        $locationService->hideLocation($location);
        $this->refreshSearch($repository);

        // Check if parent location is hidden
        $criterion = new Criterion\LocationId($locationId);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertTrue($result->searchHits[0]->valueObject->hidden);

        // Check if children locations are invisible
        $criterion = new Criterion\ParentLocationId($locationId);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        foreach ($result->searchHits as $searchHit) {
            $this->assertTrue($searchHit->valueObject->invisible, sprintf('Location %s is not hidden', $searchHit->valueObject->id));
        }
    }

    /**
     * Test that hiding and revealing a Location makes it available for search.
     */
    public function testRevealSubtree()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        // 5 is the ID of an existing location
        $locationId = $this->generateId('location', 5);
        $locationService = $repository->getLocationService();
        $location = $locationService->loadLocation($locationId);
        $locationService->hideLocation($location);
        $this->refreshSearch($repository);
        $locationService->unhideLocation($location);
        $this->refreshSearch($repository);

        // Check if parent location is not hidden
        $criterion = new Criterion\LocationId($locationId);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertFalse($result->searchHits[0]->valueObject->hidden);

        // Check if children locations are not invisible
        $criterion = new Criterion\ParentLocationId($locationId);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        foreach ($result->searchHits as $searchHit) {
            $this->assertFalse($searchHit->valueObject->invisible, sprintf('Location %s is not hidden', $searchHit->valueObject->id));
        }
    }

    /**
     * Test that a copied subtree is available for search.
     */
    public function testCopySubtree()
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

        $copiedLocation = $locationService->copySubtree($adminsLocation, $editorsLocation);
        $this->refreshSearch($repository);

        // Found under Members
        $criterion = new Criterion\ParentLocationId($membersLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $adminsLocation->id,
            $result->searchHits[0]->valueObject->id
        );

        // Found under Editors
        $criterion = new Criterion\ParentLocationId($editorsLocation->id);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);
        $this->assertEquals(1, $result->totalCount);
        $this->assertEquals(
            $copiedLocation->id,
            $result->searchHits[0]->valueObject->id
        );
    }

    /**
     * Test that moved subtree is available for search and found only under a specific parent Location.
     */
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

    /**
     * Test that updated Location is available for search.
     */
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
     * Test content is available for search after being published.
     */
    public function testPublishVersion()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $publishedContent = $this->createContentWithName('publishedContent', [2]);
        $this->refreshSearch($repository);

        $criterion = new Criterion\FullText('publishedContent');
        $query = new Query(['filter' => $criterion]);
        $result = $searchService->findContent($query);

        $this->assertCount(1, $result->searchHits);
        $this->assertEquals($publishedContent->contentInfo->id, $result->searchHits[0]->valueObject->contentInfo->id);

        // Searching for children of locationId=2 should also hit this content
        $criterion = new Criterion\ParentLocationId(2);
        $query = new LocationQuery(array('filter' => $criterion));
        $result = $searchService->findLocations($query);

        foreach ($result->searchHits as $searchHit) {
            if ($searchHit->valueObject->contentInfo->id === $publishedContent->contentInfo->id) {
                return;
            }
        }
        $this->fail('Parent location sub-items do not contain published content');
    }

    /**
     * Test recovered content is available for search.
     */
    public function testRecoverLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $trashService = $repository->getTrashService();
        $searchService = $repository->getSearchService();

        $publishedContent = $this->createContentWithName('recovery-test', [2]);
        $location = $locationService->loadLocation($publishedContent->contentInfo->mainLocationId);

        $trashService->trash($location);
        $this->refreshSearch($repository);

        $criterion = new Criterion\LocationId($location->id);
        $query = new LocationQuery(['filter' => $criterion]);
        $locations = $searchService->findLocations($query);
        $this->assertEquals(0, $locations->totalCount);

        $trashItem = $trashService->loadTrashItem($location->id);
        $trashService->recover($trashItem);
        $this->refreshSearch($repository);

        $locations = $searchService->findLocations($query);
        $this->assertEquals(0, $locations->totalCount);
        $this->assertContentIdSearch($publishedContent->contentInfo->id, 1);
    }

    /**
     * Test copied content is available for search.
     */
    public function testCopyContent()
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $publishedContent = $this->createContentWithName('copyTest', [2]);
        $this->refreshSearch($repository);
        $criterion = new Criterion\FullText('copyTest');
        $query = new Query(['filter' => $criterion]);
        $result = $searchService->findContent($query);
        $this->assertCount(1, $result->searchHits);

        $copiedContent = $contentService->copyContent($publishedContent->contentInfo, $locationService->newLocationCreateStruct(2));
        $this->refreshSearch($repository);
        $result = $searchService->findContent($query);
        $this->assertCount(2, $result->searchHits);

        $this->assertContentIdSearch($publishedContent->contentInfo->id, 1);
        $this->assertContentIdSearch($copiedContent->contentInfo->id, 1);
    }

    /**
     * Check if FullText indexing works for special cases of text.
     *
     * @param string $text Content Item field value text (to be indexed)
     * @param string $searchForText text based on which Content Item should be found
     * @dataProvider getSpecialFullTextCases
     */
    public function testIndexingSpecialFullTextCases($text, $searchForText)
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $content = $this->createContentWithName($text, [2]);
        $this->refreshSearch($repository);

        $criterion = new Criterion\FullText($searchForText);
        $query = new Query(['filter' => $criterion]);
        $result = $searchService->findContent($query);

        // for some cases there might be more than one hit, so check if proper one was found
        foreach ($result->searchHits as $searchHit) {
            if ($content->contentInfo->id === $searchHit->valueObject->versionInfo->contentInfo->id) {
                return;
            }
        }
        $this->fail('Failed to find required Content in search results');
    }

    /**
     * Data Provider for {@see testIndexingSpecialFullTextCases()} method.
     *
     * @return array
     */
    public function getSpecialFullTextCases()
    {
        return [
            ['UPPERCASE TEXT', 'uppercase text'],
            ['lowercase text', 'LOWERCASE TEXT'],
            ['text-with-hyphens', 'text-with-hyphens'],
            ['text containing spaces', 'text containing spaces'],
            ['"quoted text"', '"quoted text"'],
        ];
    }

    /**
     * Will create if not exists a simple content type for test purposes with just one required field name.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createTestContentType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentTypeIdentifier = 'test-type';
        try {
            return $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        } catch (NotFoundException $e) {
            // continue creation process
        }

        $nameField = $contentTypeService->newFieldDefinitionCreateStruct('name', 'ezstring');
        $nameField->fieldGroup = 'main';
        $nameField->position = 1;
        $nameField->isTranslatable = true;
        $nameField->isSearchable = true;
        $nameField->isRequired = true;

        $contentTypeStruct = $contentTypeService->newContentTypeCreateStruct($contentTypeIdentifier);
        $contentTypeStruct->mainLanguageCode = 'eng-GB';
        $contentTypeStruct->creatorId = 14;
        $contentTypeStruct->creationDate = new DateTime();
        $contentTypeStruct->names = ['eng-GB' => 'Test Content Type'];
        $contentTypeStruct->addFieldDefinition($nameField);

        $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');

        $contentTypeDraft = $contentTypeService->createContentType($contentTypeStruct, [$contentTypeGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
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

        $testableContentType = $this->createTestContentType();

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

    /**
     * Create & get new Location for tests.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    protected function createNewTestLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $rootLocationId = 2;
        $membersContentId = 11;
        $membersContentInfo = $contentService->loadContentInfo($membersContentId);

        $locationCreateStruct = $locationService->newLocationCreateStruct($rootLocationId);

        return $locationService->createLocation($membersContentInfo, $locationCreateStruct);
    }
}
