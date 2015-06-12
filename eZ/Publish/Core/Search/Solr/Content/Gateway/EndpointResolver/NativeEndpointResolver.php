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
     * Holds a map of translations to Endpoint names, with language code as key
     * and Endpoint name as value.
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
     * Holds a name of the default Endpoint used for translations, if configured
     *
     * @var null|string
     */
    private $defaultEndpoint;

    /**
     * Holds a name of the Endpoint used for always available translations, if configured
     *
     * @var null|string
     */
    private $alwaysAvailableEndpoint;

    /**
     * Create from Endpoint names
     *
     * @param string[] $entryEndpoints
     * @param string[] $endpointMap
     * @param null|string $defaultEndpoint
     * @param null|string $alwaysAvailableEndpoint
     */
    public function __construct(
        array $entryEndpoints = array(),
        array $endpointMap = array(),
        $defaultEndpoint = null,
        $alwaysAvailableEndpoint = null
    )
    {
        $this->entryEndpoints = $entryEndpoints;
        $this->endpointMap = $endpointMap;
        $this->defaultEndpoint = $defaultEndpoint;
        $this->alwaysAvailableEndpoint = $alwaysAvailableEndpoint;
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

        if ( isset( $this->defaultEndpoint ) )
        {
            return $this->defaultEndpoint;
        }

        throw new RuntimeException(
            "Language '{$languageCode}' is not mapped to Solr endpoint"
        );
    }

    public function getAlwaysAvailableEndpoint()
    {
        return $this->alwaysAvailableEndpoint;
    }

    public function getSearchTargets( array $languageSettings )
    {
        $useAlwaysAvailable = (
            isset( $languageSettings["useAlwaysAvailable"] ) &&
            $languageSettings["useAlwaysAvailable"] === true
        );

        if ( empty( $languageSettings ) || ( $useAlwaysAvailable && !isset( $this->alwaysAvailableEndpoint ) ) )
        {
            $mappedEndpointSet = $this->getMappedEndpointSet();

            if ( empty( $mappedEndpointSet ) )
            {
                throw new RuntimeException( "No endpoints defined for given language settings" );
            }

            return array_keys( $mappedEndpointSet );
        }

        $targetSet = array();

        if ( isset( $languageSettings["languages"] ) )
        {
            foreach ( $languageSettings["languages"] as $languageCode )
            {
                if ( isset( $this->endpointMap[$languageCode] ) )
                {
                    $targetSet[$this->endpointMap[$languageCode]] = true;
                }
                else if ( isset( $this->defaultEndpoint ) )
                {
                    $targetSet[$this->defaultEndpoint] = true;
                }
                else
                {
                    throw new RuntimeException(
                        "Language '{$languageCode}' is not mapped to Solr endpoint"
                    );
                }
            }
        }

        if ( $useAlwaysAvailable && isset( $this->alwaysAvailableEndpoint ) )
        {
            $targetSet[$this->alwaysAvailableEndpoint] = true;
        }

        if ( empty( $targetSet ) )
        {
            throw new RuntimeException( "No endpoints defined for given language settings" );
        }

        return array_keys( $targetSet );
    }

    public function getEndpoints()
    {
        $endpointSet = $this->getMappedEndpointSet();

        if ( isset( $this->alwaysAvailableEndpoint ) )
        {
            $endpointSet[$this->alwaysAvailableEndpoint] = true;
        }

        if ( empty( $endpointSet ) )
        {
            throw new RuntimeException( "No endpoints defined" );
        }

        return array_keys( $endpointSet );
    }

    /**
     * Returns the set of all mapped endpoints
     *
     * Includes default and excludes always available endpoint
     *
     * @return string[]
     */
    private function getMappedEndpointSet()
    {
        $endpointSet = array_flip( $this->endpointMap );

        if ( isset( $this->defaultEndpoint ) )
        {
            $endpointSet[$this->defaultEndpoint] = true;
        }

        return $endpointSet;
    }
}
