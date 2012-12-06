<?php
/**
 * File containing the abstract Relation Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Relation\RelationStorage;

use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 *
 */
abstract class Gateway extends StorageGateway
{
    /**
     * Stores a Relation based on the given field data
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return boolean
     */
    abstract public function storeFieldData( VersionInfo $versionInfo, Field $field );

    /**
     * Sets a loaded URL, if one is stored for the given field
     *
     * @param Field $field
     *
     * @return void
     */
    abstract public function getFieldData( Field $field );

    /**
     * Deletes stored external data
     *
     * @param VersionInfo $versionInfo
     * @param array $fieldId Array of field Ids
     * @param array $context
     *
     * @todo Remove $context, since it is inherent to the Gateway
     *
     * @return boolean
     */
    abstract public function deleteFieldData( VersionInfo $versionInfo, array $fieldId, array $context );
}
