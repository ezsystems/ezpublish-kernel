<?php

/**
 * File containing the DoctrineDatabase query builder class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

class QueryBuilder
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Creates a new query builder.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Creates a select query for full content objects, used by Content `load`.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * content object. Does not apply any WHERE conditions unless
     * translations are provided, and does not contain name data as it will
     * lead to very large result set {@see createNamesQuery}.
     *
     * @param string[] $translations
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createFindQuery(array $translations = null)
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Content object
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'contentclass_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'section_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'owner_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'remote_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'current_version', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'initial_language_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'modified', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'published', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'status', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'name', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'language_mask', 'ezcontentobject'),
            // Content object version
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'version', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'modified', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'creator_id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'created', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'status', 'ezcontentobject_version'),
            // @todo: remove ezcontentobject_version.contentobject_id from query as it duplicates ezcontentobject.id
            $this->dbHandler->aliasedColumn($query, 'contentobject_id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'language_mask', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'initial_language_id', 'ezcontentobject_version'),
            // Content object fields
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'contentclassattribute_id', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'data_type_string', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'language_code', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'language_id', 'ezcontentobject_attribute'),
            // @todo: remove ezcontentobject_attribute.version from query as it duplicates ezcontentobject_version.version
            $this->dbHandler->aliasedColumn($query, 'version', 'ezcontentobject_attribute'),
            // Content object field data
            $this->dbHandler->aliasedColumn($query, 'data_float', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'data_int', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'data_text', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'sort_key_int', 'ezcontentobject_attribute'),
            $this->dbHandler->aliasedColumn($query, 'sort_key_string', 'ezcontentobject_attribute'),
            // Content object locations
            $this->dbHandler->aliasedColumn($query, 'main_node_id', 'ezcontentobject_tree')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->innerJoin(
            $this->dbHandler->quoteTable('ezcontentobject_version'),
            $query->expr->eq(
                $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version'),
                $this->dbHandler->quoteColumn('id', 'ezcontentobject')
            )
        )->innerJoin(
            $this->dbHandler->quoteTable('ezcontentobject_attribute'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_attribute'),
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('version', 'ezcontentobject_attribute'),
                    $this->dbHandler->quoteColumn('version', 'ezcontentobject_version')
                )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable('ezcontentobject_tree'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('id', 'ezcontentobject')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('main_node_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('node_id', 'ezcontentobject_tree')
                )
            )
        );

        if (!empty($translations)) {
            $query->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn('language_code', 'ezcontentobject_attribute'),
                    $translations
                )
            );
        }

        return $query;
    }

    /**
     * Create select query to query content name data.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createNamesQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->select(
                $this->dbHandler->aliasedColumn($query, 'contentobject_id', 'ezcontentobject_name'),
                $this->dbHandler->aliasedColumn($query, 'content_version', 'ezcontentobject_name'),
                $this->dbHandler->aliasedColumn($query, 'name', 'ezcontentobject_name'),
                $this->dbHandler->aliasedColumn($query, 'content_translation', 'ezcontentobject_name')
            )
            ->from($this->dbHandler->quoteTable('ezcontentobject_name'));

        return $query;
    }

    /**
     * Creates a select query for content relations.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createRelationFindQuery()
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject_link'),
            $this->dbHandler->aliasedColumn($query, 'contentclassattribute_id', 'ezcontentobject_link'),
            $this->dbHandler->aliasedColumn($query, 'from_contentobject_id', 'ezcontentobject_link'),
            $this->dbHandler->aliasedColumn($query, 'from_contentobject_version', 'ezcontentobject_link'),
            $this->dbHandler->aliasedColumn($query, 'relation_type', 'ezcontentobject_link'),
            $this->dbHandler->aliasedColumn($query, 'to_contentobject_id', 'ezcontentobject_link')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_link')
        );

        return $query;
    }

    /**
     * Creates a select query for content version objects, used for version loading w/o fields.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * content object. Does not apply any WHERE conditions, and does not contain
     * name data as it will lead to large result set {@see createNamesQuery}.
     *
     * @return \eZ\Publish\Core\Persistence\Database\SelectQuery
     */
    public function createVersionInfoFindQuery()
    {
        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Content object version
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'version', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'modified', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'creator_id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'created', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'status', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'contentobject_id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'initial_language_id', 'ezcontentobject_version'),
            $this->dbHandler->aliasedColumn($query, 'language_mask', 'ezcontentobject_version'),
            // Content main location
            $this->dbHandler->aliasedColumn($query, 'main_node_id', 'ezcontentobject_tree'),
            // Content object
            // @todo: remove ezcontentobject.d from query as it duplicates ezcontentobject_version.contentobject_id
            $this->dbHandler->aliasedColumn($query, 'id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'contentclass_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'section_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'owner_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'remote_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'current_version', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'initial_language_id', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'modified', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'published', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'status', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'name', 'ezcontentobject'),
            $this->dbHandler->aliasedColumn($query, 'language_mask', 'ezcontentobject')
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject_version')
        )->innerJoin(
            $this->dbHandler->quoteTable('ezcontentobject'),
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id', 'ezcontentobject'),
                $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version')
            )
        )->leftJoin(
            $this->dbHandler->quoteTable('ezcontentobject_tree'),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('contentobject_id', 'ezcontentobject_version')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('main_node_id', 'ezcontentobject_tree'),
                    $this->dbHandler->quoteColumn('node_id', 'ezcontentobject_tree')
                )
            )
        );

        return $query;
    }
}
