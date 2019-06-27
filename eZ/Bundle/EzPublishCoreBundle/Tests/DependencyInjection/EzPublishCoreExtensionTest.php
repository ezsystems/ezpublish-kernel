<?php

/**
 * File containing the EzPublishCoreExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Common;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Content;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\StubPolicyProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use ReflectionObject;

class EzPublishCoreExtensionTest extends AbstractExtensionTestCase
{
    private $minimalConfig = [];

    private $siteaccessConfig = [];

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension */
    private $extension;

    /**
     * Cached RichText default settings.
     *
     * @var array
     */
    private static $richTextDefaultSettings;

    protected function setUp()
    {
        $this->extension = new EzPublishCoreExtension();
        $this->siteaccessConfig = [
            'siteaccess' => [
                'default_siteaccess' => 'ezdemo_site',
                'list' => ['ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin'],
                'groups' => [
                    'ezdemo_group' => ['ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin'],
                    'ezdemo_frontend_group' => ['ezdemo_site', 'eng', 'fre'],
                ],
                'match' => [
                    'URILElement' => 1,
                    'Map\URI' => ['the_front' => 'ezdemo_site', 'the_back' => 'ezdemo_site_admin'],
                ],
            ],
            'system' => [
                'ezdemo_site' => [],
                'eng' => [],
                'fre' => [],
                'ezdemo_site_admin' => [],
            ],
        ];

        parent::setUp();
    }

    protected function getContainerExtensions()
    {
        return [$this->extension];
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/ezpublish_minimal_no_siteaccess.yml'));
    }

    public function testSiteAccessConfiguration()
    {
        // Injecting needed config parsers.
        $refExtension = new ReflectionObject($this->extension);
        $refMethod = $refExtension->getMethod('getMainConfigParser');
        $refMethod->setAccessible(true);
        $refMethod->invoke($this->extension);
        $refParser = $refExtension->getProperty('mainConfigParser');
        $refParser->setAccessible(true);
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser $parser */
        $parser = $refParser->getValue($this->extension);
        $parser->setConfigParsers([new Common(), new Content()]);

        $this->load($this->siteaccessConfig);
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.list',
            $this->siteaccessConfig['siteaccess']['list']
        );
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.default',
            $this->siteaccessConfig['siteaccess']['default_siteaccess']
        );
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.groups', $this->siteaccessConfig['siteaccess']['groups']);

        $expectedMatchingConfig = [];
        foreach ($this->siteaccessConfig['siteaccess']['match'] as $key => $val) {
            // Value is expected to always be an array (transformed by semantic configuration parser).
            $expectedMatchingConfig[$key] = is_array($val) ? $val : ['value' => $val];
        }
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.match_config', $expectedMatchingConfig);

        $groupsBySiteaccess = [];
        foreach ($this->siteaccessConfig['siteaccess']['groups'] as $groupName => $groupMembers) {
            foreach ($groupMembers as $member) {
                if (!isset($groupsBySiteaccess[$member])) {
                    $groupsBySiteaccess[$member] = [];
                }

                $groupsBySiteaccess[$member][] = $groupName;
            }
        }
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.groups_by_siteaccess', $groupsBySiteaccess);

        $relatedSiteAccesses = ['ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin'];
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.relation_map',
            [
                // Empty string is the default repository name
                '' => [
                    // 2 is the default rootLocationId
                    2 => $relatedSiteAccesses,
                ],
            ]
        );

        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.related_siteaccesses', $relatedSiteAccesses);
        $this->assertContainerBuilderHasParameter('ezsettings.eng.related_siteaccesses', $relatedSiteAccesses);
        $this->assertContainerBuilderHasParameter('ezsettings.fre.related_siteaccesses', $relatedSiteAccesses);
    }

    public function testSiteAccessNoConfiguration()
    {
        $this->load();
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.list', ['setup']);
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.default', 'setup');
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.groups', []);
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.groups_by_siteaccess', []);
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.match_config', null);
    }

    public function testLoadWithoutRichTextPackage()
    {
        $this->load();

        $expectedParameters = $this->loadRichTextDefaultSettings()['parameters'];
        foreach ($expectedParameters as $parameterName => $parameterValue) {
            $this->assertContainerBuilderHasParameter($parameterName, $parameterValue);
        }

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'ezpublish.fieldType.ezrichtext',
            'ezpublish.fieldType',
            ['alias' => 'ezrichtext']
        );

        $this->testRichTextConfiguration();
    }

    public function testLoadWithRichTextPackage()
    {
        // mock existence of RichText package
        $this->container->setParameter('kernel.bundles', ['EzPlatformRichTextBundle' => null]);

        $this->load();

        $unexpectedParameters = $this->loadRichTextDefaultSettings()['parameters'];
        foreach ($unexpectedParameters as $parameterName => $parameterValue) {
            self::assertFalse(
                $this->container->hasParameter($parameterName),
                "Container has '{$parameterName}' parameter"
            );
        }

        $this->assertContainerBuilderNotHasService('ezpublish.fieldType.ezrichtext');

        $this->testRichTextConfiguration();
    }

    public function testImageMagickConfigurationBasic()
    {
        if (!isset($_ENV['imagemagickConvertPath']) || !is_executable($_ENV['imagemagickConvertPath'])) {
            $this->markTestSkipped('Missing or mis-configured Imagemagick convert path.');
        }

        $this->load(
            [
                'imagemagick' => [
                    'enabled' => true,
                    'path' => $_ENV['imagemagickConvertPath'],
                ],
            ]
        );
        $this->assertContainerBuilderHasParameter('ezpublish.image.imagemagick.enabled', true);
        $this->assertContainerBuilderHasParameter('ezpublish.image.imagemagick.executable_path', dirname($_ENV['imagemagickConvertPath']));
        $this->assertContainerBuilderHasParameter('ezpublish.image.imagemagick.executable', basename($_ENV['imagemagickConvertPath']));
    }

    public function testImageMagickConfigurationFilters()
    {
        if (!isset($_ENV['imagemagickConvertPath']) || !is_executable($_ENV['imagemagickConvertPath'])) {
            $this->markTestSkipped('Missing or mis-configured Imagemagick convert path.');
        }

        $customFilters = [
            'foobar' => '-foobar',
            'wow' => '-amazing',
        ];
        $this->load(
            [
                'imagemagick' => [
                    'enabled' => true,
                    'path' => $_ENV['imagemagickConvertPath'],
                    'filters' => $customFilters,
                ],
            ]
        );
        $this->assertTrue($this->container->hasParameter('ezpublish.image.imagemagick.filters'));
        $filters = $this->container->getParameter('ezpublish.image.imagemagick.filters');
        $this->assertArrayHasKey('foobar', $filters);
        $this->assertSame($customFilters['foobar'], $filters['foobar']);
        $this->assertArrayHasKey('wow', $filters);
        $this->assertSame($customFilters['wow'], $filters['wow']);
    }

    public function testImagePlaceholderConfiguration()
    {
        $this->load([
            'image_placeholder' => [
                'default' => [
                    'provider' => 'generic',
                    'options' => [
                        'foo' => 'Foo',
                        'bar' => 'Bar',
                    ],
                ],
                'fancy' => [
                    'provider' => 'remote',
                ],
            ],
        ]);

        $this->assertEquals([
            'default' => [
                'provider' => 'generic',
                'options' => [
                    'foo' => 'Foo',
                    'bar' => 'Bar',
                ],
            ],
            'fancy' => [
                'provider' => 'remote',
                'options' => [],
            ],
        ], $this->container->getParameter('image_alias.placeholder_providers'));
    }

    public function testEzPageConfiguration()
    {
        $customLayouts = [
            'FoobarLayout' => ['name' => 'Foo layout', 'template' => 'foolayout.html.twig'],
        ];
        $enabledLayouts = ['FoobarLayout', 'GlobalZoneLayout'];
        $customBlocks = [
            'FoobarBlock' => ['name' => 'Foo block'],
        ];
        $enabledBlocks = ['FoobarBlock', 'DemoBlock'];
        $this->load(
            [
                'ezpage' => [
                    'layouts' => $customLayouts,
                    'blocks' => $customBlocks,
                    'enabledLayouts' => $enabledLayouts,
                    'enabledBlocks' => $enabledBlocks,
                ],
            ]
        );

        $this->assertTrue($this->container->hasParameter('ezpublish.ezpage.layouts'));
        $layouts = $this->container->getParameter('ezpublish.ezpage.layouts');
        $this->assertArrayHasKey('FoobarLayout', $layouts);
        $this->assertSame($customLayouts['FoobarLayout'], $layouts['FoobarLayout']);
        $this->assertContainerBuilderHasParameter('ezpublish.ezpage.enabledLayouts', $enabledLayouts);

        $this->assertTrue($this->container->hasParameter('ezpublish.ezpage.blocks'));
        $blocks = $this->container->getParameter('ezpublish.ezpage.blocks');
        $this->assertArrayHasKey('FoobarBlock', $blocks);
        $this->assertSame($customBlocks['FoobarBlock'], $blocks['FoobarBlock']);
        $this->assertContainerBuilderHasParameter('ezpublish.ezpage.enabledBlocks', $enabledBlocks);
    }

    public function testRoutingConfiguration()
    {
        $this->load();
        $this->assertContainerBuilderHasAlias('router', 'ezpublish.chain_router');

        $this->assertTrue($this->container->hasParameter('ezpublish.default_router.non_siteaccess_aware_routes'));
        $nonSiteaccessAwareRoutes = $this->container->getParameter('ezpublish.default_router.non_siteaccess_aware_routes');
        // See ezpublish_minimal_no_siteaccess.yml fixture
        $this->assertContains('foo_route', $nonSiteaccessAwareRoutes);
        $this->assertContains('my_prefix_', $nonSiteaccessAwareRoutes);
    }

    /**
     * @dataProvider cacheConfigurationProvider
     *
     * @param array $customCacheConfig
     * @param string $expectedPurgeType
     */
    public function testCacheConfiguration(array $customCacheConfig, $expectedPurgeType)
    {
        $this->load($customCacheConfig);

        $this->assertContainerBuilderHasParameter('ezpublish.http_cache.purge_type', $expectedPurgeType);
    }

    public function cacheConfigurationProvider()
    {
        return [
            [[], 'local'],
            [
                [
                    'http_cache' => ['purge_type' => 'local'],
                ],
                'local',
            ],
            [
                [
                    'http_cache' => ['purge_type' => 'multiple_http'],
                ],
                'http',
            ],
            [
                [
                    'http_cache' => ['purge_type' => 'single_http'],
                ],
                'http',
            ],
            [
                [
                    'http_cache' => ['purge_type' => 'http'],
                ],
                'http',
            ],
        ];
    }

    public function testCacheConfigurationCustomPurgeService()
    {
        $serviceId = 'foobar';
        $this->setDefinition($serviceId, new Definition());
        $this->load(
            [
                'http_cache' => ['purge_type' => 'foobar', 'timeout' => 12],
            ]
        );

        $this->assertContainerBuilderHasParameter('ezpublish.http_cache.purge_type', 'foobar');
    }

    public function testLocaleConfiguration()
    {
        $this->load(['locale_conversion' => ['foo' => 'bar']]);
        $conversionMap = $this->container->getParameter('ezpublish.locale.conversion_map');
        $this->assertArrayHasKey('foo', $conversionMap);
        $this->assertSame('bar', $conversionMap['foo']);
    }

    public function testRepositoriesConfiguration()
    {
        $repositories = [
            'main' => [
                'storage' => [
                    'engine' => 'legacy',
                    'connection' => 'default',
                ],
                'search' => [
                    'engine' => 'elasticsearch',
                    'connection' => 'blabla',
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
            'foo' => [
                'storage' => [
                    'engine' => 'sqlng',
                    'connection' => 'default',
                ],
                'search' => [
                    'engine' => 'solr',
                    'connection' => 'lalala',
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        foreach ($repositories as &$repositoryConfig) {
            $repositoryConfig['storage']['config'] = [];
            $repositoryConfig['search']['config'] = [];
        }
        $this->assertSame($repositories, $this->container->getParameter('ezpublish.repositories'));
    }

    /**
     * @dataProvider repositoriesConfigurationFieldGroupsProvider
     */
    public function testRepositoriesConfigurationFieldGroups($repositories, $expectedRepositories)
    {
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $repositoriesPar = $this->container->getParameter('ezpublish.repositories');
        $this->assertEquals(count($repositories), count($repositoriesPar));

        foreach ($repositoriesPar as $key => $repo) {
            $this->assertArrayHasKey($key, $expectedRepositories);
            $this->assertArrayHasKey('fields_groups', $repo);
            $this->assertEquals($expectedRepositories[$key]['fields_groups'], $repo['fields_groups'], 'Invalid fields groups element', 0.0, 10, true);
        }
    }

    public function repositoriesConfigurationFieldGroupsProvider()
    {
        return [
            //empty config
            [
                ['main' => null],
                ['main' => [
                        'fields_groups' => [
                            'list' => ['content', 'metadata'],
                            'default' => '%ezsettings.default.content.field_groups.default%',
                        ],
                    ],
                ],
            ],
            //single item with custom fields
            [
                ['foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john'],
                            'default' => 'bar',
                        ],
                    ],
                ],
                ['foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john'],
                            'default' => 'bar',
                        ],
                    ],
                ],
            ],
            //mixed item with custom config and empty item
            [
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john', 'doe'],
                            'default' => 'bar',
                        ],
                    ],
                    'anotherone' => null,
                ],
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john', 'doe'],
                            'default' => 'bar',
                        ],
                    ],
                    'anotherone' => [
                        'fields_groups' => [
                            'list' => ['content', 'metadata'],
                            'default' => '%ezsettings.default.content.field_groups.default%',
                        ],
                    ],
                ],
            ],
            //items with only one field configured
            [
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john'],
                        ],
                    ],
                    'bar' => [
                        'fields_groups' => [
                            'default' => 'metadata',
                        ],
                    ],
                ],
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john'],
                            'default' => '%ezsettings.default.content.field_groups.default%',
                        ],
                    ],
                    'bar' => [
                        'fields_groups' => [
                            'list' => ['content', 'metadata'],
                            'default' => 'metadata',
                        ],
                    ],
                ],
            ],
            //two different repositories
            [
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john', 'doe'],
                            'default' => 'bar',
                        ],
                    ],
                    'bar' => [
                        'fields_groups' => [
                            'list' => ['lorem', 'ipsum'],
                            'default' => 'lorem',
                        ],
                    ],
                ],
                [
                    'foo' => [
                        'fields_groups' => [
                            'list' => ['bar', 'baz', 'john', 'doe'],
                            'default' => 'bar',
                        ],
                    ],
                    'bar' => [
                        'fields_groups' => [
                            'list' => ['lorem', 'ipsum'],
                            'default' => 'lorem',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testRepositoriesConfigurationEmpty()
    {
        $repositories = [
            'main' => null,
        ];
        $expectedRepositories = [
            'main' => [
                'storage' => [
                    'engine' => '%ezpublish.api.storage_engine.default%',
                    'connection' => null,
                    'config' => [],
                ],
                'search' => [
                    'engine' => '%ezpublish.api.search_engine.default%',
                    'connection' => null,
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $this->assertSame(
            $expectedRepositories,
            $this->container->getParameter('ezpublish.repositories')
        );
    }

    public function testRepositoriesConfigurationStorageEmpty()
    {
        $repositories = [
            'main' => [
                'search' => [
                    'engine' => 'fantasticfind',
                    'connection' => 'french',
                ],
            ],
        ];
        $expectedRepositories = [
            'main' => [
                'search' => [
                    'engine' => 'fantasticfind',
                    'connection' => 'french',
                    'config' => [],
                ],
                'storage' => [
                    'engine' => '%ezpublish.api.storage_engine.default%',
                    'connection' => null,
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $this->assertSame(
            $expectedRepositories,
            $this->container->getParameter('ezpublish.repositories')
        );
    }

    public function testRepositoriesConfigurationSearchEmpty()
    {
        $repositories = [
            'main' => [
                'storage' => [
                    'engine' => 'persistentprudence',
                    'connection' => 'yes',
                ],
            ],
        ];
        $expectedRepositories = [
            'main' => [
                'storage' => [
                    'engine' => 'persistentprudence',
                    'connection' => 'yes',
                    'config' => [],
                ],
                'search' => [
                    'engine' => '%ezpublish.api.search_engine.default%',
                    'connection' => null,
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $this->assertSame(
            $expectedRepositories,
            $this->container->getParameter('ezpublish.repositories')
        );
    }

    public function testRepositoriesConfigurationCompatibility()
    {
        $repositories = [
            'main' => [
                'engine' => 'legacy',
                'connection' => 'default',
                'search' => [
                    'engine' => 'elasticsearch',
                    'connection' => 'blabla',
                ],
            ],
            'foo' => [
                'engine' => 'sqlng',
                'connection' => 'default',
                'search' => [
                    'engine' => 'solr',
                    'connection' => 'lalala',
                ],
            ],
        ];
        $expectedRepositories = [
            'main' => [
                'search' => [
                    'engine' => 'elasticsearch',
                    'connection' => 'blabla',
                    'config' => [],
                ],
                'storage' => [
                    'engine' => 'legacy',
                    'connection' => 'default',
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
            'foo' => [
                'search' => [
                    'engine' => 'solr',
                    'connection' => 'lalala',
                    'config' => [],
                ],
                'storage' => [
                    'engine' => 'sqlng',
                    'connection' => 'default',
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $this->assertSame(
            $expectedRepositories,
            $this->container->getParameter('ezpublish.repositories')
        );
    }

    public function testRepositoriesConfigurationCompatibility2()
    {
        $repositories = [
            'main' => [
                'engine' => 'legacy',
                'connection' => 'default',
            ],
        ];
        $expectedRepositories = [
            'main' => [
                'storage' => [
                    'engine' => 'legacy',
                    'connection' => 'default',
                    'config' => [],
                ],
                'search' => [
                    'engine' => '%ezpublish.api.search_engine.default%',
                    'connection' => null,
                    'config' => [],
                ],
                'fields_groups' => [
                    'list' => ['content', 'metadata'],
                    'default' => '%ezsettings.default.content.field_groups.default%',
                ],
                'options' => [
                    'default_version_archive_limit' => 5,
                ],
            ],
        ];
        $this->load(['repositories' => $repositories]);
        $this->assertTrue($this->container->hasParameter('ezpublish.repositories'));

        $this->assertSame(
            $expectedRepositories,
            $this->container->getParameter('ezpublish.repositories')
        );
    }

    public function testRelatedSiteAccesses()
    {
        $mainRepo = 'main';
        $fooRepo = 'foo';
        $rootLocationId1 = 123;
        $rootLocationId2 = 456;
        $rootLocationId3 = 2;
        $config = [
            'siteaccess' => [
                'default_siteaccess' => 'ezdemo_site',
                'list' => ['ezdemo_site', 'eng', 'fre', 'ezdemo_site2', 'eng2', 'ezdemo_site3', 'fre3'],
                'groups' => [
                    'ezdemo_group' => ['ezdemo_site', 'eng', 'fre'],
                    'ezdemo_group2' => ['ezdemo_site2', 'eng2'],
                    'ezdemo_group3' => ['ezdemo_site3', 'fre3'],
                ],
                'match' => [],
            ],
            'repositories' => [
                $mainRepo => ['engine' => 'legacy', 'connection' => 'default'],
                $fooRepo => ['engine' => 'bar', 'connection' => 'blabla'],
            ],
            'system' => [
                'ezdemo_group' => [
                    'repository' => $mainRepo,
                    'content' => [
                        'tree_root' => ['location_id' => $rootLocationId1],
                    ],
                ],
                'ezdemo_group2' => [
                    'repository' => $mainRepo,
                    'content' => [
                        'tree_root' => ['location_id' => $rootLocationId2],
                    ],
                ],
                'ezdemo_group3' => [
                    'repository' => $fooRepo,
                ],
            ],
        ] + $this->siteaccessConfig;

        // Injecting needed config parsers.
        $refExtension = new ReflectionObject($this->extension);
        $refMethod = $refExtension->getMethod('getMainConfigParser');
        $refMethod->setAccessible(true);
        $refMethod->invoke($this->extension);
        $refParser = $refExtension->getProperty('mainConfigParser');
        $refParser->setAccessible(true);
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser $parser */
        $parser = $refParser->getValue($this->extension);
        $parser->setConfigParsers([new Common(), new Content()]);

        $this->load($config);

        $relatedSiteAccesses1 = ['ezdemo_site', 'eng', 'fre'];
        $relatedSiteAccesses2 = ['ezdemo_site2', 'eng2'];
        $relatedSiteAccesses3 = ['ezdemo_site3', 'fre3'];
        $expectedRelationMap = [
            $mainRepo => [
                $rootLocationId1 => $relatedSiteAccesses1,
                $rootLocationId2 => $relatedSiteAccesses2,
            ],
            $fooRepo => [
                $rootLocationId3 => $relatedSiteAccesses3,
            ],
        ];
        $this->assertContainerBuilderHasParameter('ezpublish.siteaccess.relation_map', $expectedRelationMap);

        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.related_siteaccesses', $relatedSiteAccesses1);
        $this->assertContainerBuilderHasParameter('ezsettings.eng.related_siteaccesses', $relatedSiteAccesses1);
        $this->assertContainerBuilderHasParameter('ezsettings.fre.related_siteaccesses', $relatedSiteAccesses1);

        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site2.related_siteaccesses', $relatedSiteAccesses2);
        $this->assertContainerBuilderHasParameter('ezsettings.eng2.related_siteaccesses', $relatedSiteAccesses2);

        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site3.related_siteaccesses', $relatedSiteAccesses3);
        $this->assertContainerBuilderHasParameter('ezsettings.fre3.related_siteaccesses', $relatedSiteAccesses3);
    }

    public function testRegisteredPolicies()
    {
        $this->load();
        self::assertContainerBuilderHasParameter('ezpublish.api.role.policy_map');
        $previousPolicyMap = $this->container->getParameter('ezpublish.api.role.policy_map');

        $policies1 = [
            'custom_module' => [
                'custom_function_1' => null,
                'custom_function_2' => ['CustomLimitation'],
            ],
            'helloworld' => [
                'foo' => ['bar'],
                'baz' => null,
            ],
        ];
        $this->extension->addPolicyProvider(new StubPolicyProvider($policies1));

        $policies2 = [
            'custom_module2' => [
                'custom_function_3' => null,
                'custom_function_4' => ['CustomLimitation2', 'CustomLimitation3'],
            ],
            'helloworld' => [
                'foo' => ['additional_limitation'],
                'some' => ['thingy', 'thing', 'but', 'wait'],
            ],
        ];
        $this->extension->addPolicyProvider(new StubPolicyProvider($policies2));

        $expectedPolicies = [
            'custom_module' => [
                'custom_function_1' => [],
                'custom_function_2' => ['CustomLimitation' => true],
            ],
            'helloworld' => [
                'foo' => ['bar' => true, 'additional_limitation' => true],
                'baz' => [],
                'some' => ['thingy' => true, 'thing' => true, 'but' => true, 'wait' => true],
            ],
            'custom_module2' => [
                'custom_function_3' => [],
                'custom_function_4' => ['CustomLimitation2' => true, 'CustomLimitation3' => true],
            ],
        ];

        $this->load();
        self::assertContainerBuilderHasParameter('ezpublish.api.role.policy_map');
        $expectedPolicies = array_merge_recursive($expectedPolicies, $previousPolicyMap);
        self::assertEquals($expectedPolicies, $this->container->getParameter('ezpublish.api.role.policy_map'));
    }

    /**
     * Test RichText Semantic Configuration.
     */
    public function testRichTextConfiguration()
    {
        $config = Yaml::parseFile(__DIR__ . '/Fixtures/FieldType/RichText/ezrichtext.yml');
        $this->load($config);

        // Validate Custom Tags
        $this->assertTrue(
            $this->container->hasParameter($this->extension::RICHTEXT_CUSTOM_TAGS_PARAMETER)
        );
        $expectedCustomTagsConfig = [
            'video' => [
                'template' => 'MyBundle:FieldType/RichText/tag:video.html.twig',
                'icon' => '/bundles/mybundle/fieldtype/richtext/video.svg#video',
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'default_value' => 'abc',
                    ],
                    'width' => [
                        'type' => 'number',
                        'required' => true,
                        'default_value' => 360,
                    ],
                    'autoplay' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default_value' => null,
                    ],
                ],
            ],
            'equation' => [
                'template' => 'MyBundle:FieldType/RichText/tag:equation.html.twig',
                'icon' => '/bundles/mybundle/fieldtype/richtext/equation.svg#equation',
                'attributes' => [
                    'name' => [
                        'type' => 'string',
                        'required' => true,
                        'default_value' => 'Equation',
                    ],
                    'processor' => [
                        'type' => 'choice',
                        'required' => true,
                        'default_value' => 'latex',
                        'choices' => ['latex', 'tex'],
                    ],
                ],
            ],
        ];

        $this->assertSame(
            $expectedCustomTagsConfig,
            $this->container->getParameter($this->extension::RICHTEXT_CUSTOM_TAGS_PARAMETER)
        );
    }

    public function testUrlAliasConfiguration()
    {
        $configuration = [
            'transformation' => 'urlalias_lowercase',
            'separator' => 'dash',
            'transformation_groups' => [
                'urlalias' => [
                    'commands' => [
                        'ascii_lowercase',
                        'cyrillic_lowercase',
                    ],
                    'cleanup_method' => 'url_cleanup',
                ],
                'urlalias_compact' => [
                    'commands' => [
                        'greek_normalize',
                        'exta_lowercase',
                    ],
                    'cleanup_method' => 'compact_cleanup',
                ],
            ],
        ];
        $this->load([
            'url_alias' => [
                'slug_converter' => $configuration,
            ],
        ]);
        $parsedConfig = $this->container->getParameter('ezpublish.url_alias.slug_converter');
        $this->assertSame(
            $configuration,
            $parsedConfig
        );
    }

    /**
     * Load & cache RichText default settings.
     *
     * @return array
     */
    private function loadRichTextDefaultSettings(): array
    {
        if (null === static::$richTextDefaultSettings) {
            static::$richTextDefaultSettings = Yaml::parseFile(
                __DIR__ . '/../../Resources/config/ezrichtext_default_settings.yml'
            );
        }

        return static::$richTextDefaultSettings;
    }
}
