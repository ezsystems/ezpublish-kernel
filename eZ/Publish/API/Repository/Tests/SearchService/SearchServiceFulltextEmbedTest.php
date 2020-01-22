<?php

declare(strict_types=1);

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\SearchService;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\FieldType\RichText\Value as RichTextValue;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;

/**
 * Test case for full text search in the SearchService (for embed).
 *
 * @see \eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 * @group fulltext
 */
class SearchServiceFulltextEmbedTest extends BaseTest
{
    private const EMBEDDED_ARTICLE_NAME = 'test1';

    private static $createdIds = [];

    protected function setUp()
    {
        parent::setUp();

        $repository = $this->getRepository(false);

        if (false === $repository->getSearchService()->supports(SearchService::CAPABILITY_ADVANCED_FULLTEXT)) {
            $this->markTestSkipped('Advanced FullText search is not supported by the current search engine');
        }
    }

    public function testPrepareContent(): void
    {
        $contentService = $this->getRepository()->getContentService();
        $baseArticleStruct = $this->prepareBaseArticleStruct();

        $embeddedArticleStruct = $this->fillEmbeddedArticleStruct(clone $baseArticleStruct);
        $embeddedArticleContent = $contentService->publishVersion(
            $this->createContent($embeddedArticleStruct)->versionInfo
        );

        $mainArticleStruct = $this->fillMainArticleStruct(clone $baseArticleStruct, $embeddedArticleContent->id);
        $mainArticleContent = $contentService->publishVersion(
            $this->createContent($mainArticleStruct)->versionInfo
        );

        $this->refreshSearch($this->getRepository());

        self::$createdIds = [
            $embeddedArticleContent->id,
            $mainArticleContent->id,
        ];
    }

    /**
     * @depends testPrepareContent
     */
    public function testFulltextContentSearch(): void
    {
        $searchService = $this->getRepository()->getSearchService();

        $query = new Query([
            'query' => new Criterion\FullText(self::EMBEDDED_ARTICLE_NAME),
        ]);

        $searchResult = $searchService->findContent($query);

        $this->assertGreaterThanOrEqual(2, $searchResult->totalCount);
        $this->assertResults($searchResult->searchHits);
    }

    /**
     * @depends testPrepareContent
     */
    public function testFulltextLocationSearch(): void
    {
        $searchService = $this->getRepository()->getSearchService();

        $query = new LocationQuery([
            'query' => new Criterion\FullText(self::EMBEDDED_ARTICLE_NAME),
        ]);

        $searchResult = $searchService->findLocations($query);

        $this->assertGreaterThanOrEqual(2, $searchResult->totalCount);
        $this->assertResults($searchResult->searchHits);
    }

    private function prepareBaseArticleStruct(): ContentCreateStruct
    {
        $introDocument = new \DOMDocument();
        $introDocument->loadXML(
            <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>some paragraph</para>
</section>
EOT
        );

        $repository = $this->getRepository();
        $contentType = $repository->getContentTypeService()->loadContentTypeByIdentifier('article');

        /** @var \eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct $articleStruct */
        $articleStruct = $repository->getContentService()->newContentCreateStruct($contentType, 'eng-GB');
        $articleStruct->setField('intro', new RichTextValue($introDocument), 'eng-GB');

        return $articleStruct;
    }

    private function fillEmbeddedArticleStruct(ContentCreateStruct $articleStruct): ContentCreateStruct
    {
        $articleBodyDoc = new \DOMDocument();
        $articleBodyDoc->loadXML(
            <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para>body-content</para>
</section>
EOT
        );

        $articleStruct->setField('title', self::EMBEDDED_ARTICLE_NAME);
        $articleStruct->setField('body', new RichTextValue($articleBodyDoc), 'eng-GB');

        return $articleStruct;
    }

    private function fillMainArticleStruct(ContentCreateStruct $articleStruct, int $embedContentId): ContentCreateStruct
    {
        $mainArticleBodyDoc = new \DOMDocument();
        $mainArticleBodyDoc->loadXML(
            <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ezxhtml="http://ez.no/xmlns/ezpublish/docbook/xhtml" xmlns:ezcustom="http://ez.no/xmlns/ezpublish/docbook/custom" version="5.0-variant ezpublish-1.0">
<para><ezembedinline xlink:href="ezcontent://{$embedContentId}" view="embed-inline"/></para>
</section>
EOT
        );

        $articleStruct->setField('title', 'test');
        $articleStruct->setField('body', new RichTextValue($mainArticleBodyDoc), 'eng-GB');

        return $articleStruct;
    }

    private function createContent(ContentCreateStruct $contentCreateStruct): Content
    {
        $repository = $this->getRepository();

        return $repository->getContentService()->createContent(
            $contentCreateStruct,
            [$repository->getLocationService()->newLocationCreateStruct(2)]
        );
    }

    private function assertResults(array $searchHits): void
    {
        $resultIds = [];

        /** @var SearchHit $contentItem */
        foreach ($searchHits as $contentItem) {
            $resultIds[] = $contentItem->valueObject->contentInfo->id;
        }

        $this->assertTrue(
            count(array_intersect($resultIds, self::$createdIds)) === 2
        );
    }
}
