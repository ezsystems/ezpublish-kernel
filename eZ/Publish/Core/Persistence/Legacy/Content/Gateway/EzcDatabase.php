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
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo,
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
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $db
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder $queryBuilder
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler $languageHandler
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
            $q->bindValue( ContentInfo::STATUS_DRAFT, null, \PDO::PARAM_INT )
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
            if ( isset( $languages[$field->languageCode] ) )
                continue;

            $languages[$field->languageCode] = true;
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
     * @param \eZ\Publish\SPI\Persistence\Content\Version $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     * @param boolean $alwaysAvailable
     * @return int ID
     */
    public function insertVersion( VersionInfo $versionInfo, array $fields, $alwaysAvailable )
    {
        $q = $this->dbHandler->createInsertQuery();
        $q->insertInto(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'id' ),
            $this->dbHandler->getAutoIncrementValue( 'ezcontentobject_version', 'id' )
        )->set(
            $this->dbHandler->quoteColumn( 'version' ),
            $q->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $versionInfo->modificationDate, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'creator_id' ),
            $q->bindValue( $versionInfo->creatorId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'created' ),
            $q->bindValue( $versionInfo->creationDate, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( $versionInfo->status, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $this->languageHandler->getByLocale( $versionInfo->initialLanguageCode )->id, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentobject_id' ),
            $q->bindValue( $versionInfo->contentId, null, \PDO::PARAM_INT )
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
     * Updates an existing content identified by $contentId in respect to $struct
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $struct
     * @return void
     */
    public function updateContent( $contentId, MetadataUpdateStruct $struct )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update( $this->dbHandler->quoteTable( 'ezcontentobject' ) );

        if ( isset( $struct->name ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'name' ),
                $q->bindValue( $struct->name, null, \PDO::PARAM_STR )
            );
        }
        if ( isset( $struct->mainLanguageId ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'initial_language_id' ),
                $q->bindValue( $struct->mainLanguageId, null, \PDO::PARAM_INT )
            );
        }
        if ( isset( $struct->modificationDate ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'modified' ),
                $q->bindValue( $struct->modificationDate, null, \PDO::PARAM_INT )
            );
        }
        if ( isset( $struct->ownerId ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'owner_id' ),
                $q->bindValue( $struct->ownerId, null, \PDO::PARAM_INT )
            );
        }
        if ( isset( $struct->publicationDate ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'published' ),
                $q->bindValue( $struct->publicationDate, null, \PDO::PARAM_INT )
            );
        }
        if ( isset( $struct->remoteId ) )
        {
            $q->set(
                $this->dbHandler->quoteColumn( 'remote_id' ),
                $q->bindValue( $struct->remoteId, null, \PDO::PARAM_STR )
            );
        }

        $q->where(
            $q->expr->eq(
                $this->dbHandler->quoteColumn( 'id' ),
                $q->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );
        $q->prepare()->execute();

        // Handle alwaysAvailable flag update separately as it's a more complex task and has impact on several tables
        if ( isset( $struct->alwaysAvailable ) )
        {
            $this->updateAlwaysAvailableFlag( $contentId, $struct->alwaysAvailable );
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $struct
     * @return void
     */
    public function updateVersion( $contentId, $versionNo, UpdateStruct $struct )
    {
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->set(
            $this->dbHandler->quoteColumn( 'initial_language_id' ),
            $q->bindValue( $struct->initialLanguageId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'modified' ),
            $q->bindValue( $struct->modificationDate, null, \PDO::PARAM_INT )
        )->where(
            $q->expr->lAnd(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                ),
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $q->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );
        $q->prepare()->execute();
    }

    /**
     * Updates "always available" flag for content identified by $contentId, in respect to $isAlwaysAvailable.
     *
     * @param int $contentId
     * @param bool $newAlwaysAvailable New "always available" value
     * @todo This needs to be done within a transaction
     */
    public function updateAlwaysAvailableFlag( $contentId, $newAlwaysAvailable )
    {
        // We will need to know some info on the current language mask to update the flag everywhere needed
        $langMaskInfo = $this->getLanguageMaskInfo( $contentId );

        // Only update if old and new flags differs
        if ( $langMaskInfo['always_available'] == $newAlwaysAvailable )
            return;

        /*
         * alwaysAvailable bit field value is 1 in language mask.
         * Thanks to the XOR (^) operator, alwaysAvailable bit field will be the exact opposite
         * of the previous one in $newLanguageMask.
         */
        $newLanguageMask = $langMaskInfo['language_mask'] ^ 1;
        $q = $this->dbHandler->createUpdateQuery();
        $q
            ->update( $this->dbHandler->quoteTable( 'ezcontentobject' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'language_mask' ),
                $q->bindValue( $newLanguageMask, null, \PDO::PARAM_INT )
            )
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );
        $q->prepare()->execute();

        // Now we need to update ezcontentobject_name
        $versionNo = $langMaskInfo['current_version'];
        $qName = $this->dbHandler->createUpdateQuery();
        $qName
            ->update( $this->dbHandler->quoteTable( 'ezcontentobject_name' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'language_id' ),
                $qName->bindValue( $newLanguageMask, null, \PDO::PARAM_INT )
            )
            ->where(
                $qName->expr->lAnd(
                    $qName->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id' ),
                        $qName->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $qName->expr->eq(
                        $this->dbHandler->quoteColumn( 'version' ),
                        $qName->bindValue( $versionNo, null, \PDO::PARAM_INT )
                    )
                )
            );
        $qName->prepare()->execute();

        // Now update ezcontentobject_attribute for current version
        $qAttr = $this->dbHandler->createUpdateQuery();
        $qAttr
            ->update( $this->dbHandler->quoteTable( 'ezcontentobject_attribute' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'language_id' ),
                $qAttr->bindValue( $newLanguageMask, null, \PDO::PARAM_INT )
            )
            ->where(
                $qAttr->expr->lAnd(
                    $qAttr->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id' ),
                        $qAttr->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $qAttr->expr->eq(
                        $this->dbHandler->quoteColumn( 'version' ),
                        $qAttr->bindValue( $versionNo, null, \PDO::PARAM_INT )
                    )
                )
            );
        $qAttr->prepare()->execute();
    }

    /**
     * Returns a hash containing information on language mask for content identified by $contentId.
     * Hash keys include:
     *  - current_version (Current version number for content, might be needed to update language mask everywhere)
     *  - language_mask (Current language mask)
     *  - initial_language_id
     *  - main_language_code
     *  - always_available
     *
     * @param int $contentId
     * @return array
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException Thrown if content cannot be found
     */
    private function getLanguageMaskInfo( $contentId )
    {
        $row = $this->loadContentInfo( $contentId );
        return array(
            'current_version'       => (int)$row['current_version'],
            'language_mask'         => $row['language_mask'],
            'initial_language_id'   => (int)$row['initial_language_id'],
            'main_language_code'    => $row['main_language_code'],
            'always_available'      => $row['always_available']
        );
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

        if ( $status !== APIVersionInfo::STATUS_PUBLISHED )
        {
            return true;
        }

        // If the version's status is PUBLISHED, we set the content to published status as well
        $q = $this->dbHandler->createUpdateQuery();
        $q->update(
            $this->dbHandler->quoteTable( 'ezcontentobject' )
        )->set(
            $this->dbHandler->quoteColumn( 'status' ),
            $q->bindValue( ContentInfo::STATUS_PUBLISHED, null, \PDO::PARAM_INT )
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
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
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
            $q->bindValue( $content->contentInfo->contentId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'contentclassattribute_id' ),
            $q->bindValue( $field->fieldDefinitionId, null, \PDO::PARAM_INT )
        )->set(
            $this->dbHandler->quoteColumn( 'data_type_string' ),
            $q->bindValue( $field->type )
        )->set(
            $this->dbHandler->quoteColumn( 'language_code' ),
            $q->bindValue( $field->languageCode )
            // @todo Deal with setting language_id ( needs to include always available flag if that is the case -
            //       Eg: eg: eng-US can be either 2 or 3, see fixture data )
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
                    $field->languageCode,
                    $content->contentInfo->isAlwaysAvailable
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
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param int $contentId
     * @return void
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        $contentId )
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
                    $q->bindValue( $contentId, null, \PDO::PARAM_INT )
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
            $row['ezcontentobject_always_available'] = $this->languageMaskGenerator->isAlwaysAvailable( $row['ezcontentobject_version_language_mask'] );
            $row['ezcontentobject_main_language_code'] = $this->languageHandler->getById( $row['ezcontentobject_initial_language_id'] )->languageCode;
            $row['ezcontentobject_version_languages'] = $this->languageMaskGenerator->extractLanguageIdsFromMask( $row['ezcontentobject_version_language_mask'] );
            $row['ezcontentobject_version_initial_language_code'] = $this->languageHandler->getById( $row['ezcontentobject_version_initial_language_id'] )->languageCode;
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
            $row['ezcontentobject_always_available'] = $this->languageMaskGenerator->isAlwaysAvailable( $row['ezcontentobject_version_language_mask'] );
            $row['ezcontentobject_main_language_code'] = $this->languageHandler->getById( $row['ezcontentobject_initial_language_id'] )->languageCode;
            $row['ezcontentobject_version_languages'] = $this->languageMaskGenerator->extractLanguageIdsFromMask( $row['ezcontentobject_version_language_mask'] );
            $row['ezcontentobject_version_initial_language_code'] = $this->languageHandler->getById( $row['ezcontentobject_version_initial_language_id'] )->languageCode;
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Loads info for content identified by $contentId.
     * Will basically return a hash containing all field values for ezcontentobject table plus some additional keys:
     *  - always_available => Boolean indicating if content's language mask contains alwaysAvailable bit field
     *  - main_language_code => Language code for main (initial) language. E.g. "eng-GB"
     *
     * @param int $contentId
     * @return array
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function loadContentInfo( $contentId )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q
            ->select( '*' )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject' ) )
            ->where(
                $q->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $q->bindValue( $contentId )
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        if ( empty( $rows ) )
            throw new NotFound( 'content', $contentId );

        $contentInfo = $rows[0];
        $contentInfo['always_available'] = $this->languageMaskGenerator->isAlwaysAvailable( $contentInfo['language_mask'] );
        $contentInfo['main_language_code'] = $this->languageHandler->getById( $contentInfo['initial_language_id'] )->languageCode;
        return $contentInfo;
    }

    /**
     * Loads version info for content identified by $contentId and $versionNo.
     * Will basically return a hash containing all field values from ezcontentobject_version table plus following keys:
     *  - names => Hash of content object names. Key is the language code, value is the name.
     *  - languages => Hash of language ids. Key is the language code (e.g. "eng-GB"), value is the language numeric id without the always available bit.
     *  - initial_language_code => Language code for initial language in this version.
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return array
     */
    public function loadVersionInfo( $contentId, $versionNo )
    {
        $q = $this->dbHandler->createSelectQuery();
        $q
            ->select( '*' )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_version' ) )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id' ),
                        $q->bindValue( $contentId )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteColumn( 'version' ),
                        $q->bindValue( $versionNo )
                    )
                )
            );
        $stmt = $q->prepare();
        $stmt->execute();
        $rows = $stmt->fetchAll( \PDO::FETCH_ASSOC );
        if ( empty( $rows ) )
            throw new NotFound( 'content', $contentId );
        $row = $rows[0];

        // Now load content object names
        $qName = $this->dbHandler->createSelectQuery();
        $qName
            ->select( '*' )
            ->from( $this->dbHandler->quoteTable( 'ezcontentobject_name' ) )
            ->where(
                $q->expr->lAnd(
                    $q->expr->eq(
                        $this->dbHandler->quoteColumn( 'contentobject_id' ),
                        $q->bindValue( $contentId )
                    ),
                    $q->expr->eq(
                        $this->dbHandler->quoteColumn( 'content_version' ),
                        $q->bindValue( $versionNo )
                    )
                )
            );
        $stmt = $qName->prepare();
        $stmt->execute();
        $row['names'] = $stmt->fetchAll( \PDO::FETCH_ASSOC );

        // Get languages for version
        $row['languages'] = $this->languageMaskGenerator->extractLanguageIdsFromMask( $row['language_mask'] );

        // Get initial language code
        $row['initial_language_code'] = $this->languageHandler->getById( $row['initial_language_id'] )->languageCode;

        return $row;
    }

    /**
     * Returns data for all versions with given status created by the given $userId
     *
     * @param $userId
     * @param int $status
     *
     * @return string[][]
     */
    public function listVersionsForUser( $userId, $status = VersionInfo::STATUS_DRAFT )
    {
        $query = $this->queryBuilder->createVersionFindQuery();
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'status', 'ezcontentobject_version' ),
                    $query->bindValue( $status, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'creator_id', 'ezcontentobject_version' ),
                    $query->bindValue( $userId, null, \PDO::PARAM_INT )
                )
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
     * Returns all version data for the given $contentId
     *
     * @param mixed $contentId
     * @return string[][]
     */
    public function listVersions( $contentId )
    {
        $query = $this->queryBuilder->createVersionFindQuery();
        $query->where(
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
     * Returns last version number for content identified by $contentId
     *
     * @param int $contentId
     *
     * @return int
     */
    public function getLastVersionNumber( $contentId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->max( $this->dbHandler->quoteColumn( 'version' ) )
        )->from( $this->dbHandler->quoteTable( 'ezcontentobject_version' )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'contentobject_id' ),
                $query->bindValue( $contentId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
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
     * Returns all field IDs of $contentId grouped by their type.
     * If $versionNo is set only field IDs for that version are returned.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return int[][]
     */
    public function getFieldIdsByType( $contentId, $versionNo = null )
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

        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            );
        }

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
     * Deletes relations to and from $contentId.
     * If $versionNo is set only relations for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return void
     */
    public function deleteRelations( $contentId, $versionNo = null )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable( 'ezcontentobject_link' )
        );

        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'from_contentobject_id' ),
                        $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn( 'from_contentobject_version' ),
                        $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                    )
                )
            );
        }
        else
        {
            $query->where(
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
        }

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
     * Deletes all fields of $contentId in all versions.
     * If $versionNo is set only fields for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return void
     */
    public function deleteFields( $contentId, $versionNo = null )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_attribute' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes all versions of $contentId.
     * If $versionNo is set only that version is deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return void
     */
    public function deleteVersions( $contentId, $versionNo = null )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_version' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'version' ),
                    $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();
    }

    /**
     * Deletes all names of $contentId.
     * If $versionNo is set only names for that version are deleted.
     *
     * @param int $contentId
     * @param int|null $versionNo
     *
     * @return void
     */
    public function deleteNames( $contentId, $versionNo = null )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom( 'ezcontentobject_name' )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'contentobject_id' ),
                    $query->bindValue( $contentId, null, \PDO::PARAM_INT )
                )
            );

        if ( isset( $versionNo ) )
        {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'content_version' ),
                    $query->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            );
        }

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
                    $qSelect->expr->eq( $this->dbHandler->quoteColumn( 'content_translation' ), $qSelect->bindValue( $language->languageCode ) )
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
                    $q->expr->eq( $this->dbHandler->quoteColumn( 'content_translation' ), $q->bindValue( $language->languageCode ) )
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
            $q->bindValue( $language->languageCode )
        )->set(
            $this->dbHandler->quoteColumn( 'real_translation' ),
            $q->bindValue( $language->languageCode )
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
