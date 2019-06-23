<?php

/**
 * File containing the Content Search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
abstract class Gateway
{
    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param array $languageFilter
     * @param bool $doCount
     *
     * @return mixed[][]
     */
    abstract public function find(
        Criterion $criterion,
        $offset,
        $limit,
        array $sort = null,
        array $languageFilter = [],
        $doCount = true
    );
}
