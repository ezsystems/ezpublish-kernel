<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

use OutOfBoundsException;

/**
 * Registry for Solr search engine Endpoints
 */
class EndpointRegistry
{
    /**
     * Registered endpoints
     *
     * @var array(string => Endpoint)
     */
    protected $endpoint = array();

    /**
     * Construct from optional array of Endpoints
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint[] $endpoints
     */
    public function __construct( array $endpoints = array() )
    {
        foreach ( $endpoints as $name => $endpoint )
        {
            $this->registerEndpoint( $name, $endpoint );
        }
    }

    /**
     * Registers $endpoint with $name
     *
     * @param string $name
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint $endpoint
     */
    public function registerEndpoint( $name, Endpoint $endpoint )
    {
        $this->endpoint[$name] = $endpoint;
    }

    /**
     * Get Endpoint with $name
     *
     * @param string $name
     *
     * @return \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint
     */
    public function getEndpoint( $name )
    {
        if ( !isset( $this->endpoint[$name] ) )
        {
            throw new OutOfBoundsException( "No Endpoint registered for '{$name}'." );
        }

        return $this->endpoint[$name];
    }
}

