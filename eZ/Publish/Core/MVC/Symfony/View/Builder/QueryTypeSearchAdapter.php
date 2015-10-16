<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use Closure;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * A QueryType PagerFanta adapter that uses a callback to the search service.
 */
class QueryTypeSearchAdapter implements AdapterInterface
{
    /**
     * The closure that runs the search.
     * @var Closure
     */
    private $searchClosure;

    /**
     * The search query.
     * @var \eZ\Publish\Core\Persistence\Database\Query
     */
    private $query;

    public function __construct(Query $query, Closure $searchClosure)
    {
        $this->searchClosure = $searchClosure;
        $this->query = $query;
    }

    public function getNbResults()
    {
        if (isset($this->nbResults)) {
            return $this->nbResults;
        }

        $countQuery = clone $this->query;
        $countQuery->limit = 0;

        return $this->nbResults = $this->runQuery($countQuery)->totalCount;
    }

    public function getSlice($offset, $length)
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->runQuery($query);

        // Set count for further use if returned by search engine despite !performCount (Solr, ES)
        if (!isset($this->nbResults) && isset($searchResult->totalCount)) {
            $this->nbResults = $searchResult->totalCount;
        }

        $results = [];
        foreach ($searchResult->searchHits as $searchHit) {
            $results[] = $searchHit->valueObject;
        }

        return $results;
    }

    /**
     * @return SearchResult
     */
    private function runQuery($query)
    {
        $callback = $this->searchClosure;

        return $callback($query);
    }
}
