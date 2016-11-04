<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\Priority class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;

/**
 * Sets sort direction on the Location priority for a Location query.
 */
class Priority extends Location
{
    /**
     * Constructs a new LocationPriority SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('location_priority', $sortDirection);
    }
}
