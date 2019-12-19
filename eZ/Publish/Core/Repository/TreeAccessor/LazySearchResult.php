<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\LocationListFactory;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Repository\TreeAccessor\SearchHitIterator;
use IteratorAggregate;

class LazySearchResult implements IteratorAggregate
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\Values\Content\LocationQuery */
    private $query;

    /** @var array */
    private $languageFilter;

    public function __construct(SearchService $searchService, LocationQuery $query, array $languageFilter = [])
    {
        $this->searchService = $searchService;
        $this->query = $query;
        $this->languageFilter = $languageFilter;
    }

    public function filter(Criterion $criteria): self
    {
        $query = clone $this->query;
        $query->filter = $criteria;

        return new self($this->searchService, $query, $this->languageFilter);
    }

    public function getIterator()
    {
        return new SearchHitIterator($this->searchService->findLocations($this->query, $this->languageFilter));
    }

    public function getTotalCount(): int
    {
        $query = $this->query;
        $query->offset = 0;
        $query->limit = 0;

        return $this->searchService->findLocations($query, $this->languageFilter)->totalCount;
    }
}
