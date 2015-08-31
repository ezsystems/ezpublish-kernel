<?php

/**
 * File containing the DoctrineDatabase Content search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $criteriaConverter
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter $sortClauseConverter
     */
    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter
    ) {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
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
        array $languageFilter = array(),
        $doCount = true
    ) {
        $count = $doCount ? $this->getResultCount($criterion) : null;

        if (!$doCount && $limit === 0) {
            throw new \RuntimeException('Invalid query, can not disable count and request 0 items at the same time');
        }

        if ($limit === 0 || ($count !== null && $count <= $offset)) {
            return array('count' => $count, 'rows' => array());
        }

        $contentInfoList = $this->getContentInfoList($criterion, $sort, $offset, $limit);

        return array(
            'count' => $count,
            'rows' => $contentInfoList,
        );
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
    protected function getQueryCondition(Criterion $filter, SelectQuery $query)
    {
        $condition = $query->expr->lAnd(
            $this->criteriaConverter->convertCriteria($query, $filter),
            $query->expr->eq(
                'ezcontentobject.status',
                ContentInfo::STATUS_PUBLISHED
            )
        );

        return $condition;
    }

    /**
     * Get result count.
     *
     * @param Criterion $filter
     *
     * @return int
     */
    protected function getResultCount(Criterion $filter)
    {
        $query = $this->handler->createSelectQuery();

        $columnName = $this->handler->quoteColumn('id', 'ezcontentobject');
        $query
            ->select("COUNT( DISTINCT $columnName )")
            ->from($this->handler->quoteTable('ezcontentobject'))
            ->innerJoin(
                $this->handler->quoteTable('ezcontentobject_version'),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->handler->quoteColumn('contentobject_id', 'ezcontentobject_version'),
                        $this->handler->quoteColumn('id', 'ezcontentobject')
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('version', 'ezcontentobject_version'),
                        $this->handler->quoteColumn('current_version', 'ezcontentobject')
                    )
                )
            );

        $query->where(
            $this->getQueryCondition($filter, $query)
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
     *
     * @return int[]
     */
    protected function getContentInfoList(Criterion $filter, $sort, $offset, $limit)
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            'ezcontentobject.*',
            // Content object names
            $this->handler->aliasedColumn($query, 'name', 'ezcontentobject_name'),
            $this->handler->aliasedColumn($query, 'content_translation', 'ezcontentobject_name'),
            // Main node id (if there is one)
            $this->handler->aliasedColumn($query, 'main_node_id', 'main_tree')
        );

        if ($sort !== null) {
            $this->sortClauseConverter->applySelect($query, $sort);
        }

        $query->from(
            $this->handler->quoteTable('ezcontentobject')
        )->innerJoin(
            $this->handler->quoteTable('ezcontentobject_name'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id', 'ezcontentobject_name'),
                    $this->handler->quoteColumn('id', 'ezcontentobject')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('content_version', 'ezcontentobject_name'),
                    $this->handler->quoteColumn('current_version', 'ezcontentobject')
                )
            )
        )->innerJoin(
            $this->handler->quoteTable('ezcontentobject_version'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('contentobject_id', 'ezcontentobject_version'),
                    $this->handler->quoteColumn('id', 'ezcontentobject')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('version', 'ezcontentobject_version'),
                    $this->handler->quoteColumn('current_version', 'ezcontentobject')
                )
            )
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
            $this->sortClauseConverter->applyJoin($query, $sort);
        }

        $query->where(
            $this->getQueryCondition($filter, $query)
        );

        if ($sort !== null) {
            $this->sortClauseConverter->applyOrderBy($query);
        }

        $query->limit($limit, $offset);

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
