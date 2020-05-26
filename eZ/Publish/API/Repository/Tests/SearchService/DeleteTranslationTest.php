<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Test case for delete content translation with the SearchService.
 *
 * @see \eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class DeleteTranslationTest extends BaseTest
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
     * @param array $languages
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
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
        $this->refreshSearch($repository);
        $searchResult = $this->findContent('Kontakt', 'ger-DE');
        $this->assertEquals(0, $searchResult->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTranslationWithContentRemovePolicy(): void
    {
        $repository = $this->getRepository();
        $testContent = $this->createTestContentWithLanguages(
            [
                'eng-GB' => 'Contact',
                'ger-DE' => 'Kontakt',
                'eng-US' => 'Contact',
            ]
        );

        $user = $this->provideUserWithContentRemovePolicies();
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $contentService = $repository->getContentService();
        $searchResult = $this->findContent('Contact', 'eng-US');
        $this->assertEquals(1, $searchResult->totalCount);

        $contentService->deleteTranslation($testContent->contentInfo, 'eng-US');
        $this->refreshSearch($repository);
        $searchResult = $this->findContent('Contact', 'eng-US');
        $this->assertEquals(0, $searchResult->totalCount);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPreventTranslationDeletionIfNoAccess(): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $testContent = $this->createTestContentWithLanguages(
            [
                'eng-GB' => 'Contact',
                'ger-DE' => 'Kontakt',
                'eng-US' => 'Contact',
            ]
        );

        $user = $this->provideUserWithContentRemovePolicies();
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $this->expectException(UnauthorizedException::class);

        $contentService->deleteTranslation($testContent->contentInfo, 'ger-DE');
    }

    public function provideUserWithContentRemovePolicies(): User
    {
        $limitations = [
            new LanguageLimitation(['limitationValues' => ['eng-US']]),
        ];

        return $this->createUserWithPolicies(
                'test',
                [
                    ['module' => 'content', 'function' => 'remove', 'limitations' => $limitations],
                    ['module' => 'content', 'function' => 'versionread'],
                    ['module' => 'content', 'function' => 'read'],
                ]
            );
    }
}
