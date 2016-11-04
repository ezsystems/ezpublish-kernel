<?php

/**
 * File containing the FieldTypeRegistryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\Storage;

use eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory;

class FieldTypeRegistryFactory
{
    /**
     * Returns storage field type registry.
     *
     * @param string $fieldTypeRegistryClass
     * @param \eZ\Publish\Core\Base\Container\ApiLoader\FieldTypeCollectionFactory $fieldTypeCollectionFactory
     *
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    public function buildFieldTypeRegistry($fieldTypeRegistryClass, FieldTypeCollectionFactory $fieldTypeCollectionFactory)
    {
        return new $fieldTypeRegistryClass(
            $fieldTypeCollectionFactory->getFieldTypes()
        );
    }
}
