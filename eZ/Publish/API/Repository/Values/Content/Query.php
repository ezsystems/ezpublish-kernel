<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content;

/**
 * This class is used to perform a Content query.
 */
class Query extends Filter
{
    /**
     * The query.
     *
     * For the storage backend that supports it (Solr Search Engine) query will influence
     * score of the search results.
     *
     * Can contain multiple criterion, as items of a logical one (by default
     * AND). Defaults to MatchAll.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public $query;

    /**
     * An array of facet builders.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder[]
     */
    public $facetBuilders = array();

    /**
     * If true spellcheck suggestions are returned.
     *
     * @var bool
     */
    public $spellcheck;
}
