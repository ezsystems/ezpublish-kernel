<?php
/**
 * File containing the XmlTextStorage class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\XmlText;

use eZ\Publish\Core\FieldType\GatewayBasedStorage,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

class XmlTextStorage extends GatewayBasedStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $this->getGateway( $context )->storeFieldData( $versionInfo, $field );
    }

    /**
     * Modifies $field if needed, using external data (like for Urls)
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $this->getGateway( $context )->getFieldData( $field );
    }

    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
    }
}
