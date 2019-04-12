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
    public function getDriverName(): string
    {
        return 'pgsql';
    }

    public function getRandomFunctionName($seed): string
    {
        return 'random()';
    }

    public function applySelect(SelectQuery $query, SortClause $sortClause, $number)
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\RandomTarget $sortClause->targetData */

        $query
            ->select(
                $this->dbHandler->quoteColumn('SETSEED(' . $sortClause->targetData->seed . ')')
            );

        return parent::applySelect($query, $sortClause, $number);
    }
}
