<?php
/**
 * File containing the RelationStorage Converter class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Relation;
use eZ\Publish\Core\FieldType\GatewayBasedStorage,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Converter for Relation field type external storage
 * @TODO indroduce persistence layer (gateways)
 *
 */
class RelationStorage extends GatewayBasedStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $gateway = $this->getGateway( $context );
        return $gateway->storeFieldData( $versionInfo, $field );
    }

    /**
     * Populates $field value property based on the external data.
     * We don't need to query storage for this, as identical data is stored in data & externalData
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $field->value->externalData = $field->value->data;
    }

    /**
     * @param VersionInfo $versionInfo
     * @param array $fieldId
     * @param array $context
     * @return bool
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $gateway = $this->getGateway( $context );
        return $gateway->deleteFieldData( $versionInfo, $fieldIds, $context );
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
    }
}
