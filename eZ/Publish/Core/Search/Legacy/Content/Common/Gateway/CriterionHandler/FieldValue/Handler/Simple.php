<?php

/**
 * File containing the DoctrineDatabase Simple field value handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 *
 * Simple value handler is used for creating a filter on a value that makes sense to match on only as a whole.
 * Eg. timestamp, integer, boolean, relation Content id
 */
class Simple extends Handler
{
    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(SelectQuery $query, Criterion $criterion, $column)
    {
        // For "Simple" FieldTypes, handle the following as equal:
        // - Contains
        // - LIKE when against int column
        if ($criterion->operator === Criterion\Operator::CONTAINS ||
            ($criterion->operator === Criterion\Operator::LIKE && $column === 'sort_key_int')) {
            $filter = $query->expr->eq(
                $this->dbHandler->quoteColumn($column),
                $query->bindValue($this->lowerCase($criterion->value))
            );
        } else {
            $filter = parent::handle($query, $criterion, $column);
        }

        return $filter;
    }
}
