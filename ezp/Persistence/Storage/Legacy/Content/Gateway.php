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
     * Inserts a new content object.
     *
     * @param Content $content
     * @return int ID
     */
    abstract public function insertContentObject( Content $content );

    /**
     * Inserts a new version.
     *
     * @param Version $version
     * @return int ID
     */
    abstract public function insertVersion( Version $version );

    /**
     * Updates an existing version
     *
     * @param int|string $version
     * @param int $versionNo
     * @param int|string $userId
     * @return void
     */
    abstract public function updateVersion( $version, $versionNo, $userId );

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
     * Deletes relations to and from $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteRelations( $contentId );

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
     * Deletes the actual content object referred to by $contentId
     *
     * @param int $contentId
     * @return void
     */
    abstract public function deleteContent( $contentId );
}
