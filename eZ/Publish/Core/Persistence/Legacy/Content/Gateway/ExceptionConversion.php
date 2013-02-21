<?php
/**
 * File containing the Content Gateway base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * Base class for content gateways
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Get context definition for external storage layers
     *
     * @return array
     */
    public function getContext()
    {
        try
        {
            return $this->innerGateway->getContext();
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return int ID
     */
    public function insertContentObject( CreateStruct $struct, $currentVersionNo = 1 )
    {
        try
        {
            return $this->innerGateway->insertContentObject( $struct, $currentVersionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new version.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $fields
     *
     * @return int ID
     */
    public function insertVersion( VersionInfo $versionInfo, array $fields )
    {
        try
        {
            return $this->innerGateway->insertVersion( $versionInfo, $fields );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates an existing content identified by $contentId in respect to $struct
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $struct
     *
     * @return void
     */
    public function updateContent( $contentId, MetadataUpdateStruct $struct )
    {
        try
        {
            return $this->innerGateway->updateContent( $contentId, $struct );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates version $versionNo for content identified by $contentId, in respect to $struct
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $struct
     *
     * @return void
     */
    public function updateVersion( $contentId, $versionNo, UpdateStruct $struct )
    {
        try
        {
            return $this->innerGateway->updateVersion( $contentId, $versionNo, $struct );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates "always available" flag for content identified by $contentId, in respect to $alwaysAvailable.
     *
     * @param int $contentId
     * @param boolean $newAlwaysAvailable New "always available" value
     */
    public function updateAlwaysAvailableFlag( $contentId, $newAlwaysAvailable )
    {
        try
        {
            return $this->innerGateway->updateAlwaysAvailableFlag( $contentId, $newAlwaysAvailable );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     *
     * @param int $contentId
     * @param int $version
     * @param int $status
     *
     * @return boolean
     */
    public function setStatus( $contentId, $version, $status )
    {
        try
        {
            return $this->innerGateway->setStatus( $contentId, $version, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new field.
     *
     * Only used when a new field is created (i.e. a new object or a field in a
     * new language!). After that, field IDs need to stay the same, only the
     * version number changes.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     *
     * @return int ID
     */
    public function insertNewField( Content $content, Field $field, StorageFieldValue $value )
    {
        try
        {
            return $this->innerGateway->insertNewField( $content, $field, $value );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts an existing field.
     *
     * Used to insert a field with an exsting ID but a new version number.
     *
     * @param Content $content
     * @param Field $field
     * @param StorageFieldValue $value
     *
     * @return void
     */
    public function insertExistingField( Content $content, Field $field, StorageFieldValue $value )
    {
        try
        {
            return $this->innerGateway->insertExistingField( $content, $field, $value );
        }
        catch ( \ezcDbException $e )
        {
            throw $e;
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw $e;
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates an existing field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     *
     * @return void
     */
    public function updateField( Field $field, StorageFieldValue $value )
    {
        try
        {
            return $this->innerGateway->updateField( $field, $value );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Updates an existing, non-translatable field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param int $contentId
     *
     * @return void
     */
    public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        $contentId )
    {
        try
        {
            return $this->innerGateway->updateNonTranslatableField( $field, $value, $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for a content object
     *
     * Returns an array with the relevant data.
     *
     * @param mixed $contentId
     * @param mixed $version
     * @param string[] $translations
     *
     * @return array
     */
    public function load( $contentId, $version, $translations = null )
    {
        try
        {
            return $this->innerGateway->load( $contentId, $version, $translations );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads info for content identified by $contentId.
     * Will basically return a hash containing all field values for ezcontentobject table plus following keys:
     *  - always_available => Boolean indicating if content's language mask contains alwaysAvailable bit field
     *  - main_language_code => Language code for main (initial) language. E.g. "eng-GB"
     *
     * @param int $contentId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return array
     */
    public function loadContentInfo( $contentId )
    {
        try
        {
            return $this->innerGateway->loadContentInfo( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->loadVersionInfo( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns data for all versions with given status created by the given $userId
     *
     * @param int $userId
     * @param int $status
     *
     * @return string[][]
     */
    public function listVersionsForUser( $userId, $status = VersionInfo::STATUS_DRAFT )
    {
        try
        {
            return $this->innerGateway->listVersionsForUser( $userId, $status );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns all version data for the given $contentId
     *
     * @param mixed $contentId
     *
     * @return string[][]
     */
    public function listVersions( $contentId )
    {
        try
        {
            return $this->innerGateway->listVersions( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->getLastVersionNumber( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Returns all IDs for locations that refer to $contentId
     *
     * @param int $contentId
     *
     * @return int[]
     */
    public function getAllLocationIds( $contentId )
    {
        try
        {
            return $this->innerGateway->getAllLocationIds( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->getFieldIdsByType( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->deleteRelations( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the field with the given $fieldId
     *
     * @param int $fieldId
     * @param int $version
     *
     * @return void
     */
    public function deleteField( $fieldId, $version )
    {
        try
        {
            return $this->innerGateway->deleteField( $fieldId, $version );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->deleteFields( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->deleteVersions( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
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
        try
        {
            return $this->innerGateway->deleteNames( $contentId, $versionNo );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Sets the content object name
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     *
     * @return void
     */
    public function setName( $contentId, $version, $name, $language )
    {
        try
        {
            return $this->innerGateway->setName( $contentId, $version, $name, $language );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the actual content object referred to by $contentId
     *
     * @param int $contentId
     *
     * @return void
     */
    public function deleteContent( $contentId )
    {
        try
        {
            return $this->innerGateway->deleteContent( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data for the latest published version of the content identified by
     * $contentId
     *
     * @param mixed $contentId
     *
     * @return array
     */
    public function loadLatestPublishedData( $contentId )
    {
        try
        {
            return $this->innerGateway->loadLatestPublishedData( $contentId );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data of related to/from $contentId
     *
     * @param int $contentId
     * @param int $contentVersionNo
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    public function loadRelations( $contentId, $contentVersionNo = null, $relationType = null )
    {
        try
        {
            return $this->innerGateway->loadRelations( $contentId, $contentVersionNo, $relationType );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Loads data of related to/from $contentId
     *
     * @param int $contentId
     * @param boolean $reverse Reverse relation, default false
     * @param int $contentVersionNo
     * @param int $relationType
     *
     * @return mixed[][] Content data, array structured like {@see \eZ\Publish\Core\Persistence\Legacy\Content\Gateway::load()}
     */
    public function loadReverseRelations( $contentId, $relationType = null )
    {
        try
        {
            return $this->innerGateway->loadReverseRelations( $contentId, $relationType );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Deletes the relation with the given $relationId.
     *
     * @param int $relationId
     * @param int $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     *
     * @return void
     */
    public function deleteRelation( $relationId, $type )
    {
        try
        {
            return $this->innerGateway->deleteRelation( $relationId, $type );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }

    /**
     * Inserts a new relation database record
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $createStruct
     *
     * @return int ID the inserted ID
     */
    public function insertRelation( RelationCreateStruct $struct )
    {
        try
        {
            return $this->innerGateway->insertRelation( $struct );
        }
        catch ( \ezcDbException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
        catch ( \PDOException $e )
        {
            throw new \RuntimeException( 'Database error', 0, $e );
        }
    }
}
