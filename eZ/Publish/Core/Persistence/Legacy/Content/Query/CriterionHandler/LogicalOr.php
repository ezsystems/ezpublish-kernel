<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;
use Doctrine\DBAL\Query\QueryBuilder;

class LogicalOr implements CriterionHandler
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, QueryBuilder $query, Criterion $criterion)
    {
        $subexpressions = [];
        /** @var Criterion\LogicalOperator $criterion */
        foreach ($criterion->criteria as $subCriterion) {
            $subexpressions[] = $converter->convertCriteria($query, $subCriterion);
        }

        return $query->expr()->orX(...$subexpressions);
    }
}
