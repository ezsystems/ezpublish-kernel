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
use RuntimeException;

/**
 * TranslationEndpointProvider provides Solr endpoints for a Content translations
 *
 * @todo create Endpoint class
 */
class TranslationEndpointProvider implements EndpointProvider
{
    /**
     * Holds a map of Solr endpoints
     *
     * @var array
     */
    protected $endpointMap;

    /**
     * Create from configuration
     */
    public function __construct()
    {
        $this->endpointMap = array(
            self::DOCUMENT_TYPE_CONTENT => array(
                "eng-GB" => "http://localhost:8983/solr/core0",
                "eng-US" => "http://localhost:8983/solr/core1",
                "por-PT" => "http://localhost:8983/solr/core2",
                "ger-DE" => "http://localhost:8983/solr/core3",
            ),
            self::DOCUMENT_TYPE_LOCATION => array(
                "eng-GB" => "http://localhost:8983/solr/core4",
                "eng-US" => "http://localhost:8983/solr/core5",
                "por-PT" => "http://localhost:8983/solr/core6",
                "ger-DE" => "http://localhost:8983/solr/core7",
            ),
        );
    }

    /**
     *
     *
     * @param mixed $documentType
     *
     * @return string
     */
    public function getEntryPoint( $documentType )
    {
        // @todo implement real entry point selection
        if ( is_array( $this->endpointMap[$documentType] ) )
        {
            return $this->endpointMap[$documentType]["eng-GB"];
        }

        return $this->endpointMap[$documentType];
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
