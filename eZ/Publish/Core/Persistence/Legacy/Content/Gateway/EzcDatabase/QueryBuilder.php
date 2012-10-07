<?php
/**
 * File containing the EzcDatabase query builder class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

class QueryBuilder
{
    /**
     * Database handler
     *
     * @var \EzcDbHandler
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
     * @param string[] $translations
     * @return \ezcQuerySelect
     */
    public function createFindQuery( array $translations = null )
    {
        /** @var $query \ezcQuerySelect */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Content object
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'contentclass_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'section_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'owner_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'remote_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'current_version', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'initial_language_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'published', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject' ),
            // Content object version
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'creator_id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'created', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'contentobject_id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'initial_language_id', 'ezcontentobject_version' ),
            // Content object fields
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'contentclassattribute_id', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'data_type_string', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'language_code', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'language_id', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_attribute' ),
            // Content object field data
            $this->dbHandler->aliasedColumn( $query, 'data_float', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'data_int', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'data_text', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'sort_key_int', 'ezcontentobject_attribute' ),
            $this->dbHandler->aliasedColumn( $query, 'sort_key_string', 'ezcontentobject_attribute' ),
            // Content object names
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject_name' ),
            $this->dbHandler->aliasedColumn( $query, 'content_translation', 'ezcontentobject_name' ),
            // Content object locations
            $this->dbHandler->aliasedColumn( $query, 'main_node_id', 'ezcontentobject_tree' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' ),
                $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
            )
        )->leftJoin(
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
        )->leftJoin(
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
        // @todo: Joining with ezcontentobject_name is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentobject_name' ),
            $query->expr->lAnd(
                // ezcontentobject_name.content_translation is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_name' ),
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'content_version', 'ezcontentobject_name' ),
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' )
                )
            )
        );

        if ( $translations !== null )
        {
            $query->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'language_code', 'ezcontentobject_attribute' ),
                    $translations
                )
            );
        }

        return $query;
    }

    /**
     * Creates a select query for content relations
     *
     * @return \ezcQuerySelect
     */
    public function createRelationFindQuery()
    {
        /** @var $query \ezcQuerySelect */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'contentclassattribute_id', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'from_contentobject_id', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'from_contentobject_version', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'op_code', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'relation_type', 'ezcontentobject_link' ),
            $this->dbHandler->aliasedColumn( $query, 'to_contentobject_id', 'ezcontentobject_link' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_link' )
        );

        return $query;
    }

    /**
     * Creates a select query for content version objects
     *
     * Creates a select query with all necessary joins to fetch a complete
     * content object. Does not apply any WHERE conditions.
     *
     * @return \ezcQuerySelect
     */
    public function createVersionInfoFindQuery()
    {
        /** @var $query \ezcQuerySelect */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            // Content object version
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'creator_id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'created', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'contentobject_id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'initial_language_id', 'ezcontentobject_version' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject_version' ),
            // Content main location
            $this->dbHandler->aliasedColumn( $query, 'main_node_id', 'ezcontentobject_tree' ),
            // Content object
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'contentclass_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'section_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'owner_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'remote_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'current_version', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'initial_language_id', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'published', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject' ),
            $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject' ),
            // Content object names
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject_name' ),
            $this->dbHandler->aliasedColumn( $query, 'content_translation', 'ezcontentobject_name' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentobject' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
                $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentobject_tree' ),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_tree' ),
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_version', 'ezcontentobject_tree' ),
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'main_node_id', 'ezcontentobject_tree' ),
                    $this->dbHandler->quoteColumn( 'node_id', 'ezcontentobject_tree' )
                )
            )
        // @todo: Joining with ezcontentobject_name is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezcontentobject_name' ),
            $query->expr->lAnd(
                // ezcontentobject_name.content_translation is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_name' ),
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'content_version', 'ezcontentobject_name' ),
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' )
                )
            )
        );

        return $query;
    }
}
