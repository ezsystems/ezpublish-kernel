<?php

/**
 * File containing the FieldTypeCollectionFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class FieldTypeNameableCollectionFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Collection of fieldTypes, lazy loaded via a closure.
     *
     * @var \Closure[]
     */
    protected $nameableFieldTypeClosures = array();

    /**
     * Registers an eZ Publish field type.
     * Field types are being registered as a closure so that they will be lazy loaded.
     *
     * @param string $nameableFieldTypeServiceId The field type nameable service Id
     * @param string $fieldTypeAlias The field type alias (e.g. "ezstring")
     */
    public function registerNameableFieldType($nameableFieldTypeServiceId, $fieldTypeAlias)
    {
        $container = $this->container;
        $this->nameableFieldTypeClosures[$fieldTypeAlias] = function () use ($container, $nameableFieldTypeServiceId) {
            return $container->get($nameableFieldTypeServiceId);
        };
    }

    /**
     * Returns registered field types (as closures to be lazy loaded in the public API).
     *
     * @return \Closure[]
     */
    public function getNameableFieldTypes()
    {
        return $this->nameableFieldTypeClosures;
    }
}
