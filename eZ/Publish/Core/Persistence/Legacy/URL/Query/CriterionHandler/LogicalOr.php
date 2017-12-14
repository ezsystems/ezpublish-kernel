<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class LogicalOr implements CriterionHandler
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion)
    {
        $subexpressions = [];
        foreach ($criterion->criteria as $subCriterion) {
            $subexpressions[] = $converter->convertCriteria($query, $subCriterion);
        }

        return $query->expr->lOr($subexpressions);
    }
}
