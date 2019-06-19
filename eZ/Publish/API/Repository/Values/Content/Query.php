<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a Content query.
 */
class Query extends ValueObject
{
    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';

    /**
     * The Query filter.
     *
     * For the storage backend that supports it (Solr) filters the result set
     * without influencing score. It also offers better performance as filter
     * part of the Query can be cached.
     *
     * In case when the backend does not distinguish between query and filter
     * (Legacy Storage implementation), it will simply be combined with Query query
     * using LogicalAnd criterion.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND)
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter;

    /**
     * The Query query.
     *
     * For the storage backend that supports it (Solr Storage) query will influence
     * score of the search results.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND). Defaults to MatchAll.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $query;

    /**
     * Query sorting clauses.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public $sortClauses = [];

    /**
     * An array of facet builders.
     *
     * Search engines may ignore any, or given facet builders they don't support and will just return search result
     * facets supported by the engine. API consumer should dynamically iterate over returned facets for further use.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    public $facetBuilders = [];

    /**
     * Query offset.
     *
     * Sets the offset for search hits, used for paging the results.
     *
     * @var int
     */
    public $offset = 0;

    /**
     * Query limit.
     *
     * Limit for number of search hits to return.
     * If value is `0`, search query will not return any search hits, useful for doing a count.
     *
     * @var int
     */
    public $limit = 25;

    /**
     * If true spellcheck suggestions are returned.
     *
     * @var bool
     */
    public $spellcheck;

    /**
     * If true, search engine should perform count even if that means extra lookup.
     *
     * @var bool
     */
    public $performCount = true;
}
