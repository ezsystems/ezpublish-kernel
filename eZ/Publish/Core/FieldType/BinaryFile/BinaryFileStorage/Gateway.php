<?php
/**
 * File containing the Gateway base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\Core\FieldType\StorageGateway;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the file reference in $field for $versionNo
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return void
     */
    abstract public function storeFileReference( VersionInfo $versionInfo, Field $field );

    /**
     * Returns the file reference data for the given $fieldId in $versionNo
     *
     * @param mixed $fieldId
     * @param int $versionNo
     * @return array|void
     */
    abstract public function getFileReferenceData( $fieldId, $versionNo );

    /**
     * Removes all file references for the given $fieldIds
     *
     * @param array $fieldIds
     * @return void
     */
    abstract public function removeFileReferences( array $fieldIds );

    /**
     * Removes a specific file reference for $fieldId and $versionId
     *
     * @param mixed $fieldId
     * @param int $versionNo
     * @return void
     */
    abstract public function removeFileReference( $fieldId, $versionNo );

    /**
     * Returns a map of files referenced by the given $fieldIds
     *
     * @param array $fieldIds
     * @return array
     */
    abstract public function getReferencedFiles( array $fieldIds );

    /**
     * Returns a map with the number of refereces each file from $files has
     *
     * @param array $files
     * @return array
     */
    abstract public function countFileReferences( array $files );
}
