<?php
/**
 * File containing the EzcDatabase content gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\Field;

/**
 * ezcDatabase based content gateway
 */
class EzcDatabase extends Gateway
{
    /**
     * Zeta Components database handler.
     *
     * @var EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Creates a new gateway based on $db
     *
     * @param EzcDbHandler $db
     */
    public function __construct( EzcDbHandler $db )
    {
        $this->dbHandler = $db;
    }

    /**
     * Inserts a new content object.
     *
     * @param Content $content
     * @return int ID
     * @todo Oracle sequences?
     */
    public function insertContentObject( Content $content )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
            // @FIXME: Determine version?
            $this->dbHandler->quoteColumn( 'current_version' ),
            $q->bindValue( 1, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $content->name )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclass_id' ),
            $q->bindValue( $content->typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'section_id' ),
            $q->bindValue( $content->sectionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'owner_id' ),
            $q->bindValue( $content->ownerId, null, \PDO::PARAM_INT )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Inserts a new version.
     *
     * @param Version $version
     * @return int ID
     */
    public function insertVersion( Version $version )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $version->versionNo )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $version->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $q->bindValue( $version->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $q->bindValue( $version->created, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( $version->state, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_id' ),
            $q->bindValue( $version->contentId, null, \PDO::PARAM_INT )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Updates an existing version
     *
     * @param int|string $version
     * @param int $versionNo
     * @param int|string $userId
     * @return void
     */
    public function updateVersion( $version, $versionNo, $userId )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $versionNo )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( time(), null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'user_id' ),
            $q->bindValue( $userId, null, \PDO::PARAM_INT )
        )->where( $q->expr->eq(
            $this->dbHandler->quoteColumn( 'id' ),
            $q->bindValue( $version )
        ) );
        $q->prepare()->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Inserts a new field.
     *
     * Only used when a new content object is created. After that, field IDs
     * need to stay the same, only the version number changes.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     * @return int ID
     */
    public function insertNewField( Content $content, Field $field, StorageFieldValue $value )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_id' ),
            $q->bindValue( $content->id, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclassattribute_id' ),
            $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'data_type_string' ),
            $q->bindValue( $field->type )
        )->set(
            // @FIXME: Is language code correct?
            $this->dbHandler->quoteColumn( 'language_code' ),
            $q->bindValue( $field->language )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $field->versionNo )
        )->set(
            $this->dbHandler->quoteColumn( 'data_float' ),
            $q->bindValue( $value->dataFloat )
        )->set(
            $this->dbHandler->quoteColumn( 'data_int' ),
            $q->bindValue( $value->dataInt )
        )->set(
            $this->dbHandler->quoteColumn( 'data_text' ),
            $q->bindValue( $value->dataText )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_int' ),
            $q->bindValue( $value->sortKeyInt )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_string' ),
            $q->bindValue( $value->sortKeyString )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId();
    }

    /**
     * Updates an existing field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     * @return void
     */
    public function updateField( Field $field, StorageFieldValue $value )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        )->set(
            $this->dbHandler->quoteColumn( 'data_float' ),
            $q->bindValue( $value->dataFloat )
        )->set(
            $this->dbHandler->quoteColumn( 'data_int' ),
            $q->bindValue( $value->dataInt )
        )->set(
            $this->dbHandler->quoteColumn( 'data_text' ),
            $q->bindValue( $value->dataText )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_int' ),
            $q->bindValue( $value->sortKeyInt )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_string' ),
            $q->bindValue( $value->sortKeyString )
        )->where( $q->expr->lAnd(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $field->id )
            ),
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'version' ),
                $q->bindValue( $field->versionNo )
            )
        ) );
        $q->prepare()->execute();
    }

    /**
     * Load data for a content object
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $contentId
     * @param mixed $version
     * @return array
     */
    public function load( $contentId, $version )
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
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' ),
                        $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' ),
                        $query->bindValue( $version )
                    )
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
            )
            ->where( $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
                $query->bindValue( $contentId )
            ) );
        $statement = $query->prepare();
        $statement->execute();

        $rows = array();
        while ( $row = $statement->fetch( \PDO::FETCH_ASSOC ) )
        {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Returns all version data for the given $contentId
     *
     * @param mixed $contentId
     * @return string[][]
     */
    public function listVersions( $contentId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
                $this->dbHandler->aliasedColumn( $query, 'id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'version', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'modified', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'creator_id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'created', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'status', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'contentobject_id', 'ezcontentobject_version' ),
                $this->dbHandler->aliasedColumn( $query, 'language_mask', 'ezcontentobject_version' ),
                // Language IDs
                $this->dbHandler->aliasedColumn( $query, 'language_code', 'ezcontentobject_attribute' )
            )->from(
                $this->dbHandler->quoteTable( 'ezcontentobject_version' )
            )->leftJoin(
                $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' ),
                        $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_attribute' )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' ),
                        $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_attribute' )
                    )
                )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id', 'ezcontentobject_version' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            )->groupBy(
                $this->dbHandler->quoteColumn( 'id', 'ezcontentobject_version' ),
                $this->dbHandler->quoteColumn( 'language_code', 'ezcontentobject_attribute' )
            );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }
}
