<?php

/**
 * File containing the FieldTypeRegistryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\Storage;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;
use eZ\Publish\SPI\Persistence\Content\StorageHandler;
use eZ\Publish\SPI\Persistence\Content\StorageHandlerRegistry;

class FieldTypeRegistryFactory
{
    /**
     * Returns storage field type registry.
     *
     * @param string $fieldTypeRegistryClass
     * @param \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory $fieldTypeCollectionFactory
     * @param \eZ\Publish\SPI\Persistence\Content\StorageHandlerRegistry $storageHandlerRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\StorageHandler $defaultStorageHandler
     *
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    public function buildFieldTypeRegistry(
        $fieldTypeRegistryClass,
        FieldTypeCollectionFactory $fieldTypeCollectionFactory,
        StorageHandlerRegistry $storageHandlerRegistry,
        StorageHandler $defaultStorageHandler
    ) {
        return new $fieldTypeRegistryClass(
            $fieldTypeCollectionFactory->getFieldTypes(),
            $storageHandlerRegistry,
            $defaultStorageHandler
        );
    }
}
