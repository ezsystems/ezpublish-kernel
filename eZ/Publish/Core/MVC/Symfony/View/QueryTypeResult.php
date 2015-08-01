<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Aggregate of a Query and its SearchResults
 *
 * @property-read Query $query
 * @property-read SearchResult $searchResult
 * @property-read string $queryTypeName
 * @property-read array $parameters
 */
class QueryTypeResult extends ValueObject
{
    /**
     * The Query that was executed
     * @var Query
     */
    protected $query;

    /**
     * The QueryType search results
     * @var SearchResult
     */
    protected $searchResult;

    /**
     * The name of the QueryType that was ran
     * @var string
     */
    protected $queryTypeName;

    /**
     * The parameters used to run the QueryType
     * @var array
     */
    protected $parameters;
}
