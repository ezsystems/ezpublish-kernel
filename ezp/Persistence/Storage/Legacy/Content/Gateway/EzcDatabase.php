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
    ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
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
     * Query builder.
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Language mask generator
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new gateway based on $db
     *
     * @param EzcDbHandler $db
     * @param \ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder $queryBuilder
     * @param \ezp\Persistence\Storage\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        EzcDbHandler $db,
        EzcDatabase\QueryBuilder $queryBuilder,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->dbHandler             = $db;
        $this->queryBuilder          = $queryBuilder;
        $this->languageMaskGenerator = $languageMaskGenerator;
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
        if ( is_array( $content->name ) && isset( $content->name['always-available'] ) )
        {
            $name = $content->name[$content->name['always-available']];
        }
        else
        {
            $name = '';
        }

        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontentobject', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'current_version' ),
            $q->bindValue( $content->currentVersionNo, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $name )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclass_id' ),
            $q->bindValue( $content->typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'section_id' ),
            $q->bindValue( $content->sectionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'owner_id' ),
            $q->bindValue( $content->ownerId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $content->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'remote_id' ),
            $q->bindValue( $content->remoteId )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $q->bindValue(
                $this->generateLanguageMask(
                    $content->version, $content->alwaysAvailable
                ),
                null,
                \PDO::PARAM_INT
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcontentobject', 'id' )
        );
    }

    /**
     * Generates a language mask for $version
     *
     * @param \ezp\Persistence\Content\Version $version
     * @param bool $alwaysAvailable
     * @return int
     */
    protected function generateLanguageMask( Version $version, $alwaysAvailable )
    {
        $languages = array_map(
            function ( Field $field )
            {
                return $field->language;
            },
            $version->fields
        );
        if ( $alwaysAvailable )
        {
            $languages['always-available'] = true;
        }
        return $this->languageMaskGenerator->generateLanguageMask( $languages );
    }

    /**
     * Inserts a new version.
     *
     * @param Version $version
     * @param bool $alwaysAvailable
     * @return int ID
     */
    public function insertVersion( Version $version, $alwaysAvailable )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontentobject_version', 'id' )
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
            $q->bindValue( $version->status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_id' ),
            $q->bindValue( $version->contentId, null, \PDO::PARAM_INT )
        )->set(
            // As described in field mapping document
            $this->dbHandler->quoteColumn( 'workflow_event_pos' ),
            $q->bindValue( 0, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $q->bindValue(
                $this->generateLanguageMask(
                    $version, $alwaysAvailable
                ),
                null,
                \PDO::PARAM_INT
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcontentobject_version', 'id' )
        );
    }

    /**
     * Updates an existing version
     *
     * @param int $version
     * @param int $versionNo
     * @return void
     */
    public function updateVersion( $version, $versionNo )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $versionNo, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( time(), null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $version, null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     *
     * @param int $contentId
     * @param int $version
     * @param int $status
     * @return boolean
     */
    public function setStatus( $contentId, $version, $status )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( $status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( time(), null, \PDO::PARAM_INT )
        )->where( $q->expr->lAnd(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id' ),
                $q->bindValue( $contentId, null, \PDO::PARAM_INT )
            ),
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'version' ),
                $q->bindValue( $version, null, \PDO::PARAM_INT )
            )
        ) );
        $statement = $q->prepare();
        $statement->execute();

        return (bool) $statement->rowCount();
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
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontentobject_attribute', 'id' )
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
        )->set(
            $this->dbHandler->quoteColumn( 'language_id' ),
            $q->bindValue(
                $this->languageMaskGenerator->generateLanguageIndicator(
                    $field->language,
                    $content->alwaysAvailable
                ),
                null,
                \PDO::PARAM_INT
            )
        );

        $stmt = $q->prepare();
        $stmt->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezcontentobject_attribute', 'id' )
        );
    }

    /**
     * Updates an existing field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     * @param bool $alwaysAvailable
     * @return void
     */
    public function updateField( Field $field, StorageFieldValue $value )
    {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable 
        // cannot change on update
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        )->set(
            $this->dbHandler->quoteColumn( 'data_float' ),
            $q->bindValue( $value->dataFloat )
        )->set(
            $this->dbHandler->quoteColumn( 'data_int' ),
            $q->bindValue( $value->dataInt, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'data_text' ),
            $q->bindValue( $value->dataText )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_int' ),
            $q->bindValue( $value->sortKeyInt, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'sort_key_string' ),
            $q->bindValue( $value->sortKeyString )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $field->id, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $field->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );
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
        $query = $this->queryBuilder->createFindQuery();
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id', 'ezcontentobject' ),
                    $query->bindValue( $contentId )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'version', 'ezcontentobject_version' ),
                    $query->bindValue( $version )
                )
            )
        );
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

    /**
     * Returns all IDs for locations that refer to $contentId
     *
     * @param int $contentId
     * @return int[]
     * @TODO This method does hardly belong here. Maybe put it into 
     *       Location\Handler? But that hinders inter-operability.
     */
    public function getAllLocationIds( $contentId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( 'node_id' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_tree' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_COLUMN );
    }

    /**
     * Returns all field IDs of $contentId grouped by their type
     *
     * @param int $contentId
     * @return int[][]
     */
    public function getFieldIdsByType( $contentId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->quoteColumn( 'data_type_string' )
        )->from(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $result = array();
        foreach ( $statement->fetchAll() as $row )
        {
            if ( !isset( $result[$row['data_type_string']] ) )
            {
                $result[$row['data_type_string']] = array();
            }
            $result[$row['data_type_string']][] = (int)$row['id'];
        }
        return $result;
    }

    /**
     * Deletes relations to and from $contentId
     *
     * @param int $contentId
     * @return void
     */
    public function deleteRelations( $contentId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentobject_link' )
        )->where(
            $query->expr->lOr(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'from_contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'to_contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes the field with the given $fieldId
     *
     * @param int $fieldId
     * @param int $version
     * @return void
     */
    public function deleteField( $fieldId, $version )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentobject_attribute' )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $fieldId, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $query->bindValue( $version, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes all fields of $contentId in all versions
     *
     * @param int $contentId
     * @return void
     */
    public function deleteFields( $contentId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_attribute' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes all versions of $contentId
     *
     * @param int $contentId
     * @return void
     */
    public function deleteVersions( $contentId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_version' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes all names of $contentId
     *
     * @param int $contentId
     * @return void
     */
    public function deleteNames( $contentId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_name' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes the actual content object referred to by $contentId
     *
     * @param int $contentId
     * @return void
     */
    public function deleteContent( $contentId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        $statement = $query->prepare();
        $statement->execute();
    }
}
