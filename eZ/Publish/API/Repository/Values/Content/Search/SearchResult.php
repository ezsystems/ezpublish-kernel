<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\SearchResult class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a search result.
 */
class SearchResult extends ValueObject
{
    /**
     * The facets for this search.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    public $facets = [];

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
}
