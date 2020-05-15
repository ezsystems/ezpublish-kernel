<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
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
final class DeleteTranslationTest extends BaseTest
{
    /**
     * @throws \ErrorException
     */
    public function setUp(): void
    {
        $setupFactory = $this->getSetupFactory();

        if ($setupFactory instanceof LegacyElasticsearch) {
            $this->markTestIncomplete('Not implemented for Elasticsearch Search Engine');
        }

        parent::setUp();
    }

    /**
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
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTranslation(): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $testContent = $this->createFolder(['eng-GB' => 'Contact', 'ger-DE' => 'Kontakt'], 2);
        $this->createFolder(['eng-GB' => 'OtherEngContent', 'ger-DE' => 'OtherGerContent'], 2);
        $this->refreshSearch($repository);

        $searchResult = $this->findContent('Kontakt', 'ger-DE');
        $this->assertEquals(1, $searchResult->totalCount);

        $contentService->deleteTranslation($testContent->contentInfo, 'ger-DE');
        $this->refreshSearch($repository);
        $searchResult = $this->findContent('Kontakt', 'ger-DE');
        $this->assertEquals(
            0,
            $searchResult->totalCount,
            'Found reference to the deleted Content translation'
        );

        // check if unrelated items were not affected
        $searchResult = $this->findContent('OtherGerContent', 'ger-DE');
        $this->assertEquals(1, $searchResult->totalCount, 'Unrelated translation was deleted');
    }
}
