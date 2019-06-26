<?php

/**
 * File containing the LocationSearchHitAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\SearchService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for eZ Publish location search.
 * Will return results as SearchHit objects.
 */
class LocationSearchHitAdapter implements AdapterInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\LocationQuery */
    private $query;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var array */
    private $languageFilter;

    /** @var int */
    private $nbResults;

    public function __construct(LocationQuery $query, SearchService $searchService, array $languageFilter = [])
    {
        $this->query = $query;
        $this->searchService = $searchService;
        $this->languageFilter = $languageFilter;
    }

    /**
     * Returns the number of results.
     *
     * @return int The number of results.
     */
    public function getNbResults()
    {
        if (isset($this->nbResults)) {
            return $this->nbResults;
        }

        $countQuery = clone $this->query;
        $countQuery->limit = 0;

        return $this->nbResults = $this->searchService->findLocations($countQuery)->totalCount;
    }

    /**
     * Returns a slice of the results, as SearchHit objects.
     *
     * @param int $offset The offset.
     * @param int $length The length.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]
     */
    public function getSlice($offset, $length)
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->searchService->findLocations($query, $this->languageFilter);

        // Set count for further use if returned by search engine despite !performCount (Solr, ES)
        if (!isset($this->nbResults) && isset($searchResult->totalCount)) {
            $this->nbResults = $searchResult->totalCount;
        }

        return $searchResult->searchHits;
    }
}
