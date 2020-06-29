<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Trash;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * @internal
 */
final class DateTrashed extends SortClauseHandler
{
    public function accept(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\Trash\DateTrashed;
    }

    public function applySelect(
        QueryBuilder $query,
        SortClause $sortClause,
        int $number
    ): array {
        $query
            ->addSelect(
                sprintf(
                    't.trashed AS %s',
                    $column = $this->getSortColumnName($number)
                )
            );

        return (array)$column;
    }
}
