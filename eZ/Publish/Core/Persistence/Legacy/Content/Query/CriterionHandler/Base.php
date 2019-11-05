<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

/**
 * Optional abstract Base with utility functions for common needs.
 */
abstract class Base implements CriterionHandler
{
    /**
     * Map of criterion operators to the respective function names
     * in the DoctrineDatabase DBAL.
     *
     * @var array
     */
    protected const DBAL_COMPARIONS_MAP = [
        Criterion\Operator::EQ => 'eq',
        Criterion\Operator::GT => 'gt',
        Criterion\Operator::GTE => 'gte',
        Criterion\Operator::LT => 'lt',
        Criterion\Operator::LTE => 'lte',
    ];


    protected function operateInt(QueryBuilder $query, string $column, Criterion $criterion): string
    {
        if ($criterion->operator === Criterion\Operator::IN) {
            return $query->expr()->in(
                $column,
                $query->createNamedParameter((array)$criterion->value, Connection::PARAM_INT_ARRAY)
            );
        }

        if (!isset(self::DBAL_COMPARIONS_MAP[$criterion->operator])) {
            throw new NotImplementedException(sprintf("Operator '%s' not implemented for criterion '%s'", $criterion->operator, get_class($criterion)));
        }

        $functionName = self::DBAL_COMPARIONS_MAP[$criterion->operator];
        return $query->expr()->$functionName(
            $column,
            $query->createNamedParameter((array)$criterion->value, ParameterType::INTEGER)
        );
    }


    protected function operateString(QueryBuilder $query, string $column, Criterion $criterion): string
    {
        if ($criterion->operator === Criterion\Operator::IN) {
            return $query->expr()->in(
                $column,
                $query->createNamedParameter((array)$criterion->value, Connection::PARAM_STR_ARRAY)
            );
        }

        if (!isset(self::DBAL_COMPARIONS_MAP[$criterion->operator])) {
            throw new NotImplementedException(sprintf("Operator '%s' not implemented for criterion '%s'", $criterion->operator, get_class($criterion)));
        }

        $functionName = self::DBAL_COMPARIONS_MAP[$criterion->operator];
        return $query->expr()->$functionName(
            $column,
            $query->createNamedParameter((array)$criterion->value, ParameterType::STRING)
        );
    }

    /**
     * Adds main location left join, if location is not already present as from OR join.
     *
     * @param QueryBuilder $query
     */
    protected function addMainLocationTableIfNeeded(QueryBuilder $query): void
    {
        if ($this->queryPartExists($query, 'from', 'l')) {
            return;
        }

        $this->addLeftJoinIfNeeded(
            $query,
            'content',
            'ezcontentobject_tree',
            'location',
            'location.contentobject_id = content.id AND location.node_id = location.main_node_id'
        );
    }


    /**
     * Adds a inner join if needed, unique key needs to be $alias across all joins.
     *
     * <code>
     *     $this->addInnerJoinIfNeeded($queryBuilder, 'u', 'phonenumbers', 'p', 'p.is_primary = 1');
     * </code>
     *
     * @param QueryBuilder $query
     * @param string $fromAlias The alias that points to a from clause.
     * @param string $join      The table name to join.
     * @param string $alias     The alias of the join table.
     * @param string $condition The condition for the join.
     */
    protected function addInnerJoinIfNeeded(QueryBuilder $query, string $fromAlias, string $join, string $alias, string $condition = null): void
    {
        if ($this->queryPartExists($query, 'join', $alias)) {
            return;
        }

        $query->innerJoin($fromAlias, $join, $alias, $condition);
    }

    /**
     * Adds a left "outer" join if needed, unique key needs to be $alias across all joins.
     *
     * Warning: Any where on joined table will be ignored if there is no matching row(s) within $condition.
     *
     * <code>
     *     $this->addLeftJoinIfNeeded($queryBuilder, 'u', 'phonenumbers', 'p', 'p.is_primary = 1');
     * </code>
     *
     * @param QueryBuilder $query
     * @param string $fromAlias The alias that points to a from clause.
     * @param string $join      The table name to join.
     * @param string $alias     The alias of the join table.
     * @param string $condition The condition for the join.
     */
    protected function addLeftJoinIfNeeded(QueryBuilder $query, string $fromAlias, string $join, string $alias, string $condition = null): void
    {
        if ($this->queryPartExists($query, 'join', $alias)) {
            return;
        }

        $query->leftJoin($fromAlias, $join, $alias, $condition);
    }

    /**
     * @internal
     */
    private function queryPartExists(QueryBuilder $query, string $queryPartName, string $alias): bool
    {
        foreach ($query->getQueryPart($queryPartName) as $part){

            if ($queryPartName === 'join' && isset($part['joinAlias']) && $part['joinAlias'] === $alias) {
                return true;
            }
            if ($queryPartName === 'from' && isset($part['alias']) && $part['alias'] === $alias) {
                return true;
            }
        }

        return false;
    }
}
