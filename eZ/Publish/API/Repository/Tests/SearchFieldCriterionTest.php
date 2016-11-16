<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use DateTime;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

class SearchFieldCriterionTest extends BaseTest
{
    /**
     * Data provider for {@see testSearchingByFieldCriterionOnNonSearchableField}.
     *
     * @return array
     */
    public function providerForTestSearchingByFieldCriterion()
    {
        return [
            ['ezstring'],
            ['eztext'],
        ];
    }

    /**
     * Test that searching using Criterion\Field on non-searchable fields returns proper content.
     *
     * @dataProvider providerForTestSearchingByFieldCriterion
     *
     * @param string $fieldTypeIdentifier
     */
    public function testSearchingByFieldCriterionOnNonSearchableField($fieldTypeIdentifier)
    {
        $repository = $this->getRepository();
        $fieldIdentifier = 'test_field_for_' . $fieldTypeIdentifier;
        $contentType = $this->createContentTypeWithNonSearchableField(
            'test-content-type',
            $fieldIdentifier,
            $fieldTypeIdentifier
        );
        $publishedContent = $this->publishTestContent($contentType, $fieldIdentifier, 'Test field value for ' . $fieldTypeIdentifier);

        $searchService = $repository->getSearchService();

        $criterion = new Criterion\Field(
            $fieldIdentifier,
            Criterion\Operator::CONTAINS,
            $publishedContent->getField($fieldIdentifier)->value->text
        );
        $query = new Query(['filter' => $criterion]);
        $results = $searchService->findContent($query);
        $this->assertEquals(1, $results->totalCount);
        $this->assertEquals($publishedContent->id, $results->searchHits[0]->valueObject->id);
    }

    /**
     * @param string $testContentTypeIdentifier
     * @param string $testFieldIdentifier
     * @param string $testFieldTypeIdentifier
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createContentTypeWithNonSearchableField(
        $testContentTypeIdentifier,
        $testFieldIdentifier,
        $testFieldTypeIdentifier
    ) {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $testField = $contentTypeService->newFieldDefinitionCreateStruct($testFieldIdentifier, $testFieldTypeIdentifier);
        $testField->fieldGroup = 'main';
        $testField->position = 2;
        $testField->isTranslatable = true;
        $testField->isSearchable = false;
        $testField->isRequired = true;

        $contentTypeStruct = $contentTypeService->newContentTypeCreateStruct($testContentTypeIdentifier);
        $contentTypeStruct->mainLanguageCode = 'eng-GB';
        $contentTypeStruct->creatorId = 14;
        $contentTypeStruct->creationDate = new DateTime();
        $contentTypeStruct->names = ['eng-GB' => 'Test Content Type'];
        $contentTypeStruct->addFieldDefinition($testField);

        $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');
        $contentTypeDraft = $contentTypeService->createContentType($contentTypeStruct, [$contentTypeGroup]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier($testContentTypeIdentifier);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $testFieldIdentifier
     * @param string $testFieldValue
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function publishTestContent(ContentType $contentType, $testFieldIdentifier, $testFieldValue)
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-GB');
        $contentCreateStruct->setField($testFieldIdentifier, $testFieldValue);

        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationService->newLocationCreateStruct(2)]);

        return $contentService->publishVersion($contentDraft->getVersionInfo());
    }
}
