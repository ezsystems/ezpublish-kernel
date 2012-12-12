<?php
/**
 * File containing the StorageGateway base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\FieldStorage;

/**
 * Storage gateway base class to be used by FieldType storages
 *
 * This class gives a common basis to realized gateway based storage
 * dispatching. It is intended to deal as a base class for FieldType storages,
 * giving a common infrastructure to handle multiple gateways, based on the
 * context provided by the SPI.
 *
 * The method {@link getGateway()} is used in derived classes to retrieve the
 * correct gateway implementation, based on the context. The method {@link
 * getPersistenceHandler()} can be used to retrieve the SPI persistence
 * handler. It is encouraged, to use this object to retrieve any eZ Publish
 * internal data objects, in order to allow caching.
 */
abstract class GatewayBasedStorage implements FieldStorage
{
    /**
     * Gateways
     *
     * @var \eZ\Publish\Core\FieldType\StorageGateway[]
     */
    protected $gateways;

    /**
     * Construct from gateways
     *
     * @param \eZ\Publish\Core\FieldType\StorageGateway[] $gateways
     */
    public function __construct( array $gateways )
    {
        foreach ( $gateways as $identifier => $gateway )
        {
            $this->addGateway( $identifier, $gateway );
        }
    }

    /**
     * Adds a storage $gateway assigned to the given $identifier
     *
     * @param string $identifier
     * @param \eZ\Publish\Core\FieldType\StorageGateway $gateway
     *
     * @return void
     */
    public function addGateway( $identifier, StorageGateway $gateway )
    {
        $this->gateways[$identifier] = $gateway;
    }

    /**
     * Retrieve the fitting gateway, base on the identifier in $context
     *
     * @param array $context
     *
     * @return \eZ\Publish\Core\FieldType\StorageGateway
     */
    protected function getGateway( array $context )
    {
        if ( !isset( $this->gateways[$context['identifier']] ) )
        {
            throw new \OutOfBoundsException( "No gateway for ${context['identifier']} available." );
        }

        $gateway = $this->gateways[$context['identifier']];
        $gateway->setConnection( $context['connection'] );

        return $gateway;
    }
}
