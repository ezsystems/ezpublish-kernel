<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;

use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 *
 * Simple value handler is used for creating a filter on a value that makes sense to match on only as a whole.
 * Eg. timestamp, integer, boolean, relation Content id
 */
class Simple extends Handler
{
    public function handle(
        QueryBuilder $outerQuery,
        QueryBuilder $subQuery,
        Criterion $criterion,
        string $column
    ) {
        // For "Simple" FieldTypes, handle the following as equal:
        // - Contains
        // - LIKE when against int column
        if (
            $criterion->operator === Criterion\Operator::CONTAINS ||
            ($criterion->operator === Criterion\Operator::LIKE && $column === 'sort_key_int')
        ) {
            $filter = $subQuery->expr()->eq(
                $column,
                $outerQuery->createNamedParameter($this->lowerCase($criterion->value))
            );
        } else {
            $filter = parent::handle($outerQuery, $subQuery, $criterion, $column);
        }

        return $filter;
    }
}
