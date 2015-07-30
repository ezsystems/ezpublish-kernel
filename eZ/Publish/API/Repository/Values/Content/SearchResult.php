<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\SearchResult class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * @deprecated
 * This class is returned by find methods providing a result of a search.
 */
class SearchResult extends ValueObject
{
    /**
     * the query this result is based on.
     *
     * @var Query
     */
    public $query;

    /**
     * Number of results found by the search.
     *
     * @var int
     */
    public $count;

    /**
     * The found items by the search.
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject[]
     */
    public $items = array();
}
