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
     * @param array $entryPointMap
     * @param array $endpointMap
     */
    public function __construct(
        array $entryPointMap = array(),
        array $endpointMap = array()
    )
    {
        $this->entryPointMap = $entryPointMap;
        $this->endpointMap = $endpointMap;
    }

    public function getEntryPoint( $documentType )
    {
        if ( !is_array( $this->entryPointMap ) )
        {
            return $this->entryPointMap;
        }

        if ( isset( $this->entryPointMap[$documentType] ) )
        {
            return $this->entryPointMap[$documentType];
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

            return $this->endpointMap[$documentType][$languageCode];
        }

        return $this->endpointMap[$documentType];
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

            return $targets;
        }

        return array( $this->endpointMap[$documentType] );
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
            return array_values( $this->endpointMap[$documentType] );
        }

        return array( $this->endpointMap[$documentType] );
    }
}
