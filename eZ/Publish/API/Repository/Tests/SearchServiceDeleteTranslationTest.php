<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

/**
 * Test case for delete content translation with the SearchService.
 *
 * @see \eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceDeleteTranslationTest extends BaseTest
{
    /**
     * @throws \ErrorException
     */
    public function setUp()
    {
        $setupFactory = $this->getSetupFactory();

        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete('Not implemented for Elasticsearch Search Engine');
        }

        parent::setUp();
    }

    /**
     * @param array $languages
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function createTestContentWithLanguages(array $languages): Content
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentTypeArticle = $contentTypeService->loadContentTypeByIdentifier('article');
        $contentCreateStructArticle = $contentService->newContentCreateStruct(
            $contentTypeArticle,
            'eng-GB'
        );

        foreach ($languages as $langCode => $title) {
            $contentCreateStructArticle->setField('title', $title, $langCode);
            $contentCreateStructArticle->setField(
                'intro',
                '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" version="5.0-variant ezpublish-1.0">
  <para>' . $title . '</para>
</section>',
                $langCode
            );
        }

        $locationCreateStructArticle = $locationService->newLocationCreateStruct(2);
        $draftArticle = $contentService->createContent(
            $contentCreateStructArticle,
            [$locationCreateStructArticle]
        );
        $content = $contentService->publishVersion($draftArticle->getVersionInfo());
        $this->refreshSearch($repository);

        return $content;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function findContent(string $text, string $languageCode): SearchResult
    {
        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();
        $query = new Query();
        $query->query = new Criterion\FullText($text);
        $query->limit = 0;
        $languageFilter = [
            'languages' => [$languageCode],
            'useAlwaysAvailable' => true,
            'excludeTranslationsFromAlwaysAvailable' => false,
        ];

        return $searchService->findContent($query, $languageFilter);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTranslation()
    {
        $repository = $this->getRepository();
        $testContent = $this->createTestContentWithLanguages(
            [
                'eng-GB' => 'Contact',
                'ger-DE' => 'Kontakt',
            ]
        );
        $contentService = $repository->getContentService();
        $searchResult = $this->findContent('Kontakt', 'ger-DE');
        $this->assertEquals(1, $searchResult->totalCount);

        $contentService->deleteTranslation($testContent->contentInfo, 'ger-DE');
        $searchResult = $this->findContent('Kontakt', 'ger-DE');
        $this->assertEquals(0, $searchResult->totalCount);
    }
}
