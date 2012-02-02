<?php
/**
 * File containing the EzcDatabase content gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\Language,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\Field,
    ezp\Content as ContentDo,
    ezp\Content\Version as VersionDo,
    ezcQueryUpdate;

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
     * @var eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Creates a new gateway based on $db
     *
     * @param EzcDbHandler $db
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder $queryBuilder
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        EzcDbHandler $db,
        QueryBuilder $queryBuilder,
        CachingHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator )
    {
        $this->dbHandler = $db;
        $this->queryBuilder = $queryBuilder;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Get context definition for external storage layers
     *
     * @return array
     */
    public function getContext()
    {
        return array(
            'identifier' => 'LegacyStorage',
            'connection' => $this->dbHandler,
        );
    }

    /**
     * Inserts a new content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @return int ID
     */
    public function insertContentObject( CreateStruct $struct )
    {
        if ( isset( $struct->name['always-available'] ) )
        {
            $name = $struct->name[$struct->name['always-available']];
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
            $q->bindValue( 1, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $name )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclass_id' ),
            $q->bindValue( $struct->typeId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'section_id' ),
            $q->bindValue( $struct->sectionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'owner_id' ),
            $q->bindValue( $struct->ownerId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $struct->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'remote_id' ),
            $q->bindValue( $struct->remoteId )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $struct->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'published' ),
            $q->bindValue( $struct->published, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( Content::STATUS_DRAFT, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'language_mask' ),
            $q->bindValue(
                $this->generateLanguageMask(
                    $struct->fields, $struct->alwaysAvailable
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
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param boolean $alwaysAvailable
     * @return int
     */
    protected function generateLanguageMask( array $fields, $alwaysAvailable )
    {
        $languages = array();
        foreach ( $fields as $field )
        {
            if ( isset( $languages[$field->language] ) )
                continue;

            $languages[$field->language] = true;
        }

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
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param boolean $alwaysAvailable
     * @return int ID
     */
    public function insertVersion( Version $version, array $fields, $alwaysAvailable )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontentobject_version', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $version->versionNo, null, \PDO::PARAM_INT )
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
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $version->initialLanguageId, null, \PDO::PARAM_INT )
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
                    $fields, $alwaysAvailable
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
     * Updates an existing content in respect to $struct
     *
     * @param UpdateStruct $struct
     * @return void
     */
    public function updateContent( UpdateStruct $struct )
    {
        if ( isset( $struct->name['always-available'] ) )
        {
            $name = $struct->name[$struct->name['always-available']];
        }
        else
        {
            $name = '';
        }

        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $name )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $struct->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $struct->modified, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'owner_id' ),
            $q->bindValue( $struct->ownerId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'published' ),
            $q->bindValue( $struct->published, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $struct->id, null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Updates an existing version in respect to $struct
     *
     * @param UpdateStruct $struct
     * @return void
     */
    public function updateVersion( UpdateStruct $struct )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $struct->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $struct->modified, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $q->bindValue( $struct->id, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $struct->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Sets the status of the version identified by $contentId and $version to $status.
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
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $version, null, \PDO::PARAM_INT )
                )
            )
        );
        $statement = $q->prepare();
        $statement->execute();

        if ( (bool)$statement->rowCount() === false )
            return false;

        if ( $status !== VersionDo::STATUS_PUBLISHED )
        {
            return true;
        }

        // If the version's status is PUBLISHED, we set the content to published status as well
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( ContentDo::STATUS_PUBLISHED, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );
        $statement = $q->prepare();
        $statement->execute();

        return (bool)$statement->rowCount();
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
     * @param boolean $alwaysAvailable
     * @return void
     */
    public function updateField( Field $field, StorageFieldValue $value )
    {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $q = $this->dbHandler->createUpdateQuery();
        $this->setFieldUpdateValues( $q, $value );
        $q->where(
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
     * Sets update fields for $value on $q
     *
     * @param ezcQueryUpdate $q
     * @param StorageFieldValue $value
     * @return void
     */
    protected function setFieldUpdateValues( ezcQueryUpdate $q, StorageFieldValue $value  )
    {
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
        );
    }

    /**
     * Updates an existing, non-translatable field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     * @param Content $content
     * @return void
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        UpdateStruct $content )
    {
        // Note, no need to care for language_id here, since Content->$alwaysAvailable
        // cannot change on update
        $q = $this->dbHandler->createUpdateQuery();
        $this->setFieldUpdateValues( $q, $value );
        $q->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentclassattribute_id' ),
                    $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $q->bindValue( $content->id, null, \PDO::PARAM_INT )
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
     * @param string[] $translations
     * @return array
     */
    public function load( $contentId, $version, $translations = null )
    {
        $query = $this->queryBuilder->createFindQuery( $translations );
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
     * Loads data for the latest published version of the content identified by
     * $contentId
     *
     * @param mixed $contentId
     * @return array
     */
    public function loadLatestPublishedData( $contentId )
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
                    $this->dbHandler->quoteColumn( 'current_version', 'ezcontentobject' )
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
            $this->dbHandler->aliasedColumn( $query, 'language_code', 'ezcontentobject_attribute' ),
            // Content object names
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezcontentobject_name' ),
            $this->dbHandler->aliasedColumn( $query, 'content_translation', 'ezcontentobject_name' )
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
     * Sets the name for Content $contentId in version $version to $name in $language
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     * @return void
     */
    public function setName( $contentId, $version, $name, $language )
    {
        $language = $this->languageHandler->getByLocale( $language );

        // Is it an insert or an update ?
        $qSelect = $this->dbHandler->createSelectQuery();
        $qSelect->select(
            $qSelect->alias( $qSelect->expr->count( '*' ), 'count' ) )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_name' ) )
            ->where(
                $qSelect->expr->lAnd(
                    $qSelect->expr->eq( $this->dbHandler->quoteColumn( 'contentobject_id' ), $qSelect->bindValue( $contentId ) ),
                    $qSelect->expr->eq( $this->dbHandler->quoteColumn( 'content_version' ), $qSelect->bindValue( $version ) ),
                    $qSelect->expr->eq( $this->dbHandler->quoteColumn( 'content_translation' ), $qSelect->bindValue( $language->locale ) )
                )
            );
        $stmt = $qSelect->prepare();
        $stmt->execute();
        $res = $stmt->fetchAll( \PDO::FETCH_ASSOC );

        $insert = $res[0]['count'] == 0;
        if ( $insert )
        {
            $q = $this->dbHandler->createInsertQuery();
            $q->insertInto( $this->dbHandler->quoteTable( 'ezcontentobject_name' ) );
        }
        else
        {
            $q = $this->dbHandler->createUpdateQuery();
            $q->update( $this->dbHandler->quoteTable( 'ezcontentobject_name' ) )
                ->where(
                $q->expr->lAnd(
                    $q->expr->eq( $this->dbHandler->quoteColumn( 'contentobject_id' ), $q->bindValue( $contentId ) ),
                    $q->expr->eq( $this->dbHandler->quoteColumn( 'content_version' ), $q->bindValue( $version ) ),
                    $q->expr->eq( $this->dbHandler->quoteColumn( 'content_translation' ), $q->bindValue( $language->locale ) )
                )
            );
        }

        $q->set(
            $this->dbHandler->quoteColumn( 'contentobject_id' ),
            $q->bindValue( $contentId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'content_version' ),
            $q->bindValue( $version, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'language_id' ),
            $q->bindValue( $language->id, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'content_translation' ),
            $q->bindValue( $language->locale )
        )->set(
            $this->dbHandler->quoteColumn( 'real_translation' ),
            $q->bindValue( $language->locale )
        )->set(
            $this->dbHandler->quoteColumn( 'name' ),
            $q->bindValue( $name )
        );
        $q->prepare()->execute();
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
