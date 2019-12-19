<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\TreeAccessor;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Ancestor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\Core\Repository\LocationListFactory\LazySearchResult;

/**
 * @internal
 */
final class TreeDelegate
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

    public function getAncestors(Location $location): iterable
    {
        $query = new LocationQuery();
        $query->filter = new Ancestor($location->pathString);

        return new LazySearchResult($this->searchService, $query, $this->languageFilter);
    }

    public function getSiblings(Location $location): iterable
    {
        $query = new LocationQuery();
        $query->filter = new LogicalAnd([
            new ParentLocationId($location->parentLocationId),
            new LogicalNot(
                new LocationId($location->id)
            ),
        ]);

        return new LazySearchResult($this->searchService, $query, $this->languageFilter);
    }

    public function getChildren(Location $location): iterable
    {
        $query = new LocationQuery();
        $query->filter = new ParentLocationId($location->id);

        return new LazySearchResult($this->searchService, $query, $this->languageFilter);
    }
}
