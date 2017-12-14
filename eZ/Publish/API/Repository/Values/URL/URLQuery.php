<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\URL;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to perform a URL query.
 */
class URLQuery extends ValueObject
{
    /**
     * The Query filter.
     *
     * @var \eZ\Publish\API\Repository\Values\URL\Query\Criterion
     */
    public $filter;

    /**
     * Query sorting clauses.
     *
     * @var \eZ\Publish\API\Repository\Values\URL\Query\SortClause[]
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
}
