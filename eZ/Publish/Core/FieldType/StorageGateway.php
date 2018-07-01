<?php

/**
 * File containing the StorageGateway base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\StorageGateway as SPIStorageGateway;

/**
 * Abstract base class for storage gateways.
 *
 * @deprecated Since 6.11. Extend {@link \eZ\Publish\SPI\FieldType\StorageGateway} class instead.
 */
abstract class StorageGateway extends SPIStorageGateway
{
    /**
     * Sets the data storage connection to use.
     *
     * @deprecated Since 6.11. Set gateway connection using Dependency Injection.
     *
     * Allows injection of the data storage connection to be used from external
     * source. This can be a database connection resource or something else to
     * define the storage, depending on the gateway implementation.
     *
     * @param mixed $connection
     */
    abstract public function setConnection($connection);
}
