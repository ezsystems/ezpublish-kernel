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

class MatchAll implements CriterionHandler
{
    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\MatchAll;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, QueryBuilder $query, Criterion $criterion)
    {
        return '1';
    }
}
