<?php

/**
 * File containing a SearchResult class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Search\SearchResult;

/**
 * This class represents a search result.
 *
 * @deprecated As of 6.0.0, for 5.x compatibility with items property
 */
class TrashedSearchResult extends LocationSearchResult
{
    /**
     * The found items by the search.
     *
     * @deprecated As of 6.0.0, for 5.x compatibility
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem[]
     */
    public $items = array();
}
