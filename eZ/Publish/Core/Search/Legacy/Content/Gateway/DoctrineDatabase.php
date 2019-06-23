<?php

/**
 * File containing the DoctrineDatabase Content search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use PDO;

/**
 * Content locator gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /**
     * Criteria converter.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter
     */
    protected $criteriaConverter;

    /**
     * Sort clause converter.
     *
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter
     */
    protected $sortClauseConverter;

    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $criteriaConverter
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter $sortClauseConverter
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        LanguageHandler $languageHandler
    ) {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Returns a list of object satisfying the $filter.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param array $languageFilter
     * @param bool $doCount
     *
     * @return mixed[][]
     */
    public function find(
        Criterion $criterion,
        $offset,
        $limit,
        array $sort = null,
        array $languageFilter = [],
        $doCount = true
    ) {
        $count = $doCount ? $this->getResultCount($criterion, $languageFilter) : null;

        if (!$doCount && $limit === 0) {
            throw new \RuntimeException('Invalid query, can not disable count and request 0 items at the same time');
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
     */
    protected function getLanguageMask(array $languageSettings)
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
     * Get query condition.
     *
     * @param Criterion $filter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param array $languageFilter
     *
     * @return string
     */
    protected function getQueryCondition(
        Criterion $filter,
        SelectQuery $query,
        array $languageFilter
    ) {
        $condition = $query->expr->lAnd(
            $this->criteriaConverter->convertCriteria($query, $filter, $languageFilter),
            $query->expr->eq(
                'ezcontentobject.status',
                ContentInfo::STATUS_PUBLISHED
            ),
            $query->expr->eq(
                'ezcontentobject_version.status',
                VersionInfo::STATUS_PUBLISHED
            )
        );

        // If not main-languages query
        if (!empty($languageFilter['languages'])) {
            $condition = $query->expr->lAnd(
                $condition,
                $query->expr->gt(
                    $query->expr->bitAnd(
                        $this->handler->quoteColumn('language_mask', 'ezcontentobject'),
                        $query->bindValue(
                            $this->getLanguageMask($languageFilter),
                            null,
                            PDO::PARAM_INT
                        )
                    ),
                    $query->bindValue(0, null, PDO::PARAM_INT)
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
    protected function getResultCount(Criterion $filter, array $languageFilter)
    {
        $query = $this->handler->createSelectQuery();

        $columnName = $this->handler->quoteColumn('id', 'ezcontentobject');
        $query
            ->select("COUNT( DISTINCT $columnName )")
            ->from($this->handler->quoteTable('ezcontentobject'))
            ->innerJoin(
                'ezcontentobject_version',
                'ezcontentobject.id',
                'ezcontentobject_version.contentobject_id'
            );

        $query->where(
            $this->getQueryCondition($filter, $query, $languageFilter)
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Get sorted arrays of content IDs, which should be returned.
     *
     * @param Criterion $filter
     * @param array $sort
     * @param mixed $offset
     * @param mixed $limit
     * @param array $languageFilter
     *
     * @return int[]
     */
    protected function getContentInfoList(
        Criterion $filter,
        $sort,
        $offset,
        $limit,
        array $languageFilter
    ) {
        $query = $this->handler->createSelectQuery();
        $query->selectDistinct(
            'ezcontentobject.*',
            $this->handler->aliasedColumn($query, 'main_node_id', 'main_tree')
        );

        if ($sort !== null) {
            $this->sortClauseConverter->applySelect($query, $sort);
        }

        $query->from(
            $this->handler->quoteTable('ezcontentobject')
        )->innerJoin(
            'ezcontentobject_version',
            'ezcontentobject.id',
            'ezcontentobject_version.contentobject_id'
        )->leftJoin(
            $this->handler->alias(
                $this->handler->quoteTable('ezcontentobject_tree'),
                $this->handler->quoteIdentifier('main_tree')
            ),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id', 'main_tree'),
                    $this->handler->quoteColumn('id', 'ezcontentobject')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('main_node_id', 'main_tree'),
                    $this->handler->quoteColumn('node_id', 'main_tree')
                )
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

        $query->limit($limit, $offset);

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
