<?php

/**
 * File containing the Search Location Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Base class for location search gateways.
 */
abstract class Gateway
{
    /**
     * Returns total count and data for all Locations satisfying the parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param array $languageFilter
     * @param bool $doCount
     *
     * @return mixed[][]
     */
    abstract public function find(
        Criterion $criterion,
        $offset,
        $limit,
        array $sortClauses = null,
        array $languageFilter = [],
        $doCount = true
    );
}
