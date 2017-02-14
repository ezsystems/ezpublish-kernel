<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use RuntimeException;

/**
 * Registry of External Storage Handlers (Persistence Layer).
 */
class StorageHandlerRegistry
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\StorageHandler[]
     */
    private $map;

    /**
     * Register Storage Handler.
     *
     * @param string $identifier Storage Handler identifier
     * @param \eZ\Publish\SPI\Persistence\Content\StorageHandler $storageHandler
     */
    public function register($identifier, StorageHandler $storageHandler)
    {
        $this->map[$identifier] = $storageHandler;
    }

    /**
     * Get registered Storage Handler.
     *
     * @param $identifier
     * @return \eZ\Publish\SPI\Persistence\Content\StorageHandler
     */
    public function get($identifier)
    {
        if (!isset($this->map[$identifier])) {
            throw new RuntimeException("StorageHandler '$identifier' does not exist");
        }

        return $this->map[$identifier];
    }
}
