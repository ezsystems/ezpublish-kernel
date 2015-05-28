<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointResolver;

use eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointResolver;
use RuntimeException;

/**
 * NativeEndpointResolver provides Solr endpoints for a Content translations
 */
class NativeEndpointResolver implements EndpointResolver
{
    /**
     * Holds an array of Solr entry endpoint names
     *
     * @var string[]
     */
    private $entryEndpoints;

    /**
     * Holds a map of Endpoint names, with language codes as keys
     *
     * <code>
     *  array(
     *      "cro-HR" => "endpoint1",
     *      "eng-GB" => "endpoint2",
     *  );
     * </code>
     *
     * @var string[]
     */
    private $endpointMap;

    /**
     * Create from Endpoint names
     *
     * @param string[] $entryEndpoints
     * @param string[] $endpointMap
     */
    public function __construct( array $entryEndpoints = array(), array $endpointMap = array() )
    {
        $this->entryEndpoints = $entryEndpoints;
        $this->endpointMap = $endpointMap;
    }

    public function getEntryEndpoint()
    {
        if ( empty( $this->entryEndpoints ) )
        {
            throw new RuntimeException( "Not entry endpoints defined" );
        }

        return reset( $this->entryEndpoints );
    }

    public function getIndexingTarget( $languageCode )
    {
        if ( isset( $this->endpointMap[$languageCode] ) )
        {
            return $this->endpointMap[$languageCode];
        }

        throw new RuntimeException(
            "Language '{$languageCode}' is not mapped to Solr endpoint"
        );
    }

    public function getSearchTargets( array $languageSettings )
    {
        if (
            empty( $languageSettings ) ||
            (
                isset( $languageSettings["useAlwaysAvailable"] ) &&
                $languageSettings["useAlwaysAvailable"] === true
            )
        )
        {
            return $this->getEndpoints();
        }

        $targets = array();

        foreach ( $languageSettings["languages"] as $languageCode )
        {
            if ( !isset( $this->endpointMap[$languageCode] ) )
            {
                throw new RuntimeException(
                    "Language '{$languageCode}' is not mapped to Solr endpoint"
                );
            }

            $targets[] = $this->endpointMap[$languageCode];
        }

        if ( empty( $targets ) )
        {
            throw new RuntimeException( "No endpoints defined for given language settings" );
        }

        return $targets;
    }

    public function getEndpoints()
    {
        if ( empty( $this->endpointMap ) )
        {
            throw new RuntimeException( "No endpoints defined" );
        }

        return array_values( $this->endpointMap );
    }
}
