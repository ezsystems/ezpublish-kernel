<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use function time;

/**
 * @internal For internal use by the Content gateway.
 */
final class QueryBuilder
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create select query to query content name data.
     */
    public function createNamesQuery(): DoctrineQueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'contentobject_id AS ezcontentobject_name_contentobject_id',
                'content_version AS ezcontentobject_name_content_version',
                'name AS ezcontentobject_name_name',
                'content_translation AS ezcontentobject_name_content_translation'
            )
            ->from(Gateway::CONTENT_NAME_TABLE);

        return $query;
    }

    /**
     * Create a select query for content relations.
     */
    public function createRelationFindQueryBuilder(): DoctrineQueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'l.id AS ezcontentobject_link_id',
                'l.contentclassattribute_id AS ezcontentobject_link_contentclassattribute_id',
                'l.from_contentobject_id AS ezcontentobject_link_from_contentobject_id',
                'l.from_contentobject_version AS ezcontentobject_link_from_contentobject_version',
                'l.relation_type AS ezcontentobject_link_relation_type',
                'l.to_contentobject_id AS ezcontentobject_link_to_contentobject_id'
            )
            ->from(
                Gateway::CONTENT_RELATION_TABLE, 'l'
            );

        return $query;
    }

    /**
     * Create an update query for setting Content item Version status.
     */
    public function getSetVersionStatusQuery(
        int $contentId,
        int $versionNo,
        int $versionStatus
    ): DoctrineQueryBuilder {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update(Gateway::CONTENT_VERSION_TABLE)
            ->set('status', ':status')
            ->set('modified', ':modified')
            ->where('contentobject_id = :contentId')
            ->andWhere('version = :versionNo')
            ->setParameter('status', $versionStatus, ParameterType::INTEGER)
            ->setParameter('modified', time(), ParameterType::INTEGER)
            ->setParameter('contentId', $contentId, ParameterType::INTEGER)
            ->setParameter('versionNo', $versionNo, ParameterType::INTEGER);

        return $query;
    }

    /**
     * Create a select query to load Content Info data.
     *
     * @see Gateway::loadContentInfo()
     * @see Gateway::loadContentInfoList()
     * @see Gateway::loadContentInfoByRemoteId()
     * @see Gateway::loadContentInfoByLocationId()
     */
    public function createLoadContentInfoQueryBuilder(
        bool $joinMainLocation = true
    ): DoctrineQueryBuilder {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();

        $joinCondition = $expr->eq('c.id', 't.contentobject_id');
        if ($joinMainLocation) {
            // wrap join condition with AND operator and join by a Main Location
            $joinCondition = $expr->andX(
                $joinCondition,
                $expr->eq('t.node_id', 't.main_node_id')
            );
        }

        $queryBuilder
            ->select('c.*', 't.main_node_id AS ezcontentobject_tree_main_node_id')
            ->from(Gateway::CONTENT_ITEM_TABLE, 'c')
            ->leftJoin(
                'c',
                'ezcontentobject_tree',
                't',
                $joinCondition
            );

        return $queryBuilder;
    }

    /**
     * Create a doctrine query builder with db fields needed to populate VersionInfo.
     *
     * @param int|null $versionNo Selects current version number if left undefined as null
     */
    public function createVersionInfoQueryBuilder(?int $versionNo = null): DoctrineQueryBuilder
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $queryBuilder->expr();
        $queryBuilder
            ->select(
                'c.id AS ezcontentobject_id',
                'c.contentclass_id AS ezcontentobject_contentclass_id',
                'c.section_id AS ezcontentobject_section_id',
                'c.owner_id AS ezcontentobject_owner_id',
                'c.remote_id AS ezcontentobject_remote_id',
                'c.current_version AS ezcontentobject_current_version',
                'c.initial_language_id AS ezcontentobject_initial_language_id',
                'c.modified AS ezcontentobject_modified',
                'c.published AS ezcontentobject_published',
                'c.status AS ezcontentobject_status',
                'c.name AS ezcontentobject_name',
                'c.language_mask AS ezcontentobject_language_mask',
                'c.is_hidden AS ezcontentobject_is_hidden',
                'v.id AS ezcontentobject_version_id',
                'v.version AS ezcontentobject_version_version',
                'v.modified AS ezcontentobject_version_modified',
                'v.creator_id AS ezcontentobject_version_creator_id',
                'v.created AS ezcontentobject_version_created',
                'v.status AS ezcontentobject_version_status',
                'v.language_mask AS ezcontentobject_version_language_mask',
                'v.initial_language_id AS ezcontentobject_version_initial_language_id',
                't.main_node_id AS ezcontentobject_tree_main_node_id'
            )
            ->from('ezcontentobject', 'c')
            ->innerJoin(
                'c',
                'ezcontentobject_version',
                'v',
                $expr->andX(
                    $expr->eq('c.id', 'v.contentobject_id'),
                    $expr->eq('v.version', $versionNo ?: 'c.current_version')
                )
            )
            ->leftJoin(
                'c',
                'ezcontentobject_tree',
                't',
                $expr->andX(
                    $expr->eq('c.id', 't.contentobject_id'),
                    $expr->eq('t.node_id', 't.main_node_id')
                )
            );

        return $queryBuilder;
    }

    /**
     * Get query builder for content version objects, used for version loading w/o fields.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * content object. Does not apply any WHERE conditions, and does not contain
     * name data as it will lead to large result set {@see createNamesQuery}.
     */
    public function createVersionInfoFindQueryBuilder(): DoctrineQueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();

        $query
            ->select(
                'v.id AS ezcontentobject_version_id',
                'v.version AS ezcontentobject_version_version',
                'v.modified AS ezcontentobject_version_modified',
                'v.creator_id AS ezcontentobject_version_creator_id',
                'v.created AS ezcontentobject_version_created',
                'v.status AS ezcontentobject_version_status',
                'v.contentobject_id AS ezcontentobject_version_contentobject_id',
                'v.initial_language_id AS ezcontentobject_version_initial_language_id',
                'v.language_mask AS ezcontentobject_version_language_mask',
                // Content main location
                't.main_node_id AS ezcontentobject_tree_main_node_id',
                // Content object
                'c.id AS ezcontentobject_id',
                'c.contentclass_id AS ezcontentobject_contentclass_id',
                'c.section_id AS ezcontentobject_section_id',
                'c.owner_id AS ezcontentobject_owner_id',
                'c.remote_id AS ezcontentobject_remote_id',
                'c.current_version AS ezcontentobject_current_version',
                'c.initial_language_id AS ezcontentobject_initial_language_id',
                'c.modified AS ezcontentobject_modified',
                'c.published AS ezcontentobject_published',
                'c.status AS ezcontentobject_status',
                'c.name AS ezcontentobject_name',
                'c.language_mask AS ezcontentobject_language_mask',
                'c.is_hidden AS ezcontentobject_is_hidden'
            )
            ->from('ezcontentobject_version', 'v')
            ->innerJoin(
                'v',
                'ezcontentobject',
                'c',
                $expr->eq('c.id', 'v.contentobject_id')
            )
            ->leftJoin(
                'v',
                'ezcontentobject_tree',
                't',
                $expr->andX(
                    $expr->eq('t.contentobject_id', 'v.contentobject_id'),
                    $expr->eq('t.main_node_id', 't.node_id')
                )
            );

        return $query;
    }
}
