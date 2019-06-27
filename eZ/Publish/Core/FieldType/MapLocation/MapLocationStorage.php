<?php

/**
 * File containing the MapLocationStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\MapLocation;

use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Storage for the MapLocation field type.
 */
class MapLocationStorage extends GatewayBasedStorage
{
    /** @var \eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway */
    protected $gateway;

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return mixed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return $this->gateway->storeFieldData($versionInfo, $field);
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $this->gateway->getFieldData($versionInfo, $field);
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        $this->gateway->deleteFieldData($versionInfo, $fieldIds);
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     * @return \eZ\Publish\SPI\Search\Field[]|null
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return is_array($field->value->externalData) ? $field->value->externalData['address'] : null;
    }
}
