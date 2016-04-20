<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a Content filter.
 */
class Filter extends ValueObject
{
    const SORT_ASC = 'ascending';

    const SORT_DESC = 'descending';

    /**
     * The filter.
     *
     * For the storage backend that supports it (Solr) filters the result set
     * without influencing score. It also offers better performance as filter
     * part of the Query can be cached.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND)
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $filter;

    /**
     * Query sorting clauses.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public $sortClauses = [];

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
     * If true, search engine should perform count even if that means extra lookup.
     *
     * @var bool
     */
    public $performCount = true;
}
