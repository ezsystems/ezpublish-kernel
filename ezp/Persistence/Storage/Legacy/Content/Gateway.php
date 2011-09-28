<?php
/**
 * File containing the Content Gateway base class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Content,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\Field;

/**
 * Base class for contentg gateways
 */
abstract class Gateway
{
    /**
     * Get context definition for external storage layers
     *
     * @return array
     */
    abstract public function getContext();

    /**
     * Inserts a new content object.
     *
     * @param Content $content
     * @param \ezp\Persistence\Content\Field[] $fields
     * @return int ID
     */
    abstract public function insertContentObject( Content $content, array $fields );

    /**
     * Inserts a new version.
     *
     * @param Version $version;
     * @param \ezp\Persistence\Content\Field[] $fields
     * @param bool $alwaysAvailable
     * @return int ID
     */
    abstract public function insertVersion( Version $version, array $fields, $alwaysAvailable );


    /**
     * Updates an existing version
     *
     * @param int $version
     * @param int $versionNo
     * @return void
     */
    abstract public function updateVersion( $version, $versionNo );

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
    abstract public function setStatus( $contentId, $version, $status );

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
    abstract public function insertNewField( Content $content, Field $field, StorageFieldValue $value );

    /**
     * Updates an existing field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     * @return void
     */
    abstract public function updateField( Field $field, StorageFieldValue $value );

    /**
     * Updates an existing, non-translatable field
     *
     * @param Field $field
     * @param StorageFieldValue $value
     * @param Content $content
     * @return void
     */
    abstract public function updateNonTranslatableField(
        Field $field,
        StorageFieldValue $value,
        Content\UpdateStruct $content );

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
    abstract public function load( $contentId, $version, $translations = null );

    /**
     * Returns all version data for the given $contentId
     *
     * @param mixed $contentId
     * @return string[][]
     */
    abstract public function listVersions( $contentId );

    /**
     * Returns all IDs for locations that refer to $contentId
     *
     * @param int $contentId
     * @return int[]
     * @TODO This method does hardly belong here. Maybe put it into
     *       Location\Handler? But that hinders inter-operability.
     */
    abstract public function getAllLocationIds( $contentId );

    /**
     * Returns all field IDs of $contentId grouped by their type
     *
     * @param int $contentId
     * @return int[][]
     */
    abstract public function getFieldIdsByType( $contentId );

    /**
     * Deletes relations to and from $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteRelations( $contentId );

    /**
     * Deletes the field with the given $fieldId
     *
     * @param int $fieldId
     * @param int $version
     * @return void
     */
    abstract public function deleteField( $fieldId, $version );

    /**
     * Deletes all fields of $contentId in all versions
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteFields( $contentId );

    /**
     * Deletes all versions of $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteVersions( $contentId );

    /**
     * Deletes all names of $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteNames( $contentId );

    /**
     * Sets the content object name
     *
     * @param int $contentId
     * @param int $version
     * @param string $name
     * @param string $language
     * @return void
     */
    abstract public function setName( $contentId, $version, $name, $language );

    /**
     * Deletes the actual content object referred to by $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteContent( $contentId );

    /**
     * Loads data for the latest published version of the content identified by
     * $contentId
     *
     * @param mixed $contentId
     * @return array
     */
    abstract public function loadLatestPublishedData( $contentId );
}
