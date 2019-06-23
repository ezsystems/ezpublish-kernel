<?php

/**
 * File containing the DoctrineDatabase base field value Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator as CriterionOperator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use RuntimeException;

/**
 * Content locator gateway implementation using the DoctrineDatabase.
 */
abstract class Handler
{
    /**
     * DB handler to fetch additional field information.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * Map of criterion operators to the respective function names
     * in the DoctrineDatabase DBAL.
     *
     * @var array
     */
    protected $comparatorMap = [
        CriterionOperator::EQ => 'eq',
        CriterionOperator::GT => 'gt',
        CriterionOperator::GTE => 'gte',
        CriterionOperator::LT => 'lt',
        CriterionOperator::LTE => 'lte',
    ];

    /**
     * Transformation processor.
     *
     * @var \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Creates a new criterion handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\TransformationProcessor $transformationProcessor
     */
    public function __construct(DatabaseHandler $dbHandler, TransformationProcessor $transformationProcessor)
    {
        $this->dbHandler = $dbHandler;
        $this->transformationProcessor = $transformationProcessor;
    }

    /**
     * Generates query expression for operator and value of a Field Criterion.
     *
     * @throws \RuntimeException If operator is not handled.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $column
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(SelectQuery $query, Criterion $criterion, $column)
    {
        $column = $this->dbHandler->quoteColumn($column);

        switch ($criterion->operator) {
            case Criterion\Operator::IN:
                $filter = $query->expr->in(
                    $column,
                    array_map([$this, 'lowerCase'], $criterion->value)
                );
                break;

            case Criterion\Operator::BETWEEN:
                $filter = $query->expr->between(
                    $column,
                    $query->bindValue($this->lowerCase($criterion->value[0])),
                    $query->bindValue($this->lowerCase($criterion->value[1]))
                );
                break;

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $filter = $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue($this->lowerCase($criterion->value))
                );
                break;

            case Criterion\Operator::LIKE:
                if (strpos($criterion->value, '%') !== false) {
                    // @deprecated In 6.13.x/7.3.x and higher, to be removed in 8.0
                    @trigger_error(
                        "Usage of '%' in Operator::LIKE criteria with Legacy Search Engine was never intended, " .
                        "and is deprecated for removal in 8.0. Please use '*' like in FullText, works across engines",
                        E_USER_DEPRECATED
                    );
                    $value = $this->lowerCase($criterion->value);
                } else {
                    $value = str_replace('*', '%', $this->prepareLikeString($criterion->value));
                }

                $filter = $query->expr->like(
                    $column,
                    $query->bindValue($value)
                );
                break;

            case Criterion\Operator::CONTAINS:
                $filter = $query->expr->like(
                    $column,
                    $query->bindValue(
                        '%' . $this->prepareLikeString($criterion->value) . '%'
                    )
                );
                break;

            default:
                throw new RuntimeException("Unknown operator '{$criterion->operator}' for Field criterion handler.");
        }

        return $filter;
    }

    /**
     * Returns the given $string prepared for use in SQL LIKE clause.
     *
     * LIKE clause wildcards '%' and '_' contained in the given $string will be escaped.
     *
     * @param $string
     *
     * @return string
     */
    protected function prepareLikeString($string)
    {
        return addcslashes($this->lowerCase($string), '%_');
    }

    /**
     * Downcases a given string using string transformation processor.
     *
     * @param string $string
     *
     * @return string
     */
    protected function lowerCase($string)
    {
        return $this->transformationProcessor->transformByGroup($string, 'lowercase');
    }
}
