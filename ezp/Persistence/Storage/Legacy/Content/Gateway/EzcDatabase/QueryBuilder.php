<?php
/**
 * File containing the EzcDatabase query builder class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase;
use ezp\Persistence\Storage\Legacy\EzcDbHandler;

class QueryBuilder
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new query builder.
     *
     * @param EzcDbHandler $dbHandler
     */
    public function __construct( ezcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Creates a select query for content objects
     *
     * Creates a select query with all necessary joins to fetch a complete
     * content object. Does not apply any WHERE conditions.
     *
     * @return ezcQuerySelect
     */
    public function createFindQuery()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
                // Content object
                $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject' ),
                $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject' ),
                $this->dbHandler->aliasedColumn( $query, 'contentclass_id', 'ezcontentobject' ),
                $this->dbHandler->aliasedColumn( $query, 'section_id', 'ezcontentobject' ),
                $this->dbHandler->aliasedColumn( $query, 'owner_id', 'ezcontentobject' ),
                $this->dbHandler->aliasedColumn( $query, 'remote_id', 'ezcontentobject' ),
                // Content object version
                $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'creator_id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'created', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'contentobject_id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject_version' ),
                // Content object fields
                $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'contentclassattribute_id', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'data_type_string', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'language_code', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_attribute' ),
                // Content object field data
                $this->dbHandler->aliasedColumn( $query, 'data_float', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'data_int', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'data_text', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'sort_key_int', 'ezcontentobject_attribute' ),
                $this->dbHandler->aliasedColumn( $query, 'sort_key_string', 'ezcontentobject_attribute' ),
                // Content object locations
                $this->dbHandler->aliasedColumn( $query, 'contentobject_id', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'contentobject_is_published', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'contentobject_version', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'depth', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'is_hidden', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'is_invisible', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'main_node_id', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'modified_subnode', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'node_id', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'parent_node_id', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'path_identification_string', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'path_string', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'priority', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'remote_id', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'sort_field', 'ezcontentobject_tree' ),
                $this->dbHandler->aliasedColumn( $query, 'sort_order', 'ezcontentobject_tree' )
            )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject' ) )
            ->leftJoin(
                $this->dbHandler->quoteTable( 'ezcontentobject_version' ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' ),
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
                )
            )
            ->leftJoin(
                $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_attribute' ),
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_attribute' ),
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' )
                    )
                )
            )
            ->leftJoin(
                $this->dbHandler->quoteTable( 'ezcontentobject_tree' ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' ),
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_version', 'ezcontentobject_tree' ),
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' )
                    )
                )
        );

        return $query;
    }
}
