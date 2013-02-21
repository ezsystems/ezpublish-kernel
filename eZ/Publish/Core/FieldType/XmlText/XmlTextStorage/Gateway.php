<?php
/**
 * File containing the XmlText Gateway abstract class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText\XmlTextStorage;

use eZ\Publish\Core\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Abstract gateway class for XmlText type.
 * Handles data that is not directly included in raw XML value from the field (i.e. URLs)
 */
abstract class Gateway extends StorageGateway
{
    /**
     * Populates $field->value->externalData with external data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    abstract public function getFieldData( Field $field );

    /**
     * Stores data, external to XMLText type
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return boolean
     */
    abstract public function storeFieldData( VersionInfo $versionInfo, Field $field );
}
