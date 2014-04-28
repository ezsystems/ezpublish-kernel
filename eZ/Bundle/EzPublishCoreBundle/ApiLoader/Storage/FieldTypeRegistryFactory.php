<?php
/**
 * File containing the FieldTypeRegistryFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader\Storage;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\FieldTypeCollectionFactory;

class FieldTypeRegistryFactory
{
    /**
     * Returns storage field type registry
     *
     * @param string $fieldTypeRegistryClass
     * @param \eZ\Bundle\EzPublishCoreBundle\ApiLoader\FieldTypeCollectionFactory $fieldTypeCollectionFactory
     *
     * @return \eZ\Publish\Core\Persistence\FieldTypeRegistry
     */
    public function buildFieldTypeRegistry( $fieldTypeRegistryClass, FieldTypeCollectionFactory $fieldTypeCollectionFactory )
    {
        return new $fieldTypeRegistryClass(
            $fieldTypeCollectionFactory->getFieldTypes()
        );
    }
}
