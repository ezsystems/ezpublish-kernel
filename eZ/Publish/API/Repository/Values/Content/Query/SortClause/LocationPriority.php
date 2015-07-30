<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPriority class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on the location priority date for a content query.
 *
 * @deprecated Since 5.3, use Location search instead
 */
class LocationPriority extends SortClause
{
    /**
     * Constructs a new LocationPriority SortClause.
     *
     * @param string $sortDirection
     *
     * @deprecated Since 5.3, use Location search instead
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('location_priority', $sortDirection);
    }
}
