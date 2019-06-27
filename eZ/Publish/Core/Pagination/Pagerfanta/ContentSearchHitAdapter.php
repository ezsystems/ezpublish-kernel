<?php

/**
 * File containing the ContentSearchHitAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Pagination\Pagerfanta;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\SearchService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * Pagerfanta adapter for eZ Publish content search.
 * Will return results as SearchHit objects.
 */
class ContentSearchHitAdapter implements AdapterInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Query */
    private $query;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var int */
    private $nbResults;

    public function __construct(Query $query, SearchService $searchService)
    {
        $this->query = $query;
        $this->searchService = $searchService;
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

        return $this->nbResults = $this->searchService->findContent($countQuery)->totalCount;
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

        $searchResult = $this->searchService->findContent($query);

        // Set count for further use if returned by search engine despite !performCount (Solr, ES)
        if (!isset($this->nbResults) && isset($searchResult->totalCount)) {
            $this->nbResults = $searchResult->totalCount;
        }

        return $searchResult->searchHits;
    }
}
