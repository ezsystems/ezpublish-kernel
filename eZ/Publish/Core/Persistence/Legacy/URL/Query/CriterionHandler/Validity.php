<?php

namespace eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\URL\Query\CriterionHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

class Validity implements CriterionHandler
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\Validity;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, SelectQuery $query, Criterion $criterion)
    {
        /** @var Criterion\Validity $criterion */
        return $query->expr->eq(
            'is_valid',
            $query->bindValue($criterion->isValid)
        );
    }
}
