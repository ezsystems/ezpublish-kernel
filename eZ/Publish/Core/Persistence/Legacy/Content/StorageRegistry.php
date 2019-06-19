<?php

/**
 * File containing the StorageRegistry class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content;

use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\Core\FieldType\NullStorage;

/**
 * Registry for external storages.
 */
class StorageRegistry
{
    /**
     * Map of storages.
     *
     * @var array
     */
    protected $storageMap = [];

    /**
     * Create field storage registry with converter map.
     *
     * In $storageMap a mapping of field type names to object / callable is
     * expected, in case of callable factory it should return the storage object.
     * The object is used to store/restore/delete/… data in external storage
     * (e.g.another database or a web service). The storage object must comply to
     * the {@link \eZ\Publish\SPI\FieldType\FieldStorage} interface.
     *
     * @param array $storageMap A map where key is field type name, and value is
     *              a callable factory to get FieldStorage OR FieldStorage object
     */
    public function __construct(array $storageMap)
    {
        foreach ($storageMap as $typeName => $storage) {
            $this->register($typeName, $storage);
        }
    }

    /**
     * Register $storage for $typeName.
     *
     * @param string $typeName
     * @param mixed $storage Callable or FieldStorage
     */
    public function register($typeName, $storage)
    {
        $this->storageMap[$typeName] = $storage;
    }

    /**
     * Returns the storage for $typeName.
     *
     * @param string $typeName
     *
     * @throws \RuntimeException When type is neither FieldStorage instance or callable factory
     *
     * @return \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function getStorage($typeName)
    {
        if (!isset($this->storageMap[$typeName])) {
            $this->storageMap[$typeName] = new NullStorage();
        } elseif (!$this->storageMap[$typeName] instanceof FieldStorage) {
            if (!is_callable($this->storageMap[$typeName])) {
                throw new \RuntimeException("FieldStorage '$typeName' is neither callable or instance");
            }

            $factory = $this->storageMap[$typeName];
            $this->storageMap[$typeName] = call_user_func($factory);
        }

        return $this->storageMap[$typeName];
    }
}
