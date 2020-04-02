<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * Date metadata criterion handler.
 */
class DateMetadata extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\DateMetadata;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $column = $criterion->target === Criterion\DateMetadata::MODIFIED ? 'modified' : 'published';
        $column = "c.{$column}";

        $value = (array)$criterion->value;
        switch ($criterion->operator) {
            case Criterion\Operator::IN:
                return $queryBuilder->expr()->in(
                    $column,
                    $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
                );

            case Criterion\Operator::BETWEEN:
                return $this->dbPlatform->getBetweenExpression(
                    $column,
                    $queryBuilder->createNamedParameter($value[0], ParameterType::INTEGER),
                    $queryBuilder->createNamedParameter($value[1], ParameterType::INTEGER)
                );

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];

                return $queryBuilder->expr()->$operatorFunction(
                    $column,
                    $queryBuilder->createNamedParameter(reset($value), ParameterType::INTEGER)
                );

            default:
                throw new RuntimeException(
                    "Unknown operator '{$criterion->operator}' for DateMetadata Criterion handler."
                );
        }
    }
}
