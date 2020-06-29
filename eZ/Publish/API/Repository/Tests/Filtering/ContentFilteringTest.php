<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Filtering;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentList;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\Core\FieldType\Keyword;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringSortClause;
use IteratorAggregate;
use function array_map;
use function count;
use function sprintf;

/**
 * @internal
 */
final class ContentFilteringTest extends BaseRepositoryFilteringTestCase
{
    /**
     * Test that special cases of Location Sort Clauses are working correctly.
     *
     * Content can have multiple Locations, so we need to check if the list of results
     * doesn't contain duplicates.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindWithLocationSortClauses(): void
    {
        $repository = $this->getRepository(false);
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        $this->contentProvider->createSharedContentStructure();

        // sanity check
        $locations = $locationService->loadLocations(
            $contentService->loadContentInfoByRemoteId(
                TestContentProvider::CONTENT_REMOTE_IDS['folder2']
            )
        );
        self::assertCount(2, $locations);
        [$location1, $location2] = $locations;
        self::assertNotEquals($location1->depth, $location2->depth);

        $sortClause = new SortClause\Location\Depth(Query::SORT_ASC);

        $filter = new Filter();
        $filter
            ->withCriterion(
                new Criterion\RemoteId(TestContentProvider::CONTENT_REMOTE_IDS['folder2'])
            )
            ->orWithCriterion(
                new Criterion\RemoteId(TestContentProvider::CONTENT_REMOTE_IDS['folder1'])
            )
            ->withSortClause(new SortClause\ContentName(Query::SORT_ASC))
            ->withSortClause($sortClause);

        $contentList = $contentService->find($filter);
        $this->assertFoundContentItemsByRemoteIds(
            $contentList,
            [
                TestContentProvider::CONTENT_REMOTE_IDS['folder1'],
                TestContentProvider::CONTENT_REMOTE_IDS['folder2'],
            ]
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \Exception
     */
    protected function compareWithSearchResults(Filter $filter, IteratorAggregate $filteredContentList): void
    {
        $query = $this->buildSearchQueryFromFilter($filter);
        $contentListFromSearch = $this->findUsingContentSearch($query);
        self::assertCount($contentListFromSearch->getTotalCount(), $filteredContentList);
        $filteredContentListIterator = $filteredContentList->getIterator();
        foreach ($contentListFromSearch as $pos => $expectedContentItem) {
            $this->assertContentItemEquals(
                $expectedContentItem,
                $filteredContentListIterator->offsetGet($pos),
                "Content items at the position {$pos} are not the same"
            );
        }
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function findUsingContentSearch(Query $query): ContentList
    {
        $repository = $this->getRepository(false);
        $searchService = $repository->getSearchService();
        $searchResults = $searchService->findContent($query);

        return new ContentList(
            $searchResults->totalCount,
            array_map(
                static function (SearchHit $searchHit) {
                    return $searchHit->valueObject;
                },
                $searchResults->searchHits
            )
        );
    }

    protected function getDefaultSortClause(): FilteringSortClause
    {
        return new SortClause\ContentId();
    }

    public function getFilterFactories(): iterable
    {
        yield from parent::getFilterFactories();

        yield 'Content remote ID for an item without any Location' => [
            static function (Content $parentFolder): Filter {
                return (new Filter())
                    ->withCriterion(
                        new Criterion\RemoteId(TestContentProvider::CONTENT_REMOTE_IDS['no-location'])
                    );
            },
            // expected total count
            1,
        ];
    }

    /**
     * Create Folder and sub-folders matching expected paginator page size (creates `$pageSize` * `$noOfPages` items).
     *
     * @param int $pageSize
     * @param int $noOfPages
     *
     * @return int parent Folder Location ID
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function createMultiplePagesOfContentItems(int $pageSize, int $noOfPages): int
    {
        $parentFolder = $this->createFolder(['eng-GB' => 'Parent Folder'], 2);
        $parentFolderMainLocationId = $parentFolder->contentInfo->mainLocationId;

        $noOfItems = $pageSize * $noOfPages;
        for ($itemNo = 1; $itemNo <= $noOfItems; ++$itemNo) {
            $this->createFolder(['eng-GB' => "Child no #{$itemNo}"], $parentFolderMainLocationId);
        }

        return $parentFolderMainLocationId;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPagination(): void
    {
        $pageSize = 10;
        $noOfPages = 4;
        $parentLocationId = $this->createMultiplePagesOfContentItems($pageSize, $noOfPages);

        $collectedContentIDs = [];
        $filter = new Filter(new Criterion\ParentLocationId($parentLocationId));
        for ($offset = 0; $offset < $noOfPages; $offset += $pageSize) {
            $filter->sliceBy($pageSize, 0);
            $contentList = $this->find($filter);

            // a total count reflects a total number of items, not a number of items on a current page
            self::assertSame($pageSize * $noOfPages, $contentList->getTotalCount());

            // an actual number of items on a current page
            self::assertCount($pageSize, $contentList);

            // check if results are not duplicated across multiple pages
            foreach ($contentList as $contentItem) {
                self::assertNotContains(
                    $contentItem->id,
                    $collectedContentIDs,
                    "Content item ID={$contentItem->id} exists on multiple pages"
                );
                $collectedContentIDs[] = $contentItem->id;
            }
        }
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindContentWithExternalStorageFields(): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();

        $blogType = $contentTypeService->loadContentTypeByIdentifier('blog');
        $contentCreate = $contentService->newContentCreateStruct($blogType, 'eng-GB');
        $contentCreate->setField('name', 'British Blog');
        $contentCreate->setField('tags', new Keyword\Value(['British', 'posts']));
        $contentDraft = $contentService->createContent($contentCreate);
        $contentService->publishVersion($contentDraft->getVersionInfo());

        $filter = new Filter(new Criterion\ContentTypeIdentifier('blog'));
        $contentList = $this->find($filter, []);

        self::assertSame(1, $contentList->getTotalCount());
        self::assertCount(1, $contentList);

        foreach ($contentList as $content) {
            $legacyLoadedContent = $contentService->loadContent($content->id, []);
            self::assertEquals($legacyLoadedContent, $content);
        }
    }

    /**
     * @dataProvider getDataForTestFindContentWithLocationCriterion
     *
     * @param string[] $expectedContentRemoteIds
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testFindContentUsingLocationCriterion(
        callable $filterFactory,
        array $expectedContentRemoteIds
    ): void {
        $parentFolder = $this->contentProvider->createSharedContentStructure();
        $filter = $this->buildFilter($filterFactory, $parentFolder);
        $this->assertFoundContentItemsByRemoteIds(
            $this->find($filter, []),
            $expectedContentRemoteIds
        );
    }

    public function getDataForTestFindContentWithLocationCriterion(): iterable
    {
        yield 'Content items with secondary Location, sorted by Content ID' => [
            static function (Content $parentFolder): Filter {
                return (new Filter())
                    ->withCriterion(
                        new Criterion\Location\IsMainLocation(
                            Criterion\Location\IsMainLocation::NOT_MAIN
                        )
                    )
                    ->withSortClause(new SortClause\ContentId(Query::SORT_ASC));
            },
            [TestContentProvider::CONTENT_REMOTE_IDS['folder2']],
        ];

        yield 'Folders with Location, sorted by Content ID' => [
            static function (Content $parentFolder): Filter {
                return (new Filter())
                    ->withCriterion(
                        new Criterion\Location\IsMainLocation(
                            Criterion\Location\IsMainLocation::MAIN
                        )
                    )
                    ->andWithCriterion(
                        new Criterion\ParentLocationId($parentFolder->contentInfo->mainLocationId)
                    )
                    ->andWithCriterion(
                        new Criterion\ContentTypeIdentifier('folder')
                    )
                    ->withSortClause(new SortClause\ContentId(Query::SORT_ASC));
            },
            [
                TestContentProvider::CONTENT_REMOTE_IDS['folder1'],
                TestContentProvider::CONTENT_REMOTE_IDS['folder2'],
            ],
        ];
    }

    protected function assertFoundContentItemsByRemoteIds(
        iterable $list,
        array $expectedContentRemoteIds
    ): void {
        self::assertCount(count($expectedContentRemoteIds), $list);
        foreach ($list as $content) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
            self::assertContainsEquals(
                $content->contentInfo->remoteId,
                $expectedContentRemoteIds,
                sprintf(
                    'Content %d (%s) was not supposed to be found',
                    $content->id,
                    $content->contentInfo->remoteId
                )
            );
        }
    }

    /**
     * @dataProvider getListOfSupportedSortClauses
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testFindWithSortClauses(string $sortClauseFQCN): void
    {
        $this->performAndAssertSimpleSortClauseQuery(new $sortClauseFQCN(Query::SORT_ASC));
        $this->performAndAssertSimpleSortClauseQuery(new $sortClauseFQCN(Query::SORT_DESC));
    }

    /**
     * Simple test to check each sort clause validity on a database integration level.
     *
     * Note: It should be expanded in the future to check validity of the sorting logic itself
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    private function performAndAssertSimpleSortClauseQuery(FilteringSortClause $sortClause): void
    {
        $filter = new Filter(new Criterion\ContentId(57), [$sortClause]);
        $contentList = $this->find($filter, []);
        self::assertCount(1, $contentList);
        self::assertSame(57, $contentList->getIterator()[0]->id);
    }

    public function getListOfSupportedSortClauses(): iterable
    {
        yield 'Content\\Id' => [SortClause\ContentId::class];
        yield 'ContentName' => [SortClause\ContentName::class];
        yield 'DateModified' => [SortClause\DateModified::class];
        yield 'DatePublished' => [SortClause\DatePublished::class];
        yield 'SectionIdentifier' => [SortClause\SectionIdentifier::class];
        yield 'SectionName' => [SortClause\SectionName::class];
        yield 'Location\\Depth' => [SortClause\Location\Depth::class];
        yield 'Location\\Id' => [SortClause\Location\Id::class];
        yield 'Location\\Path' => [SortClause\Location\Path::class];
        yield 'Location\\Priority' => [SortClause\Location\Priority::class];
        yield 'Location\\Visibility' => [SortClause\Location\Visibility::class];
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\ContentList
     */
    protected function find(Filter $filter, ?array $contextLanguages = null): iterable
    {
        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();

        return $contentService->find($filter, $contextLanguages);
    }

    private function buildSearchQueryFromFilter(Filter $filter): Query
    {
        $limit = $filter->getLimit();

        return new Query(
            [
                'filter' => $filter->getCriterion(),
                'sortClauses' => $filter->getSortClauses(),
                'offset' => $filter->getOffset(),
                'limit' => $limit > 0 ? $limit : 999,
            ]
        );
    }
}
