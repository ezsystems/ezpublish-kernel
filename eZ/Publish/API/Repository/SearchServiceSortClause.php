<?php

/**
 * File containing the eZ\Publish\API\Repository\SearchServiceSortClause class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Search service Location sort clause interface.
 *
 * @since 6.7
 * @deprecated in 6.7, will be moved into SearchService in 7.0
 */
interface SearchServiceSortClause
{
    /**
     * Get SortClause objects built from $location's sort options.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\SortClause[]
     */
    public function getSortClauseFromLocation(Location $location);
}
