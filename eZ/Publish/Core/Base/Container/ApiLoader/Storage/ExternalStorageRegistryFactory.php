<?php

/**
 * File containing the ExternalStorageRegistryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\Storage;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ExternalStorageRegistryFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Collection of external storage handlers for field types that need them.
     *
     * @var \Closure[]
     */
    protected $externalStorages = [];

    /**
     * Registers an external storage handler for a field type, identified by $fieldTypeAlias.
     * They are being registered as closures so that they will be lazy loaded.
     *
     * @param string $serviceId The external storage handler service Id
     * @param string $fieldTypeAlias The field type alias (e.g. "ezstring")
     */
    public function registerExternalStorageHandler($serviceId, $fieldTypeAlias)
    {
        $container = $this->container;
        $this->externalStorages[$fieldTypeAlias] = function () use ($container, $serviceId) {
            return $container->get($serviceId);
        };
    }

    /**
     * Returns registered external storage handlers for field types (as closures to be lazy loaded in the public API).
     *
     * @return \Closure[]
     */
    public function getExternalStorageHandlers()
    {
        return $this->externalStorages;
    }

    /**
     * Returns external storage registry.
     *
     * @param string $externalStorageRegistryClass
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    public function buildExternalStorageRegistry($externalStorageRegistryClass)
    {
        return new $externalStorageRegistryClass($this->getExternalStorageHandlers());
    }
}
