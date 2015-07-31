<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Aggregate of a Query and its SearchResults.
 */
class QueryTypeResult extends ValueObject
{
    /** @var Query The Query that was executed*/
    protected $query;

    /** @var SearchResult The QueryType search results*/
    protected $searchResult;

    /** @var string The name of the QueryType that was ran */
    protected $queryTypeName;

    /** @var array The parameters used to run the QueryType */
    protected $parameters;
}
