<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

/**
 * Handler for Field external storages for.
 */
interface StorageHandler
{
    /**
     * Write external data for field into external storage.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     * @return mixed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field);

    /**
     * Copy external data from $originalField into $field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     */
    public function copyFieldData(VersionInfo $versionInfo, Field $field, Field $originalField);

    /**
     * Fetch external data for $field from its corresponding external storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field);

    /**
     * Delete data for field $ids from external storage of $fieldType.
     *
     * @param string $fieldType
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param mixed[] $ids
     */
    public function deleteFieldData($fieldType, VersionInfo $versionInfo, array $ids);
}
