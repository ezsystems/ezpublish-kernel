<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\URL\SearchResult;
use eZ\Publish\API\Repository\Values\URL\URLQuery;
use eZ\Publish\API\Repository\Values\URL\UsageSearchResult;

/**
 * Base class for URLService tests.
 */
abstract class BaseURLServiceTest extends BaseTest
{
    private const URL_CONTENT_TYPE_IDENTIFIER = 'link_ct';

    protected function doTestFindUrls(URLQuery $query, array $expectedUrls, $expectedTotalCount = null, $ignoreOrder = true)
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $searchResult = $repository->getURLService()->findUrls($query);
        /* END: Use Case */

        if ($expectedTotalCount === null) {
            $expectedTotalCount = count($expectedUrls);
        }

        $this->assertInstanceOf(SearchResult::class, $searchResult);
        $this->assertEquals($expectedTotalCount, $searchResult->totalCount);
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

    protected function assertSearchResultItemsAreUnique(SearchResult $results): void
    {
        $visitedUrls = [];

        foreach ($results->items as $item) {
            $this->assertNotContains(
                $item->url,
                $visitedUrls,
                'Search results contains duplicated url: ' . $item->url
            );

            $visitedUrls[] = $item->url;
        }
    }

    protected function assertUsagesSearchResultItems(UsageSearchResult $searchResult, array $expectedContentInfoIds)
    {
        $this->assertCount(count($expectedContentInfoIds), $searchResult->items);
        foreach ($searchResult->items as $contentInfo) {
            $this->assertContains($contentInfo->id, $expectedContentInfoIds);
        }
    }

    protected function createContentWithLink(
        string $name, string $url,
        string $languageCode = 'eng-GB',
        int $parentLocationId = 2
    ): Content {
        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        try {
            $contentType = $contentTypeService->loadContentTypeByIdentifier(self::URL_CONTENT_TYPE_IDENTIFIER);
        } catch (NotFoundException $e) {
            $contentType = $this->createContentTypeWithUrl();
        }

        $struct = $contentService->newContentCreateStruct($contentType, $languageCode);
        $struct->setField('name', $name, $languageCode);
        $struct->setField('url', $url, $languageCode);

        $contentDraft = $contentService->createContent(
            $struct,
            [$locationService->newLocationCreateStruct($parentLocationId)]
        );

        return $contentService->publishVersion($contentDraft->versionInfo);
    }

    private function createContentTypeWithUrl(): ContentType
    {
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(self::URL_CONTENT_TYPE_IDENTIFIER);
        $typeCreate->mainLanguageCode = 'eng-GB';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = [
            'eng-GB' => 'URL: ' . self::URL_CONTENT_TYPE_IDENTIFIER,
        ];
        $typeCreate->descriptions = [
            'eng-GB' => '',
        ];
        $typeCreate->creatorId = $this->generateId('user', $repository->getPermissionResolver()->getCurrentUserReference()->getUserId());
        $typeCreate->creationDate = $this->createDateTime();

        $typeCreate->addFieldDefinition($this->createNameFieldDefinitionCreateStruct($contentTypeService));
        $typeCreate->addFieldDefinition($this->createUrlFieldDefinitionCreateStruct($contentTypeService));

        $contentTypeDraft = $contentTypeService->createContentType($typeCreate, [
            $contentTypeService->loadContentTypeGroupByIdentifier('Content'),
        ]);
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $contentTypeService->loadContentTypeByIdentifier(self::URL_CONTENT_TYPE_IDENTIFIER);
    }

    private function createNameFieldDefinitionCreateStruct(ContentTypeService $contentTypeService): FieldDefinitionCreateStruct
    {
        $nameFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('name', 'ezstring');
        $nameFieldCreate->names = [
            'eng-GB' => 'Name',
        ];
        $nameFieldCreate->descriptions = [
            'eng-GB' => '',
        ];
        $nameFieldCreate->fieldGroup = 'default';
        $nameFieldCreate->position = 1;
        $nameFieldCreate->isTranslatable = false;
        $nameFieldCreate->isRequired = true;
        $nameFieldCreate->isInfoCollector = false;
        $nameFieldCreate->validatorConfiguration = [
            'StringLengthValidator' => [
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ],
        ];
        $nameFieldCreate->fieldSettings = [];
        $nameFieldCreate->isSearchable = true;
        $nameFieldCreate->defaultValue = '';

        return $nameFieldCreate;
    }

    private function createUrlFieldDefinitionCreateStruct(ContentTypeService $contentTypeService): FieldDefinitionCreateStruct
    {
        $urlFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct('url', 'ezurl');
        $urlFieldCreate->names = [
            'eng-GB' => 'URL',
        ];
        $urlFieldCreate->descriptions = [
            'eng-GB' => '',
        ];
        $urlFieldCreate->fieldGroup = 'default';
        $urlFieldCreate->position = 2;
        $urlFieldCreate->isTranslatable = false;
        $urlFieldCreate->isRequired = true;
        $urlFieldCreate->isInfoCollector = false;
        $urlFieldCreate->validatorConfiguration = [];
        $urlFieldCreate->fieldSettings = [];
        $urlFieldCreate->isSearchable = false;
        $urlFieldCreate->defaultValue = '';

        return $urlFieldCreate;
    }
}
