<?php
namespace ezp\PublicAPI\Values\Content;

use ezp\PublicAPI\Value\ValueObject;

/**
 *
 * This class is returnd by find methods providing a result of a search.
 */
class SearchResult extends ValueObject
{
    /**
     * the query this result is based on
     *
     * @var Query
     */
    public $query;

    /**
     * Number of results found by the search
     *
     * @var int
     */
    public $count;

    /**
     * The found items by the search
     *
     * @var array an array of {@link ValueObject}
     */
    public $items = array();
}
