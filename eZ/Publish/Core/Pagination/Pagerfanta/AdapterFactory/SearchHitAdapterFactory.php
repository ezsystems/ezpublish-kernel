<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Pagination\Pagerfanta\AdapterFactory;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchHitAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\FixedAdapter;

/**
 * @internal
 */
final class SearchHitAdapterFactory implements SearchHitAdapterFactoryInterface
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function createAdapter(Query $query, array $languageFilter = []): AdapterInterface
    {
        if ($query instanceof LocationQuery) {
            return new LocationSearchHitAdapter($query, $this->searchService, $languageFilter);
        }

        return new ContentSearchHitAdapter($query, $this->searchService, $languageFilter);
    }

    public function createFixedAdapter(Query $query, array $languageFilter = []): AdapterInterface
    {
        if ($query instanceof LocationQuery) {
            $searchResults = $this->searchService->findLocations($query, $languageFilter);
        } else {
            $searchResults = $this->searchService->findContent($query, $languageFilter);
        }

        return new FixedAdapter(
            $searchResults->totalCount,
            $searchResults->searchHits
        );
    }
}
