<?php
/**
 * File containing the abstract Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Url\UrlStorage;

use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 *
 */
abstract class Gateway extends StorageGateway
{
    /**
     * Stores a URL based on the given field data
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
     * Deletes external data for $fieldId in $versionNo
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return void
     */
    abstract public function deleteFieldData( $versionNo, $fieldId );
}
