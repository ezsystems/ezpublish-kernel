<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\DiffStatus;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff\StringDiff;
use eZ\Publish\API\Repository\Values\Content\VersionDiff\VersionDiff;
use OutOfBoundsException;

class ContentComparisonServiceTest extends BaseContentServiceTest
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentComparisonService */
    private $contentComparisonService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    public function setUp(): void
    {
        parent::setUp();

        $repository = $this->getRepository();
        $this->contentService = $repository->getContentService();
        $this->contentComparisonService = $repository->getContentComparisonService();
        $this->contentTypeService = $repository->getContentTypeService();
    }

    public function testCompareVersions(): void
    {
        $draft2 = $this->createUpdatedDraftVersion2();
        $content = $this->contentService->publishVersion($draft2->versionInfo);

        $versions = $this->contentService->loadVersions($content->contentInfo);

        $this->assertCount(2, $versions);

        $versionDiff = $this->contentComparisonService->compareVersions($versions[0], $versions[1], 'eng-US');

        $this->assertInstanceOf(
            VersionDiff::class,
            $versionDiff
        );

        $fieldDiff = $versionDiff->getFieldValueDiffByIdentifier('name');

        /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextLineComparisonResult $textCompareResult */
        $textCompareResult = $fieldDiff->getComparisonResult();

        $expectedDiff = [
            new StringDiff('An', DiffStatus::UNCHANGED),
            new StringDiff('awesome', DiffStatus::UNCHANGED),
            new StringDiff('forum', DiffStatus::REMOVED),
            new StringDiff('forum²', DiffStatus::ADDED),
        ];

        $this->assertEquals($expectedDiff, $textCompareResult->getStringDiffs());
    }

    public function testCompareVersionsFromDifferentContent(): void
    {
        $draftA = $this->createContentDraft(
            'forum',
            2,
            [
                'name' => 'content one',
            ]
        );
        $draftB = $this->createContentDraft(
            'forum',
            2,
            [
                'name' => 'content two',
            ]
        );
        $contentA = $this->contentService->publishVersion($draftA->versionInfo);
        $contentB = $this->contentService->publishVersion($draftB->versionInfo);

        $this->expectException(InvalidArgumentException::class);
        $this->contentComparisonService->compareVersions($contentA->versionInfo, $contentB->versionInfo);
    }

    public function testCompareVersionsWhenFieldRemovedFromContentType(): void
    {
        $draftA = $this->createContentDraft(
            'folder',
            2,
            [
                'name' => 'content one',
                'short_name' => 'shortName content one',
            ]
        );
        $this->contentService->publishVersion($draftA->versionInfo);
        $this->removeFieldFromContentType('folder', 'short_name');

        $contentA = $this->contentService->loadContent($draftA->id, null, 1);

        $draftB = $this->contentService->createContentDraft($contentA->contentInfo);
        $struct = $this->contentService->newContentUpdateStruct();
        $struct->setField('name', 'content two');

        $this->contentService->updateContent($draftB->versionInfo, $struct);
        $contentB = $this->contentService->publishVersion($draftB->versionInfo);

        $versionDiff = $this->contentComparisonService->compareVersions($contentA->versionInfo, $contentB->versionInfo);

        $versionDiff->getFieldValueDiffByIdentifier('name');

        $this->expectException(OutOfBoundsException::class);
        $versionDiff->getFieldValueDiffByIdentifier('short_name');
    }

    public function testCompareVersionsWhenFieldAddedToContentType(): void
    {
        $draftA = $this->createContentDraft(
            'folder',
            2,
            [
                'name' => 'content one',
            ]
        );
        $this->contentService->publishVersion($draftA->versionInfo);
        $this->addFieldToContentType('folder', 'new_name', 'ezstring');

        $contentA = $this->contentService->loadContent($draftA->id, null, 1);

        $draftB = $this->contentService->createContentDraft($contentA->contentInfo);
        $struct = $this->contentService->newContentUpdateStruct();
        $struct->setField('new_name', 'content two new');

        $this->contentService->updateContent($draftB->versionInfo, $struct);
        $contentB = $this->contentService->publishVersion($draftB->versionInfo);

        $versionDiff = $this->contentComparisonService->compareVersions($contentA->versionInfo, $contentB->versionInfo);

        $fieldDiff = $versionDiff->getFieldValueDiffByIdentifier('new_name');

        /** @var \eZ\Publish\API\Repository\Values\Content\VersionDiff\FieldType\TextLineComparisonResult $textCompareResult */
        $textCompareResult = $fieldDiff->getComparisonResult();

        $expectedDiff = [
            new StringDiff('content two new', DiffStatus::ADDED),
        ];

        $this->assertEquals($expectedDiff, $textCompareResult->getStringDiffs());
    }

    public function addFieldToContentType(
        string $contentTypeIdentifier,
        string $fieldIdentifier,
        string $fieldTypeIdentifier
    ): void {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);

        $fieldDefCreate = $this->contentTypeService->newFieldDefinitionCreateStruct(
            $fieldIdentifier,
            $fieldTypeIdentifier
        );

        $this->contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefCreate);
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }

    protected function removeFieldFromContentType(
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier
    ): void {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $contentType->getFieldDefinition($fieldDefinitionIdentifier)
        );

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }
}
