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
 */
class TranslationEndpointProvider implements EndpointProvider
{
    /**
     * @var array
     */
    protected $endpointMap;

    public function __construct()
    {
        $this->endpointMap = array(
            "eng-GB" => "http://localhost:8983/solr/core0",
            "eng-US" => "http://localhost:8983/solr/core1",
            "por-PT" => "http://localhost:8983/solr/core2",
            "ger-DE" => "http://localhost:8983/solr/core3",
        );
    }

    public function getEntryPoint()
    {
        return $this->endpointMap["eng-GB"];
    }

    public function getIndexingTarget( $languageCode )
    {
        if ( !isset( $this->endpointMap[$languageCode] ) )
        {
            throw new RuntimeException(
                "Language '{$languageCode}' is not mapped to Solr core endpoint"
            );
        }

        return $this->endpointMap[$languageCode];
    }

    public function getSearchTargets( array $languageSettings )
    {
        if ( empty( $languageSettings ) )
        {
            return $this->getAllEndpoints();
        }

        if ( isset( $languageSettings["useAlwaysAvailable"] ) && $languageSettings["useAlwaysAvailable"] === true )
        {
            return $this->getAllEndpoints();
        }

        if ( !isset( $languageSettings["languages"] ) || empty( $languageSettings["languages"] ) )
        {
            return $this->getAllEndpoints();
        }

        $targets = array();

        foreach ( $languageSettings["languages"] as $languageCode )
        {
            $targets[] = $this->endpointMap[$languageCode];
        }

        return $targets;
    }

    public function getAllEndpoints()
    {
        return array_values( $this->endpointMap );
    }
}
