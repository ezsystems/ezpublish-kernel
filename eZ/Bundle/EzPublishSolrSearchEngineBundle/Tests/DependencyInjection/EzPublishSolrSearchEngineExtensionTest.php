<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishSolrSearchEngineBundle\DependencyInjection\EzPublishSolrSearchEngineExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Yaml\Yaml;

class EzPublishSolrSearchEngineExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new EzPublishSolrSearchEngineExtension();

        parent::setUp();
    }

    protected function getContainerExtensions()
    {
        return array($this->extension);
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse(
            file_get_contents(__DIR__ . '/Fixtures/minimal.yml')
        );
    }

    public function testEmpty()
    {
        $this->load();
    }

    public function dataProviderForTestEndpoint()
    {
        return array(
            array(
                'endpoint_dsn',
                array(
                    'dsn' => 'https://jura:pura@10.10.10.10:5434/jolr',
                    'core' => 'core0',
                ),
                array(
                    'scheme' => 'https',
                    'host' => '10.10.10.10',
                    'port' => 5434,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/jolr',
                    'core' => 'core0',
                ),
            ),
            array(
                'endpoint_standalone',
                array(
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ),
                array(
                    'scheme' => 'https',
                    'host' => '22.22.22.22',
                    'port' => 1232,
                    'user' => 'jura',
                    'pass' => 'pura',
                    'path' => '/holr',
                    'core' => 'core1',
                ),
            ),
            array(
                'endpoint_override',
                array(
                    'dsn' => 'https://miles:teg@257.258.259.400:5555/noship',
                    'scheme' => 'http',
                    'host' => 'farm.com',
                    'port' => 1234,
                    'core' => 'core2',
                    'user' => 'darwi',
                    'pass' => 'odrade',
                    'path' => '/dunr',
                ),
                array(
                    'scheme' => 'https',
                    'host' => '257.258.259.400',
                    'port' => 5555,
                    'user' => 'miles',
                    'pass' => 'teg',
                    'path' => '/noship',
                    'core' => 'core2',
                ),
            ),
            array(
                'endpoint_defaults',
                array(
                    'core' => 'core3',
                ),
                array(
                    'scheme' => 'http',
                    'host' => '127.0.0.1',
                    'port' => 8983,
                    'user' => null,
                    'pass' => null,
                    'path' => '/solr',
                    'core' => 'core3',
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderForTestEndpoint
     *
     * @param string $endpointName
     * @param array $endpointValues
     * @param array $expectedArgument
     */
    public function testEndpoint($endpointName, $endpointValues, $expectedArgument)
    {
        $this->load(array('endpoints' => array($endpointName => $endpointValues)));

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            "ez_search_engine_solr.endpoints.{$endpointName}",
            'ezpublish.search.solr.endpoint',
            array('alias' => $endpointName)
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            "ez_search_engine_solr.endpoints.{$endpointName}",
            0,
            $expectedArgument
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEndpointCoreRequired()
    {
        $this->load(
            array(
                'endpoints' => array(
                    'endpoint0' => array(
                        'dsn' => 'https://12.13.14.15:4444/solr',
                    ),
                ),
            )
        );
    }

    public function dataProviderForTestConnection()
    {
        return array(
            array(
                array(
                    'connections' => array(),
                ),
            ),
            array(
                array(
                    'connections' => array(
                        'connection1' => array(),
                    ),
                ),
            ),
            array(
                array(
                    'connections' => array(
                        'connection1' => array(
                            'entry_endpoints' => array(),
                            'cluster' => array(),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'connections' => array(
                        'connection1' => array(
                            'entry_endpoints' => array(
                                'content' => array(),
                                'location' => array(),
                            ),
                            'cluster' => array(
                                'content' => array(),
                                'location' => array(),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @param array $configurationValues
     *
     * @dataProvider dataProviderForTestConnection
     */
    public function testConnectionLoad($configurationValues)
    {
        $this->load($configurationValues);
    }

    public function testConnection()
    {
        $configurationValues = array(
            'connections' => array(
                'connection1' => array(
                    'entry_endpoints' => array(
                        'content' => array(
                            'endpoint1',
                            'endpoint2',
                        ),
                        'location' => array(
                            'endpoint2',
                        ),
                    ),
                    'cluster' => array(
                        'content' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint1',
                                'eng-GB' => 'endpoint2',
                                'gal-MW' => 'endpoint3',
                            ),
                            'default' => 'endpoint4',
                            'main_translations' => 'endpoint5',
                        ),
                        'location' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint2',
                            ),
                            'default' => 'endpoint3',
                            'main_translations' => 'endpoint4',
                        ),
                    ),
                ),
            ),
        );

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            0,
            array(
                'endpoint1',
                'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            1,
            array(
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
                'gal-MW' => 'endpoint3',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            2,
            'endpoint4'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            3,
            'endpoint5'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.content.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.content_handler.connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            0,
            array(
                'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            1,
            array(
                'cro-HR' => 'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            2,
            'endpoint3'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            3,
            'endpoint4'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.location.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.location_handler.connection1'
        );
    }

    public function testConnectionEndpointDefaults()
    {
        $configurationValues = array(
            'connections' => array(
                'connection1' => array(
                    'cluster' => array(
                        'content' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint1',
                                'eng-GB' => 'endpoint2',
                            ),
                            'default' => 'endpoint3',
                            'main_translations' => 'endpoint4',
                        ),
                        'location' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint5',
                            ),
                            'default' => 'endpoint6',
                            'main_translations' => 'endpoint7',
                        ),
                    ),
                ),
            ),
        );

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            0,
            array(
                'endpoint1',
                'endpoint2',
                'endpoint3',
                'endpoint4',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            1,
            array(
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            2,
            'endpoint3'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            3,
            'endpoint4'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.content.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.content_handler.connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            0,
            array(
                'endpoint5',
                'endpoint6',
                'endpoint7',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            1,
            array(
                'cro-HR' => 'endpoint5',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            2,
            'endpoint6'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            3,
            'endpoint7'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.location.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.location_handler.connection1'
        );
    }

    public function testConnectionEndpointUniqueDefaults()
    {
        $configurationValues = array(
            'connections' => array(
                'connection1' => array(
                    'cluster' => array(
                        'content' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint1',
                                'eng-GB' => 'endpoint2',
                            ),
                            'default' => 'endpoint2',
                            'main_translations' => 'endpoint2',
                        ),
                        'location' => array(
                            'translations' => array(
                                'cro-HR' => 'endpoint5',
                            ),
                            'default' => 'endpoint5',
                            'main_translations' => 'endpoint5',
                        ),
                    ),
                ),
            ),
        );

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            0,
            array(
                'endpoint1',
                'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            1,
            array(
                'cro-HR' => 'endpoint1',
                'eng-GB' => 'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            2,
            'endpoint2'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            3,
            'endpoint2'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.content.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.content_handler.connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            0,
            array(
                'endpoint5',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            1,
            array(
                'cro-HR' => 'endpoint5',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            2,
            'endpoint5'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            3,
            'endpoint5'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.location.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.location_handler.connection1'
        );
    }

    public function testConnectionClusterDefaults()
    {
        $configurationValues = array(
            'connections' => array(
                'connection1' => array(
                    'cluster' => 'endpoint1',
                ),
            ),
        );

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            0,
            array(
                'endpoint1',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            1,
            array()
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            2,
            'endpoint1'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.content.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.content_handler.connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            0,
            array(
                'endpoint1',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            1,
            array()
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            2,
            'endpoint1'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.location.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.location_handler.connection1'
        );
    }

    public function testConnectionClustersDefault()
    {
        $configurationValues = array(
            'connections' => array(
                'connection1' => array(
                    'cluster' => array(
                        'content' => 'endpoint1',
                        'location' => 'endpoint2',
                    ),
                ),
            ),
        );

        $this->load($configurationValues);

        $this->assertContainerBuilderHasParameter(
            'ez_search_engine_solr.default_connection',
            'connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            0,
            array(
                'endpoint1',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            1,
            array()
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            2,
            'endpoint1'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.content.connection1',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.content.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.content_handler.connection1'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            0,
            array(
                'endpoint2',
            )
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            1,
            array()
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            2,
            'endpoint2'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'ezpublish.search.solr.content.gateway.endpoint_resolver.native.location.connection1',
            3,
            null
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.search.solr.location.gateway.native.connection1'
        );
        $this->assertContainerBuilderHasService(
            'ezpublish.spi.search.solr.location_handler.connection1'
        );
    }
}
