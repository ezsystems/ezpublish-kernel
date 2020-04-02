<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue\Handler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\TransformationProcessor;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 *
 * Collection value handler is used for creating a filter on a value that is in fact a collection of values,
 * separated by a character.
 * Eg. list of countries, list of Selection options, list of RelationList Content ids
 */
class Collection extends Handler
{
    /**
     * Character separating indexed values.
     *
     * @var string
     */
    protected $separator;

    public function __construct(
        Connection $connection,
        TransformationProcessor $transformationProcessor,
        string $separator
    ) {
        parent::__construct($connection, $transformationProcessor);

        $this->separator = $separator;
    }

    public function handle(
        QueryBuilder $outerQuery,
        QueryBuilder $subQuery,
        Criterion $criterion,
        string $column
    ) {
        $singleValueExpr = 'eq';

        switch ($criterion->operator) {
            case Criterion\Operator::LIKE:
                // Allow usage of * as wildcards in ::LIKE
                $value = str_replace('*', '%', $this->prepareLikeString($criterion->value));

                $singleValueExpr = 'like';
            // No break here, rest is handled by shared code with ::CONTAINS below

            case Criterion\Operator::CONTAINS:
                $value = isset($value) ? $value : $this->prepareLikeString($criterion->value);
                $quotedColumn = $column;
                $expr = $subQuery->expr();
                $filter = $expr->orX(
                    $expr->$singleValueExpr(
                        $quotedColumn,
                        $outerQuery->createNamedParameter($value, ParameterType::STRING)
                    ),
                    $expr->like(
                        $quotedColumn,
                        $outerQuery->createNamedParameter(
                            '%' . $this->separator . $value,
                            ParameterType::STRING
                        )
                    ),
                    $expr->like(
                        $quotedColumn,
                        $outerQuery->createNamedParameter(
                            $value . $this->separator . '%',
                            ParameterType::STRING
                        )
                    ),
                    $expr->like(
                        $quotedColumn,
                        $outerQuery->createNamedParameter(
                            '%' . $this->separator . $value . $this->separator . '%',
                            ParameterType::STRING
                        )
                    )
                );
                break;

            default:
                $filter = parent::handle($outerQuery, $subQuery, $criterion, $column);
        }

        return $filter;
    }
}
