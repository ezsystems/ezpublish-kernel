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
    public function applyJoin(
        SelectQuery $query,
        SortClause $sortClause,
        $number,
        array $languageSettings
    ) {
        $seedAsInt = $sortClause->targetData->seed;
        if (!$seedAsInt) {
            return;
        }

        $seed = $this->interpolateIntegerToFloatRange($seedAsInt);
        $query
            ->innerJoin(
                '(select setseed(:seed)) as seed',
                0,
                0
            )
            ->bindParam($seed, ':seed');
    }

    public function getDriverName(): string
    {
        return 'postgresql';
    }

    public function getRandomFunctionName(?int $seed): string
    {
        return 'random()';
    }

    /**
     * Take into consideration that little seed variations can output basically that same number when interpolated to [-1,1] range.
     */
    private function interpolateIntegerToFloatRange(int $seedAsInt): float
    {
        $minFloat = -1;
        $maxFloat = 1;

        return $minFloat + ($seedAsInt - PHP_INT_MIN) * ($maxFloat - $minFloat) / (PHP_INT_MAX - PHP_INT_MIN);
    }
}
