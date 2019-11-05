<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\FieldType;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\Query;
use EzSystems\EzPlatformSolrSearchEngine\Tests\SetupFactory\LegacySetupFactory as LegacySolrSetupFactory;

final class SearchableUserTest extends BaseTest
{
    private $searchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = $this->getRepository()->getSearchService();
    }

    /**
     * @dataProvider providerForTestFindUserByEmail
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \ErrorException
     */
    public function testFindUserByEmail(string $email, string $searchInput): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        // create extra data (not to be found) to check system sanity
        if ($this->getSetupFactory() instanceof LegacySolrSetupFactory) {
            // exact match (see the data provider) is supported by Solr only
            $this->createUser('jane', 'Jane', 'Smith', null, 'jane@subdomain.example.com');
        } else {
            $this->createUser('jane', 'Jane', 'Smith', null, 'jane@anything.org');
        }

        // create the actual user to be found
        $user = $this->createUser('john', 'John', 'Doe', null, $email);

        $this->refreshSearch($repository);

        $userContent = $contentService->loadContent($user->id);
        $query = new Query();
        $query->query = new Query\Criterion\FullText($searchInput);

        $results = $this->searchService->findContent($query);
        self::assertEquals(1, $results->totalCount);
        self::assertEquals($userContent, $results->searchHits[0]->valueObject);
    }

    public function providerForTestFindUserByEmail(): array
    {
        return [
            ['john@example.com', '"john@example.com"'],
            ['john@example.com', '"@example.com"'],
        ];
    }
}
