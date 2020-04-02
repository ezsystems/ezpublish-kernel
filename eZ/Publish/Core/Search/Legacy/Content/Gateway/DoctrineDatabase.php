<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Gateway;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use RuntimeException;

/**
 * Content locator gateway implementation using the Doctrine database.
 */
final class DoctrineDatabase extends Gateway
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Criteria converter.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter
     */
    private $criteriaConverter;

    /**
     * Sort clause converter.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter
     */
    private $sortClauseConverter;

    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    private $languageHandler;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        LanguageHandler $languageHandler
    ) {
        $this->connection = $connection;
        $this->dbPlatform = $connection->getDatabasePlatform();
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->languageHandler = $languageHandler;
    }

    public function find(
        Criterion $criterion,
        $offset,
        $limit,
        array $sort = null,
        array $languageFilter = [],
        $doCount = true
    ): array {
        $count = $doCount ? $this->getResultCount($criterion, $languageFilter) : null;

        if (!$doCount && $limit === 0) {
            throw new RuntimeException('Invalid query. Cannot disable count and request 0 items at the same time.');
        }

        if ($limit === 0 || ($count !== null && $count <= $offset)) {
            return ['count' => $count, 'rows' => []];
        }

        $contentInfoList = $this->getContentInfoList($criterion, $sort, $offset, $limit, $languageFilter);

        return [
            'count' => $count,
            'rows' => $contentInfoList,
        ];
    }

    /**
     * Generates a language mask from the given $languageSettings.
     *
     * @param array $languageSettings
     *
     * @return int
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getLanguageMask(array $languageSettings)
    {
        $mask = 0;
        if ($languageSettings['useAlwaysAvailable']) {
            $mask |= 1;
        }

        foreach ($languageSettings['languages'] as $languageCode) {
            $mask |= $this->languageHandler->loadByLanguageCode($languageCode)->id;
        }

        return $mask;
    }

    /**
     * @param array $languageFilter
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    private function getQueryCondition(
        Criterion $filter,
        QueryBuilder $query,
        array $languageFilter
    ) {
        $expr = $query->expr();
        $condition = $expr->andX(
            $this->criteriaConverter->convertCriteria($query, $filter, $languageFilter),
            $expr->eq(
                'c.status',
                ContentInfo::STATUS_PUBLISHED
            ),
            $expr->eq(
                'v.status',
                VersionInfo::STATUS_PUBLISHED
            )
        );

        // If not main-languages query
        if (!empty($languageFilter['languages'])) {
            $condition = $expr->andX(
                $condition,
                $expr->gt(
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        $query->createNamedParameter(
                            $this->getLanguageMask($languageFilter),
                            ParameterType::INTEGER,
                            ':language_mask'
                        )
                    ),
                    $query->createNamedParameter(0, ParameterType::INTEGER, ':zero')
                )
            );
        }

        return $condition;
    }

    /**
     * Get result count.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $languageFilter
     *
     * @return int
     */
    private function getResultCount(Criterion $filter, array $languageFilter)
    {
        $query = $this->connection->createQueryBuilder();

        $columnName = 'c.id';
        $query
            ->select("COUNT( DISTINCT $columnName )")
            ->from(ContentGateway::CONTENT_ITEM_TABLE, 'c')
            ->innerJoin(
                'c',
                ContentGateway::CONTENT_VERSION_TABLE,
                'v',
                'c.id = v.contentobject_id',
            );

        $query->where(
            $this->getQueryCondition($filter, $query, $languageFilter)
        );

        $statement = $query->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Get sorted arrays of content IDs, which should be returned.
     *
     * @param array $sort
     * @param mixed $offset
     * @param mixed $limit
     * @param array $languageFilter
     *
     * @return int[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    private function getContentInfoList(
        Criterion $filter,
        ?array $sort,
        ?int $offset,
        ?int $limit,
        array $languageFilter
    ): array {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'DISTINCT c.*, main_tree.main_node_id AS main_tree_main_node_id',
        );

        if ($sort !== null) {
            $this->sortClauseConverter->applySelect($query, $sort);
        }

        $query
            ->from(ContentGateway::CONTENT_ITEM_TABLE, 'c')
            ->innerJoin(
                'c',
                ContentGateway::CONTENT_VERSION_TABLE,
                'v',
                'c.id = v.contentobject_id'
            )
            ->leftJoin(
                'c',
                LocationGateway::CONTENT_TREE_TABLE,
                'main_tree',
                $query->expr()->andX(
                    'main_tree.contentobject_id = c.id',
                    'main_tree.main_node_id = main_tree.node_id'
                )
            );

        if ($sort !== null) {
            $this->sortClauseConverter->applyJoin($query, $sort, $languageFilter);
        }

        $query->where(
            $this->getQueryCondition($filter, $query, $languageFilter)
        );

        if ($sort !== null) {
            $this->sortClauseConverter->applyOrderBy($query);
        }

        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
