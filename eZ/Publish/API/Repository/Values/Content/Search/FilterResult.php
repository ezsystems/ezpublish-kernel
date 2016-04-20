<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Search;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a filter result.
 */
class FilterResult extends ValueObject
{
    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Search\SearchHit[]
     */
    public $searchHits = [];

    /**
     * The duration of the search processing in ms.
     *
     * @var int
     */
    public $time;

    /**
     * The total number of searchHits.
     *
     * `null` if Filter->performCount was set to false and search engine avoids search lookup.
     *
     * @var int|null
     */
    public $totalCount;
}
