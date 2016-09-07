<?php

/**
 * File containing the DoctrineDatabase base field value Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler\FieldValue;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator as CriterionOperator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use PDO;
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
     */
    protected $dbHandler;

    /**
     * Map of criterion operators to the respective function names
     * in the DoctrineDatabase DBAL.
     *
     * @var array
     */
    protected $comparatorMap = array(
        CriterionOperator::EQ => 'eq',
        CriterionOperator::GT => 'gt',
        CriterionOperator::GTE => 'gte',
        CriterionOperator::LT => 'lt',
        CriterionOperator::LTE => 'lte',
        CriterionOperator::LIKE => 'like',
    );

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
                if (!is_string($criterion->value)) {
                    throw new \Exception('Value must be sting for in operator');
                }
                $filter = $query->expr->in(
                    $column,
                    array_map(array($this, 'lowerCase'), $criterion->value)
                );
                break;

            case Criterion\Operator::BETWEEN:
                $filter = $query->expr->between(
                    $column,
                    $this->bindValue($query, $criterion->value[0]),
                    $this->bindValue($query, $criterion->value[1])
                );
                break;

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
            case Criterion\Operator::LIKE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                $filter = $query->expr->$operatorFunction(
                    $column,
                    $this->bindValue($query, $criterion->value)
                );
                break;

            case Criterion\Operator::CONTAINS:
                if (!is_string($criterion->value)) {
                    throw new \Exception('Value must be sting for contains operator');
                }
                $filter = $query->expr->like(
                    $column,
                    $query->bindValue('%' . $this->lowercase($criterion->value) . '%')
                );
                break;

            default:
                throw new RuntimeException("Unknown operator '{$criterion->operator}' for Field criterion handler.");
        }

        return $filter;
    }

    /**
     * Binds vale a given value using the appropriate type.
     *
     * @param mixed $value
     * @return string the placeholder name used.
     */
    protected function bindValue($query, $value)
    {
        if ($value instanceof \DateTime) {
            return $query->bindValue($value->getTimestamp(), null, PDO::PARAM_INT);
        }

        return $query->bindValue($this->lowerCase(value));
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
