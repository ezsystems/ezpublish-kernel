<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location\DateTrashed class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location;

/**
 * Sets sort direction on the trashed date for Trash query.
 */
class DateTrashed extends Location
{
    /**
     * Constructs a new DateTrashed SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_DESC)
    {
        parent::__construct('trashed', $sortDirection);
    }
}
