<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Filter\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use function sprintf;

/**
 * Repository Filtering query builder wrapper for \Doctrine\DBAL\Query\QueryBuilder.
 *
 * **NOTE:** To be used **only** with Repository Content/Location Filtering feature.
 *
 * @see \Doctrine\DBAL\Query\QueryBuilder
 */
final class FilteringQueryBuilder extends QueryBuilder
{
    public const SORT_ORDER_MAP = [Query::SORT_ASC => 'ASC', Query::SORT_DESC => 'DESC'];

    /**
     * Create table JOIN, but only if it hasn't been already joined (determined based on $tableAlias).
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\DatabaseException if conditions of pre-existing same alias joins are different
     */
    public function joinOnce(
        string $fromAlias,
        string $tableName,
        string $tableAlias,
        string $conditions
    ): FilteringQueryBuilder {
        $existingJoinConditions = $this->getExistingTableAliasJoinCondition($tableAlias);
        if (null !== $existingJoinConditions) {
            $this->validateJoinOnceConditions(
                $existingJoinConditions,
                $conditions,
                $tableName,
                $tableAlias
            );

            return $this;
        }

        // at this point, if table exists as fromAlias, it means it's a "FROM" table
        if ($this->isJoinedAsFromTableAlias($tableAlias)) {
            return $this;
        }

        $this->join($fromAlias, $tableName, $tableAlias, $conditions);

        return $this;
    }

    /**
     * Create table LEFT JOIN, but only if it hasn't been already joined (determined based on $tableAlias).
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\DatabaseException if conditions of pre-existing same alias joins are different
     */
    public function leftJoinOnce(
        string $fromAlias,
        string $tableName,
        string $tableAlias,
        string $conditions
    ): FilteringQueryBuilder {
        $existingJoinConditions = $this->getExistingTableAliasJoinCondition($tableAlias);
        if (null !== $existingJoinConditions) {
            $this->validateJoinOnceConditions(
                $existingJoinConditions,
                $conditions,
                $tableName,
                $tableAlias
            );

            return $this;
        }

        // at this point, if table exists as fromAlias, it means it's a "FROM" table
        if ($this->isJoinedAsFromTableAlias($tableAlias)) {
            return $this;
        }

        $this->leftJoin($fromAlias, $tableName, $tableAlias, $conditions);

        return $this;
    }

    /**
     * @return string conditions, null if table is not joined yet.
     */
    public function getExistingTableAliasJoinCondition(string $tableAlias): ?string
    {
        $joinPart = $this->getQueryPart('join');
        // joins are stored per each $fromAlias (as an assoc. key), but a flat list of joins is needed here
        $joins = !empty($joinPart) ? array_merge(...array_values($joinPart)) : [];
        $existingTableAliasJoins = array_values(
            array_filter(
                $joins,
                static function (array $joinData) use ($tableAlias) {
                    return $joinData['joinAlias'] === $tableAlias;
                }
            )
        );

        $joinCondition = $existingTableAliasJoins[0]['joinCondition'] ?? null;

        return null !== $joinCondition ? (string)$joinCondition : null;
    }

    /**
     * Inherited from \Doctrine\DBAL\Query\QueryBuilder::addOrderBy.
     *
     * @param string $sort
     * @param string|null $order
     */
    public function addOrderBy($sort, $order = null): FilteringQueryBuilder
    {
        return parent::addOrderBy($sort, $this->mapLegacyOrderToDoctrine($order));
    }

    private function mapLegacyOrderToDoctrine(?string $order): ?string
    {
        if (null !== $order && isset(self::SORT_ORDER_MAP[$order])) {
            return self::SORT_ORDER_MAP[$order];
        }

        // intentionally pass through
        return $order;
    }

    private function validateJoinOnceConditions(
        string $existingJoinConditions,
        string $conditions,
        string $tableName,
        string $tableAlias
    ): void {
        if ($existingJoinConditions !== $conditions) {
            throw new DatabaseException(
                sprintf(
                    'FilteringQueryBuilder: "%s" table cannot be joined as "%s" ' .
                    'with conditions "%s" because there is a pre-existing join with the same ' .
                    'alias but different conditions: "%s"',
                    $tableName,
                    $tableAlias,
                    $conditions,
                    $existingJoinConditions
                )
            );
        }
    }

    private function isJoinedAsFromTableAlias(string $tableAlias): bool
    {
        return array_key_exists($tableAlias, $this->getQueryPart('join'));
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function buildOperatorBasedCriterionConstraint(
        string $columnName,
        array $criterionValue,
        string $operator
    ): string {
        switch ($operator) {
            case Operator::IN:
                return $this->expr()->in(
                    $columnName,
                    $this->createNamedParameter($criterionValue, Connection::PARAM_INT_ARRAY)
                );

            case Query\Criterion\Operator::BETWEEN:
                $databasePlatform = $this->getConnection()->getDatabasePlatform();

                return $databasePlatform->getBetweenExpression(
                    $columnName,
                    $this->createNamedParameter($criterionValue[0], ParameterType::INTEGER),
                    $this->createNamedParameter($criterionValue[1], ParameterType::INTEGER)
                );

            case Query\Criterion\Operator::EQ:
            case Query\Criterion\Operator::GT:
            case Query\Criterion\Operator::GTE:
            case Query\Criterion\Operator::LT:
            case Query\Criterion\Operator::LTE:
                return $this->expr()->comparison(
                    $columnName,
                    $operator,
                    $this->createNamedParameter(reset($criterionValue), ParameterType::INTEGER)
                );

            default:
                throw new DatabaseException(
                    "Unsupported operator {$operator} for column {$columnName}"
                );
        }
    }

    public function joinPublishedVersion(): FilteringQueryBuilder
    {
        $expressionBuilder = $this->expr();

        $this->joinOnce(
            'content',
            ContentGateway::CONTENT_VERSION_TABLE,
            'version',
            (string)$expressionBuilder->andX(
                'content.id = version.contentobject_id',
                'content.current_version = version.version',
                $expressionBuilder->eq(
                    'version.status',
                    $this->createNamedParameter(
                        VersionInfo::STATUS_PUBLISHED,
                        ParameterType::INTEGER
                    )
                )
            )
        );

        return $this;
    }

    public function joinAllLocations(): FilteringQueryBuilder
    {
        $this->joinOnce(
            'content',
            LocationGateway::CONTENT_TREE_TABLE,
            'location',
            'content.id = location.contentobject_id'
        );

        return $this;
    }
}
