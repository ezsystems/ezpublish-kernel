<?php

/**
 * File containing the Storage Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\Core\Persistence\Content\StorageHandler as ContentStorageHandler;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Handler for external storages.
 */
class StorageHandler extends ContentStorageHandler
{
    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @return mixed
     */
    public function copyFieldData(VersionInfo $versionInfo, Field $field, Field $originalField)
    {
        return $this->storageRegistry->getStorage($field->type)->copyLegacyField(
            $versionInfo,
            $field,
            $originalField,
            $this->context
        );
    }
}
