<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\LocationListFactory;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;

abstract class AbstractLazySearchQuery
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var array */
    private $languageFilter;

    public function __construct(SearchService $searchService, array $prioritizedLanguages = Language::ALL)
    {
        $this->searchService = $searchService;
        $this->languageFilter =             [
            'prioritizedLanguages' => $prioritizedLanguages
        ];
    }

    public function getLocationList(
        Location $location,
        int $offset = 0,
        int $limit = 25,
        ?callable $callable = null
    ): LazySearchResult {
        $query = $this->createQuery($location);
        if ($callable !== null) {
            $callable($query);
        }

        $query->offset = $offset;
        $query->limit = $limit;

        return new LazySearchResult($this->searchService, $query, $this->languageFilter);
    }

    protected abstract function createQuery(Location $location): LocationQuery;
}
