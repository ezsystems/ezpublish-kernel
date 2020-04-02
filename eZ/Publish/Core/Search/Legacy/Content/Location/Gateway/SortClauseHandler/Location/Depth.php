<?php

/**
 * File containing a DoctrineDatabase Location depth sort clause handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway\SortClauseHandler\Location;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
class Depth extends SortClauseHandler
{
    /**
     * Check if this sort clause handler accepts to handle the given sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function accept(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Location\Depth;
    }

    public function applySelect(
        QueryBuilder $query, SortClause $sortClause,
        int $number
    ): array {
        $query
            ->addSelect(
                sprintf('t.depth AS %s', $column = $this->getSortColumnName($number))
            );

        return [$column];
    }
}
