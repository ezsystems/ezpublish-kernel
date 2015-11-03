<?php

/**
 * File containing a SearchResult class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

/**
 * This class represents a search result.
 */
class ContentSearchResult extends SearchResult
{
    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit\ContentSearchHit[]
     */
    public $searchHits = array();
}
