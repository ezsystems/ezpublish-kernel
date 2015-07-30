<?php

/**
 * File containing the StorageGateway base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType;

/**
 * Abstract base class for storage gateways.
 *
 * This base class must be extended by storage gateways to be used with a
 * {@link eZ\Publish\Core\FieldType\GatewayBasedStorage} based storage
 * implementation.
 *
 * The {@link setConnection()} method is called by the GatewayBasedStorage to
 * set the connection from the current persistence context.
 */
abstract class StorageGateway
{
    /**
     * Sets the data storage connection to use.
     *
     * Allows injection of the data storage connection to be used from external
     * source. This can be a database connection resource or something else to
     * define the storage, depending on the gateway implementation.
     *
     * @param mixed $connection
     */
    abstract public function setConnection($connection);
}
