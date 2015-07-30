<?php

/**
 * File containing the DoctrineDatabase Simple field value handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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
        switch ($criterion->operator) {
            case Criterion\Operator::CONTAINS:
                $filter = $query->expr->eq(
                    $this->dbHandler->quoteColumn($column),
                    $query->bindValue($this->lowerCase($criterion->value))
                );
                break;

            default:
                $filter = parent::handle($query, $criterion, $column);
        }

        return $filter;
    }
}
