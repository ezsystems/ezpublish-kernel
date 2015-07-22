<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Tests\Content\Gateway\EndpointResolver;

use eZ\Publish\Core\Search\Solr\Content\Gateway\EndpointResolver\NativeEndpointResolver;
use eZ\Publish\Core\Search\Solr\Tests\TestCase;
use RuntimeException;

/**
 * Test case for native endpoint resolver.
 */
class NativeEndpointResolverTest extends TestCase
{
    public function testGetEntryEndpoint()
    {
        $entryEndpoints = array(
            'endpoint2',
            'endpoint0',
            'endpoint1',
        );

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        $this->assertEquals(
            'endpoint2',
            $endpointResolver->getEntryEndpoint()
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetEntryEndpointThrowsRuntimeException()
    {
        $entryEndpoints = array();

        $endpointResolver = $this->getEndpointResolver($entryEndpoints);

        $endpointResolver->getEntryEndpoint();
    }

    public function testGetIndexingTarget()
    {
        $endpointMap = array(
            'eng-GB' => 'endpoint3',
        );

        $endpointResolver = $this->getEndpointResolver(array(), $endpointMap);

        $this->assertEquals(
            'endpoint3',
            $endpointResolver->getIndexingTarget('eng-GB')
        );
    }

    public function testGetIndexingTargetReturnsDefaultEndpoint()
    {
        $endpointMap = array();
        $defaultEndpoint = 'endpoint4';

        $endpointResolver = $this->getEndpointResolver(array(), $endpointMap, $defaultEndpoint);

        $this->assertEquals(
            'endpoint4',
            $endpointResolver->getIndexingTarget('ger-DE')
        );
    }

    public function getIndexingTargetThrowsRuntimeException()
    {
        $endpointResolver = $this->getEndpointResolver();

        $endpointResolver->getIndexingTarget('ger-DE');
    }

    public function testGetMainLanguagesEndpoint()
    {
        $mainLanguagesEndpoint = 'endpoint5';

        $endpointResolver = $this->getEndpointResolver(array(), array(), null, $mainLanguagesEndpoint);

        $this->assertEquals(
            'endpoint5',
            $endpointResolver->getMainLanguagesEndpoint()
        );
    }

    public function testGetMainLanguagesEndpointReturnsNull()
    {
        $endpointResolver = $this->getEndpointResolver();

        $this->assertNull($endpointResolver->getMainLanguagesEndpoint());
    }

    public function providerForTestGetSearchTargets()
    {
        return array(
            // Will return all endpoints (for always available fallback without main languages endpoint)
            0 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            1 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            2 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                ),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            3 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            4 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return mapped endpoints matched by languages + main languages endpoint
            5 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ),
            ),
            // Will return mapped endpoints matched by languages + main languages endpoint
            6 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ),
            ),
            // Will return mapped endpoints matched by languages + main languages endpoint
            7 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'main_languages_endpoint',
                ),
            ),
            // Will return mapped endpoints matched by languages + main languages endpoint
            8 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'main_languages_endpoint',
                ),
            ),
            // Will return mapped endpoints matched by languages
            9 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return mapped endpoints matched by languages
            10 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                ),
            ),
            // Will return mapped endpoints matched by languages
            11 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return mapped endpoints matched by languages
            12 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                ),
            ),
            // Will return mapped endpoints matched by languages
            13 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return mapped endpoints matched by languages
            14 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_de_DE',
                ),
            ),
            // Will return mapped endpoints matched by languages
            15 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_de_DE',
                ),
            ),
            // Will return mapped endpoints matched by languages
            16 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                        'ger-DE',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            17 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            18 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            19 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return all endpoints (for always available fallback without main languages endpoint)
            20 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages only)
            21 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages only)
            22 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages only)
            23 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages only)
            24 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            25 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            26 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                null,
                array(),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            27 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            28 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                null,
                array(),
                array(
                    'endpoint_en_GB',
                    'endpoint_de_DE',
                    'default_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            29 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            30 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            31 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            32 => array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                    'ger-DE' => 'endpoint_de_DE',
                ),
                null,
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            33 => array(
                array(),
                'default_endpoint',
                null,
                array(),
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                array(
                    'default_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            34 => array(
                array(),
                null,
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            35 => array(
                array(),
                'default_endpoint',
                'main_languages_endpoint',
                array(),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            36 => array(
                array(),
                'default_endpoint',
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'default_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            37 => array(
                array(),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            38 => array(
                array(),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return all endpoints (search on main languages without main languages endpoint)
            39 => array(
                array(),
                'default_endpoint',
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                // Not providing languages, but with main languages endpoint searches
                // on main languages, which needs to include only main languages endpoint
                array(
                    'default_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            40 => array(
                array(),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
            // Will return main languages endpoint (search on main languages with main languages endpoint)
            41 => array(
                array(),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                array(
                    'main_languages_endpoint',
                ),
            ),
        );
    }

    /**
     * @dataProvider providerForTestGetSearchTargets
     *
     * @param string[] $endpointMap
     * @param null|string $defaultEndpoint
     * @param null|string $mainLanguagesEndpoint
     * @param array $languageSettings
     * @param string[] $expected
     */
    public function testGetSearchTargets(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $languageSettings,
        $expected
    ) {
        $endpointResolver = $this->getEndpointResolver(
            array(),
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $actual = $endpointResolver->getSearchTargets($languageSettings);

        $this->assertEquals($expected, $actual);
    }

    public function providerForTestGetSearchTargetsThrowsRuntimeException()
    {
        return array(
            // Will try to return all endpoints
            0 => array(
                array(),
                null,
                null,
                array(),
                'No endpoints defined',
            ),
            1 => array(
                array(),
                null,
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => true,
                ),
                'No endpoints defined',
            ),
            2 => array(
                array(),
                null,
                null,
                array(
                    'languages' => array(),
                    'useAlwaysAvailable' => false,
                ),
                'No endpoints defined',
            ),
            3 => array(
                array(),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                'No endpoints defined',
            ),
            // Will try to map translation
            4 => array(
                array(),
                null,
                null,
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ),
            5 => array(
                array(),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => true,
                ),
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ),
            6 => array(
                array(),
                null,
                'main_languages_endpoint',
                array(
                    'languages' => array(
                        'eng-GB',
                    ),
                    'useAlwaysAvailable' => false,
                ),
                "Language 'eng-GB' is not mapped to Solr endpoint",
            ),
        );
    }

    /**
     * @dataProvider providerForTestGetSearchTargetsThrowsRuntimeException
     * @expectedException \RuntimeException
     *
     * @param string[] $endpointMap
     * @param null|string $defaultEndpoint
     * @param null|string $mainLanguagesEndpoint
     * @param array $languageSettings
     * @param string $message
     */
    public function testGetSearchTargetsThrowsRuntimeException(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $languageSettings,
        $message
    ) {
        $endpointResolver = $this->getEndpointResolver(
            array(),
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        try {
            $endpointResolver->getSearchTargets($languageSettings);
        } catch (RuntimeException $e) {
            $this->assertEquals($message, $e->getMessage());

            throw $e;
        }
    }

    public function providerForTestGetEndpoints()
    {
        return array(
            array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                ),
                null,
                null,
                array(
                    'endpoint_en_GB',
                ),
            ),
            array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                ),
                'default_endpoint',
                null,
                array(
                    'endpoint_en_GB',
                    'default_endpoint',
                ),
            ),
            array(
                array(
                    'eng-GB' => 'endpoint_en_GB',
                ),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'endpoint_en_GB',
                    'default_endpoint',
                    'main_languages_endpoint',
                ),
            ),
            array(
                array(),
                'default_endpoint',
                null,
                array(
                    'default_endpoint',
                ),
            ),
            array(
                array(),
                null,
                'main_languages_endpoint',
                array(
                    'main_languages_endpoint',
                ),
            ),
            array(
                array(),
                'default_endpoint',
                'main_languages_endpoint',
                array(
                    'default_endpoint',
                    'main_languages_endpoint',
                ),
            ),
        );
    }

    /**
     * @dataProvider providerForTestGetEndpoints
     *
     * @param string[] $endpointMap
     * @param null|string $defaultEndpoint
     * @param null|string $mainLanguagesEndpoint
     * @param string[] $expected
     */
    public function testGetEndpoints(
        $endpointMap,
        $defaultEndpoint,
        $mainLanguagesEndpoint,
        $expected
    ) {
        $endpointResolver = $this->getEndpointResolver(
            array(),
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );

        $endpoints = $endpointResolver->getEndpoints();

        $this->assertEquals($expected, $endpoints);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetEndpointsThrowsRuntimeException()
    {
        $endpointResolver = $this->getEndpointResolver(
            array(),
            array(),
            null,
            null
        );

        $endpointResolver->getEndpoints();
    }

    protected function getEndpointResolver(
        array $entryEndpoints = array(),
        array $endpointMap = array(),
        $defaultEndpoint = null,
        $mainLanguagesEndpoint = null
    ) {
        return new NativeEndpointResolver(
            $entryEndpoints,
            $endpointMap,
            $defaultEndpoint,
            $mainLanguagesEndpoint
        );
    }
}
