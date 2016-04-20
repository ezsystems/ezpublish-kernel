<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Search\SearchResult class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

/**
 * This class represents a search result.
 */
class SearchResult extends FilterResult
{
    /**
     * The facets for this search.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\Facet[]
     */
    public $facets = array();

    /**
     * If spellcheck is on this field contains a collated query suggestion where in the appropriate
     * criterions the wrong spelled value is replaced by a corrected one (TBD).
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $spellSuggestion;

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
}
