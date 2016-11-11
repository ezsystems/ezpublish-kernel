<?php

/**
 * File containing the NullStorage class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Description of NullStorage.
 */
class NullStorage implements FieldStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return false;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::getFieldData()
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::deleteFieldData()
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        return true;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::hasFieldData()
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return false;
    }

    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage::getIndexData()
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return false;
    }

    /**
     * This method is used exclusively by Legacy Storage to copy external data of existing field in main language to
     * the untranslatable field not passed in create or update struct, but created implicitly in storage layer.
     *
     * By default the method falls back to the {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     * External storages implement this method as needed.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @param array $context
     *
     * @return null|bool Same as {@link \eZ\Publish\SPI\FieldType\FieldStorage::storeFieldData()}.
     */
    public function copyLegacyField(VersionInfo $versionInfo, Field $field, Field $originalField, array $context)
    {
        return;
    }
}
