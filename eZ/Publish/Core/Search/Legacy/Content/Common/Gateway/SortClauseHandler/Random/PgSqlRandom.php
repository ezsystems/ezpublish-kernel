<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Random;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom;

class PgSqlRandom extends AbstractRandom
{
    /**
     * @param int $number
     *
     * @return string
     */
    public function applySelect(SelectQuery $query, SortClause $sortClause, $number)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\RandomTarget $sortClause->targetData */
        $query
            ->select(
                $query->alias(
                    $this->getRandomFunctionName($sortClause->targetData->seed),
                    $column = $this->getSortColumnName($number)
                )
            );

        return $column;
    }

    public function getDriverName(): string
    {
        return 'postgresql';
    }

    public function getRandomFunctionName(?int $seed): string
    {
        return 'random()';
    }
}
