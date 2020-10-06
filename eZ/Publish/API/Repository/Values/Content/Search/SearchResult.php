<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Search;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;
use IteratorAggregate;
use Traversable;

/**
 * This class represents a search result.
 */
class SearchResult extends ValueObject implements IteratorAggregate
{
    /**
     * The facets for this search.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     *
     * @deprecated since eZ Platform 3.2.0, to be removed in eZ Platform 4.0.0.
     */
    public $facets = [];

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Search\AggregationResultCollection
     */
    public $aggregations;

    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]
     */
    public $searchHits = [];

    /**
     * If spellcheck is on this field contains a collated query suggestion where in the appropriate
     * criterions the wrong spelled value is replaced by a corrected one (TBD).
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $spellSuggestion;

    /**
     * The duration of the search processing in ms.
     *
     * @var int
     */
    public $time;

    /**
     * Indicates if the search has timed out.
     *
     * @var bool
     */
    public $timedOut;

    /**
     * The maximum score of this query.
     *
     * @var float
     */
    public $maxScore;

    /**
     * The total number of searchHits.
     *
     * `null` if Query->performCount was set to false and search engine avoids search lookup.
     *
     * @var int|null
     */
    public $totalCount;

    public function __construct(array $properties = [])
    {
        if (!isset($properties['aggregations'])) {
            $properties['aggregations'] = new AggregationResultCollection();
        }

        parent::__construct($properties);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->searchHits);
    }
}
