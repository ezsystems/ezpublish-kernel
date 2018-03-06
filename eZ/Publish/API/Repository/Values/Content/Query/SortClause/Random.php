<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Random class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\RandomTarget;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort random on a content query.
 */
class Random extends SortClause
{
    /**
     * Constructs a new Random SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($seed, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('random', $sortDirection, new RandomTarget($seed));
    }
}
