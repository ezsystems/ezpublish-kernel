<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointProvider;

use eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointProvider;
use eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointRegistry;
use RuntimeException;

/**
 * TranslationEndpointProvider provides Solr endpoints for a Content translations
 */
class TranslationEndpointProvider implements EndpointProvider
{
    /**
     * Endpoint registry service
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointRegistry
     */
    protected $endpointRegistry;

    /**
     * Holds a map of Solr entry points
     *
     * @var array
     */
    protected $entryPointMap;

    /**
     * Holds a map of Solr endpoints
     *
     * @var array
     */
    protected $endpointMap;

    /**
     * Create from registry and mapping configuration
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointRegistry
     * @param array $entryPointMap
     * @param array $endpointMap
     */
    public function __construct(
        EndpointRegistry $endpointRegistry,
        array $entryPointMap = array(),
        array $endpointMap = array()
    )
    {
        $this->endpointRegistry = $endpointRegistry;
        $this->entryPointMap = $entryPointMap;
        $this->endpointMap = $endpointMap;
    }

    public function getEntryPoint( $documentType )
    {
        if ( !is_array( $this->entryPointMap ) )
        {
            return $this->endpointRegistry->getEndpoint( $this->entryPointMap );
        }

        if ( isset( $this->entryPointMap[$documentType] ) )
        {
            return $this->endpointRegistry->getEndpoint(
                $this->entryPointMap[$documentType]
            );
        }

        throw new RuntimeException(
            "Document type '{$documentType}' is not mapped to Solr core entry point"
        );
    }

    public function getIndexingTarget( $documentType, $languageCode )
    {
        if ( is_array( $this->endpointMap[$documentType] ) )
        {
            if ( !isset( $this->endpointMap[$documentType][$languageCode] ) )
            {
                throw new RuntimeException(
                    "Language '{$languageCode}' is not mapped to Solr core endpoint"
                );
            }

            return $this->endpointRegistry->getEndpoint(
                $this->endpointMap[$documentType][$languageCode]
            );
        }

        return $this->endpointRegistry->getEndpoint(
            $this->endpointMap[$documentType]
        );
    }

    public function getSearchTargets( $documentType, array $languageSettings )
    {
        if (
            empty( $languageSettings ) ||
            ( isset( $languageSettings["useAlwaysAvailable"] ) && $languageSettings["useAlwaysAvailable"] === true ) ||
            ( !isset( $languageSettings["languages"] ) || empty( $languageSettings["languages"] ) )
        )
        {
            return $this->getAllEndpoints( $documentType );
        }

        if ( !isset( $this->endpointMap[$documentType] ) )
        {
            throw new RuntimeException(
                "Document type '{$documentType}' is not mapped to Solr core endpoint(s)"
            );
        }

        if ( is_array( $this->endpointMap[$documentType] ) )
        {
            $targets = array();

            foreach ( $languageSettings["languages"] as $languageCode )
            {
                if ( !isset( $this->endpointMap[$documentType][$languageCode] ) )
                {
                    throw new RuntimeException(
                        "Language '{$languageCode}' is not mapped to Solr core endpoint"
                    );
                }

                $targets[] = $this->endpointMap[$documentType][$languageCode];
            }

            return $this->getEndpoints( $targets );
        }

        return array(
            $this->endpointRegistry->getEndpoint(
                $this->endpointMap[$documentType]
            )
        );
    }

    public function getAllEndpoints( $documentType )
    {
        if ( !isset( $this->endpointMap[$documentType] ) )
        {
            throw new RuntimeException(
                "Document type '{$documentType}' is not mapped to Solr core endpoint(s)"
            );
        }

        if ( is_array( $this->endpointMap[$documentType] ) )
        {
            return $this->getEndpoints(
                array_values( $this->endpointMap[$documentType] )
            );
        }

        return array(
            $this->endpointRegistry->getEndpoint(
                $this->endpointMap[$documentType]
            )
        );
    }

    /**
     * Returns an array of Endpoints for the given array of Endpoint identifiers
     *
     * @param array $identifiers
     *
     * @return \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint[]
     */
    protected function getEndpoints( array $identifiers )
    {
        $endpoints = array();

        foreach ( $identifiers as $identifier )
        {
            $endpoints[] = $this->endpointRegistry->getEndpoint( $identifier );
        }

        return $endpoints;
    }
}
