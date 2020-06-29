<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Location\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Filter\Gateway\Gateway;
use eZ\Publish\SPI\Persistence\Filter\CriterionVisitor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Persistence\Filter\SortClauseVisitor;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Legacy Storage
 */
final class DoctrineGateway implements Gateway
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \eZ\Publish\SPI\Persistence\Filter\CriterionVisitor */
    private $criterionVisitor;

    /** @var \eZ\Publish\SPI\Persistence\Filter\SortClauseVisitor */
    private $sortClauseVisitor;

    public function __construct(
        Connection $connection,
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor
    ) {
        $this->connection = $connection;
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
    }

    private function getDatabasePlatform(): AbstractPlatform
    {
        try {
            return $this->connection->getDatabasePlatform();
        } catch (DBALException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function count(FilteringCriterion $criterion): int
    {
        $query = $this->buildQuery($criterion);

        $query->select($this->getDatabasePlatform()->getCountExpression('DISTINCT location.node_id'));

        return (int)$query->execute()->fetch(FetchMode::COLUMN);
    }

    public function find(
        FilteringCriterion $criterion,
        array $sortClauses,
        int $limit,
        int $offset
    ): iterable {
        $query = $this->buildQuery($criterion);
        $this->sortClauseVisitor->visitSortClauses($query, $sortClauses);

        $query->setFirstResult($offset);
        if ($limit > 0) {
            $query->setMaxResults($limit);
        }

        $resultStatement = $query->execute();

        while (false !== ($row = $resultStatement->fetch(FetchMode::ASSOCIATIVE))) {
            yield $row;
        }
    }

    private function buildQuery(FilteringCriterion $criterion): FilteringQueryBuilder
    {
        $queryBuilder = new FilteringQueryBuilder($this->connection);
        $queryBuilder
            ->select(
                [
                    // Location
                    'location.node_id AS location_node_id',
                    'location.priority AS location_priority',
                    'location.is_hidden AS location_is_hidden',
                    'location.is_invisible AS location_is_invisible',
                    'location.remote_id AS location_remote_id',
                    'location.contentobject_id AS location_contentobject_id',
                    'location.parent_node_id AS location_parent_node_id',
                    'location.path_identification_string AS location_path_identification_string',
                    'location.path_string AS location_path_string',
                    'location.depth AS location_depth',
                    'location.sort_field AS location_sort_field',
                    'location.sort_order AS location_sort_order',
                    // Main Location (nullable)
                    'location.main_node_id AS content_main_location_id',
                    // Content Info
                    'content.id AS content_id',
                    'content.contentclass_id AS content_type_id',
                    'content.current_version AS content_current_version',
                    'content.initial_language_id AS content_initial_language_id',
                    'content.language_mask AS content_language_mask',
                    'content.modified AS content_modified',
                    'content.name AS content_name',
                    'content.owner_id AS content_owner_id',
                    'content.published AS content_published',
                    'content.remote_id AS content_remote_id',
                    'content.section_id AS content_section_id',
                    'content.status AS content_status',
                    'content.is_hidden AS content_is_hidden',
                ]
            )
            ->distinct()
            ->from(LocationGateway::CONTENT_TREE_TABLE, 'location')
            ->join(
                'location',
                ContentGateway::CONTENT_ITEM_TABLE,
                'content',
                'content.id = location.contentobject_id'
            )
            ->joinPublishedVersion()
        ;

        $constraint = $this->criterionVisitor->visitCriteria($queryBuilder, $criterion);
        if (null !== $constraint) {
            $queryBuilder->where($constraint);
        }

        return $queryBuilder;
    }
}
