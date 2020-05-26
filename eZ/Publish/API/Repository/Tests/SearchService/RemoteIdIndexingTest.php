<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class RemoteIdIndexingTest extends BaseTest
{
    /** @var int[] */
    private static $contentIdByRemoteIdIndex = [];

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (empty(self::$contentIdByRemoteIdIndex)) {
            foreach ($this->getRemoteIDs() as $remoteId) {
                self::$contentIdByRemoteIdIndex[$remoteId] = $this->createTestFolder(
                    $remoteId
                );
            }

            $this->refreshSearch($this->getRepository(false));
        }
    }

    /**
     * @dataProvider providerForTestIndexingRemoteId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     */
    public function testIndexingRemoteId(Criterion $criterion): void
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();

        $remoteId = $criterion->value[0];
        if (!isset(self::$contentIdByRemoteIdIndex[$remoteId])) {
            self::fail("Failed to find Content ID for remote ID = {$criterion->value}");
        }

        $contentId = self::$contentIdByRemoteIdIndex[$remoteId];

        // test searching using both Content & Location remote IDs for both Content items
        $query = new Query();
        $query->filter = $criterion;
        $result = $searchService->findContent($query);
        self::assertSame(
            1,
            $result->totalCount,
            "Failed to find Content ID = {$contentId} filtering by remote ID = '{$remoteId}'"
        );
        self::assertSame($contentId, $result->searchHits[0]->valueObject->id);

        $query = new LocationQuery();
        $query->filter = $criterion;
        $result = $searchService->findLocations($query);
        self::assertSame(
            1,
            $result->totalCount,
            "Failed to find Location for Content ID = {$contentId} filtering by remote ID = '{$remoteId}'"
        );
        self::assertSame($contentId, $result->searchHits[0]->valueObject->contentInfo->id);
    }

    private function getRemoteIDs(): array
    {
        return [
            'md5sum' => 'dec23f2de27399a4c0561187b805aa74',
            'sha512' => '3ad11d1424370b5a258f7dc7724b25535f3fb2e1fa3f7047839f68d18cf58eb5',
            'external:10' => 'external:10',
            'external_10' => 'external_10',
            'with space' => 'with space',
            'with national characters' => 'zażółć gęślą jaźń',
            'with emoji' => json_decode('"\ud83d\ude00"'),
        ];
    }

    /**
     * Create 2 * number of remote IDs test data sets (one for Content, another for Location).
     *
     * @return iterable
     */
    public function providerForTestIndexingRemoteId(): iterable
    {
        foreach ($this->getRemoteIDs() as $description => $remoteId) {
            yield "Content remote ID = {$description}" => [new Criterion\RemoteId($remoteId)];
            yield "Location remote ID = {$description}" => [new Criterion\LocationRemoteId($remoteId)];
        }
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function createTestFolder(string $remoteId): int
    {
        $repository = $this->getRepository(false);
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');
        $folderCreateStruct = $contentService->newContentCreateStruct($folderType, 'eng-GB');
        $folderCreateStruct->remoteId = $remoteId;
        $locationCreateStruct = $locationService->newLocationCreateStruct(2);
        $locationCreateStruct->remoteId = $remoteId;
        $folder = $contentService->publishVersion(
            $contentService->createContent(
                $folderCreateStruct,
                [$locationCreateStruct]
            )->getVersionInfo()
        );

        return $folder->id;
    }
}
