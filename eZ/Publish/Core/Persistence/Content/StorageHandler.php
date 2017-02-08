<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Content;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

/**
 * Handler for external storages.
 */
class StorageHandler
{
    /**
     * Storage registry.
     *
     * @var \eZ\Publish\Core\Persistence\Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Array with database context.
     *
     * @var array
     */
    protected $context;

    /**
     * Creates a new storage handler.
     *
     * @param \eZ\Publish\Core\Persistence\Content\StorageRegistry $storageRegistry
     * @param array $context
     */
    public function __construct(StorageRegistry $storageRegistry, array $context)
    {
        $this->storageRegistry = $storageRegistry;
        $this->context = $context;
    }

    /**
     * Stores data from $field in its corresponding external storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @return mixed
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field)
    {
        return $this->storageRegistry->getStorage($field->type)->storeFieldData(
            $versionInfo,
            $field,
            $this->context
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Field $originalField
     * @return mixed
     */
    public function copyFieldData(VersionInfo $versionInfo, Field $field, Field $originalField)
    {
        return $this->storageRegistry->getStorage($field->type)->storeFieldData(
            $versionInfo,
            $field,
            $this->context
        );
    }

    /**
     * Fetches external data for $field from its corresponding external storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param Field $field
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field)
    {
        $storage = $this->storageRegistry->getStorage($field->type);
        if ($storage->hasFieldData()) {
            $storage->getFieldData($versionInfo, $field, $this->context);
        }
    }

    /**
     * Deletes data for field $ids from external storage of $fieldType.
     *
     * @param string $fieldType
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param mixed[] $ids
     */
    public function deleteFieldData($fieldType, VersionInfo $versionInfo, array $ids)
    {
        $this->storageRegistry->getStorage($fieldType)
            ->deleteFieldData($versionInfo, $ids, $this->context);
    }

    /**
     * Get StorageHandler context data.
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
