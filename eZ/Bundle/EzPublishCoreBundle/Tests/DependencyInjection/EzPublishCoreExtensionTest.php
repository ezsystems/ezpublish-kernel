<?php
/**
 * File containing the EzPublishCoreExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Common;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Content;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;
use ReflectionObject;

class EzPublishCoreExtensionTest extends AbstractExtensionTestCase
{
    private $minimalConfig = array();

    private $siteaccessConfig = array();

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new EzPublishCoreExtension();
        $this->siteaccessConfig = array(
            'siteaccess' => array(
                'default_siteaccess' => 'ezdemo_site',
                'list' => array( 'ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin' ),
                'groups' => array(
                    'ezdemo_group' => array( 'ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin' ),
                    'ezdemo_frontend_group' => array( 'ezdemo_site', 'eng', 'fre' ),
                ),
                'match' => array(
                    'URILElement' => 1,
                    'Map\URI' => array( 'the_front' => 'ezdemo_site', 'the_back' => 'ezdemo_site_admin' )
                )
            ),
            'system' => array(
                'ezdemo_site' => array(),
                'eng' => array(),
                'fre' => array(),
                'ezdemo_site_admin' => array(
                    'legacy_mode' => true
                ),
            )
        );

        parent::setUp();
    }

    protected function getContainerExtensions()
    {
        return array( $this->extension );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( file_get_contents( __DIR__ . '/Fixtures/ezpublish_minimal_no_siteaccess.yml' ) );
    }

    public function testSiteAccessConfiguration()
    {
        // Injecting needed config parsers.
        $refExtension = new ReflectionObject( $this->extension );
        $refParser = $refExtension->getProperty( 'configParser' );
        $refParser->setAccessible( true );
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser $parser */
        $parser = $refParser->getValue( $this->extension );
        $parser->setConfigParsers( array( new Common(), new Content() ) );

        $this->load( $this->siteaccessConfig );
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.list',
            $this->siteaccessConfig['siteaccess']['list']
        );
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.default',
            $this->siteaccessConfig['siteaccess']['default_siteaccess']
        );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.groups', $this->siteaccessConfig['siteaccess']['groups'] );

        $expectedMatchingConfig = array();
        foreach ( $this->siteaccessConfig['siteaccess']['match'] as $key => $val )
        {
            // Value is expected to always be an array (transformed by semantic configuration parser).
            $expectedMatchingConfig[$key] = is_array( $val ) ? $val : array( 'value' => $val );
        }
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.match_config', $expectedMatchingConfig );

        $groupsBySiteaccess = array();
        foreach ( $this->siteaccessConfig['siteaccess']['groups'] as $groupName => $groupMembers )
        {
            foreach ( $groupMembers as $member )
            {
                if ( !isset( $groupsBySiteaccess[$member] ) )
                    $groupsBySiteaccess[$member] = array();

                $groupsBySiteaccess[$member][] = $groupName;
            }
        }
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.groups_by_siteaccess', $groupsBySiteaccess );

        $relatedSiteAccesses = array( 'ezdemo_site', 'eng', 'fre' );
        $this->assertContainerBuilderHasParameter(
            'ezpublish.siteaccess.relation_map',
            array(
                // Empty string is the default repository name
                '' => array(
                    // 2 is the default rootLocationId
                    2 => $relatedSiteAccesses
                )
            )
        );

        $this->assertContainerBuilderHasParameter( 'ezsettings.ezdemo_site.related_siteaccesses', $relatedSiteAccesses );
        $this->assertContainerBuilderHasParameter( 'ezsettings.eng.related_siteaccesses', $relatedSiteAccesses );
        $this->assertContainerBuilderHasParameter( 'ezsettings.fre.related_siteaccesses', $relatedSiteAccesses );
    }

    public function testSiteAccessNoConfiguration()
    {
        $this->load();
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.list', array( 'setup' ) );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.default', 'setup' );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.groups', array() );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.groups_by_siteaccess', array() );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.match_config', null );
    }

    public function testImageMagickConfigurationBasic()
    {
        if ( !isset( $_ENV['imagemagickConvertPath'] ) || !is_executable( $_ENV['imagemagickConvertPath'] ) )
        {
            $this->markTestSkipped( 'Missing or mis-configured Imagemagick convert path.' );
        }

        $this->load(
            array(
                'imagemagick' => array(
                    'enabled' => true,
                    'path' => $_ENV['imagemagickConvertPath']
                )
            )
        );
        $this->assertContainerBuilderHasParameter( 'ezpublish.image.imagemagick.enabled', true );
        $this->assertContainerBuilderHasParameter( 'ezpublish.image.imagemagick.executable_path', dirname( $_ENV['imagemagickConvertPath'] ) );
        $this->assertContainerBuilderHasParameter( 'ezpublish.image.imagemagick.executable', basename( $_ENV['imagemagickConvertPath'] ) );
    }

    public function testImageMagickConfigurationFilters()
    {
        if ( !isset( $_ENV['imagemagickConvertPath'] ) || !is_executable( $_ENV['imagemagickConvertPath'] ) )
        {
            $this->markTestSkipped( 'Missing or mis-configured Imagemagick convert path.' );
        }

        $customFilters = array(
            'foobar' => '-foobar',
            'wow' => '-amazing'
        );
        $this->load(
            array(
                'imagemagick' => array(
                    'enabled' => true,
                    'path' => $_ENV['imagemagickConvertPath'],
                    'filters' => $customFilters
                )
            )
        );
        $this->assertTrue( $this->container->hasParameter( 'ezpublish.image.imagemagick.filters' ) );
        $filters = $this->container->getParameter( 'ezpublish.image.imagemagick.filters' );
        $this->assertArrayHasKey( 'foobar', $filters );
        $this->assertSame( $customFilters['foobar'], $filters['foobar'] );
        $this->assertArrayHasKey( 'wow', $filters );
        $this->assertSame( $customFilters['wow'], $filters['wow'] );
    }

    public function testEzPageConfiguration()
    {
        $customLayouts = array(
            'FoobarLayout' => array( 'name' => 'Foo layout', 'template' => 'foolayout.html.twig' )
        );
        $enabledLayouts = array( 'FoobarLayout', 'GlobalZoneLayout' );
        $customBlocks = array(
            'FoobarBlock' => array( 'name' => 'Foo block' )
        );
        $enabledBlocks = array( 'FoobarBlock', 'DemoBlock' );
        $this->load(
            array(
                'ezpage' => array(
                    'layouts' => $customLayouts,
                    'blocks' => $customBlocks,
                    'enabledLayouts' => $enabledLayouts,
                    'enabledBlocks' => $enabledBlocks
                )
            )
        );

        $this->assertTrue( $this->container->hasParameter( 'ezpublish.ezpage.layouts' ) );
        $layouts = $this->container->getParameter( 'ezpublish.ezpage.layouts' );
        $this->assertArrayHasKey( 'FoobarLayout', $layouts );
        $this->assertSame( $customLayouts['FoobarLayout'], $layouts['FoobarLayout'] );
        $this->assertContainerBuilderHasParameter( 'ezpublish.ezpage.enabledLayouts', $enabledLayouts );

        $this->assertTrue( $this->container->hasParameter( 'ezpublish.ezpage.blocks' ) );
        $blocks = $this->container->getParameter( 'ezpublish.ezpage.blocks' );
        $this->assertArrayHasKey( 'FoobarBlock', $blocks );
        $this->assertSame( $customBlocks['FoobarBlock'], $blocks['FoobarBlock'] );
        $this->assertContainerBuilderHasParameter( 'ezpublish.ezpage.enabledBlocks', $enabledBlocks );
    }

    public function testRoutingConfiguration()
    {
        $this->load();
        $this->assertContainerBuilderHasAlias( 'router', 'ezpublish.chain_router' );

        $this->assertTrue( $this->container->hasParameter( 'ezpublish.default_router.non_siteaccess_aware_routes' ) );
        $nonSiteaccessAwareRoutes = $this->container->getParameter( 'ezpublish.default_router.non_siteaccess_aware_routes' );
        // See ezpublish_minimal_no_siteaccess.yml fixture
        $this->assertContains( 'foo_route', $nonSiteaccessAwareRoutes );
        $this->assertContains( 'my_prefix_', $nonSiteaccessAwareRoutes );

        $this->assertTrue( $this->container->hasParameter( 'ezpublish.default_router.legacy_aware_routes' ) );
        $legacyAwareRoutes = $this->container->getParameter( 'ezpublish.default_router.legacy_aware_routes' );
        $this->assertContains( 'legacy_foo_route', $legacyAwareRoutes );
        $this->assertContains( 'my_prefix_', $legacyAwareRoutes );
    }

    /**
     * @dataProvider cacheConfigurationProvider
     *
     * @param array $customCacheConfig
     * @param string $expectedPurgeService
     * @param int $expectedTimeout
     */
    public function testCacheConfiguration( array $customCacheConfig, $expectedPurgeService )
    {
        $this->load( $customCacheConfig );

        $this->assertContainerBuilderHasAlias( 'ezpublish.http_cache.purge_client', $expectedPurgeService );
    }

    public function cacheConfigurationProvider()
    {
        return array(
            array( array(), 'ezpublish.http_cache.purge_client.local', 1 ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'local' )
                ),
                'ezpublish.http_cache.purge_client.local',
            ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'multiple_http' )
                ),
                'ezpublish.http_cache.purge_client.fos',
            ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'single_http' )
                ),
                'ezpublish.http_cache.purge_client.fos',
            ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'http' )
                ),
                'ezpublish.http_cache.purge_client.fos',
            ),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCacheConfigurationWrongPurgeType()
    {
        $this->load(
            array(
                'http_cache' => array( 'purge_type' => 'foobar', 'timeout' => 12 )
            )
        );
    }

    public function testCacheConfigurationCustomPurgeService()
    {
        $serviceId = 'foobar';
        $this->setDefinition( $serviceId, new Definition() );
        $this->load(
            array(
                'http_cache' => array( 'purge_type' => 'foobar', 'timeout' => 12 )
            )
        );
    }

    public function testLocaleConfiguration()
    {
        $this->load( array( 'locale_conversion' => array( 'foo' => 'bar' ) ) );
        $conversionMap = $this->container->getParameter( 'ezpublish.locale.conversion_map' );
        $this->assertArrayHasKey( 'foo', $conversionMap );
        $this->assertSame( 'bar', $conversionMap['foo'] );
    }

    public function testRepositoriesConfiguration()
    {
        $repositories = array(
            'main' => array( 'engine' => 'legacy', 'connection' => 'default' ),
            'foo' => array( 'engine' => 'bar', 'connection' => 'blabla' ),
        );
        $this->load( array( 'repositories' => $repositories ) );
        $this->assertTrue( $this->container->hasParameter( 'ezpublish.repositories' ) );

        foreach ( $repositories as &$repositoryConfig )
        {
            $repositoryConfig['config'] = array();
        }
        $this->assertSame( $repositories, $this->container->getParameter( 'ezpublish.repositories' ) );
    }

    public function testRelatedSiteAccesses()
    {
        $mainRepo = 'main';
        $fooRepo = 'foo';
        $rootLocationId1 = 123;
        $rootLocationId2 = 456;
        $rootLocationId3 = 2;
        $config = array(
            'siteaccess' => array(
                'default_siteaccess' => 'ezdemo_site',
                'list' => array( 'ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin', 'ezdemo_site2', 'eng2', 'ezdemo_site3', 'fre3' ),
                'groups' => array(
                    'ezdemo_group' => array( 'ezdemo_site', 'eng', 'fre', 'ezdemo_site_admin' ),
                    'ezdemo_frontend_group' => array( 'ezdemo_site', 'eng', 'fre' ),
                    'ezdemo_group2' => array( 'ezdemo_site2', 'eng2' ),
                    'ezdemo_group3' => array( 'ezdemo_site3', 'fre3' ),
                ),
                'match' => array()
            ),
            'repositories' => array(
                $mainRepo => array( 'engine' => 'legacy', 'connection' => 'default' ),
                $fooRepo => array( 'engine' => 'bar', 'connection' => 'blabla' ),
            ),
            'system' => array(
                'ezdemo_group' => array(
                    'repository' => $mainRepo,
                    'content' => array(
                        'tree_root' => array( 'location_id' => $rootLocationId1 )
                    )
                ),
                'ezdemo_group2' => array(
                    'repository' => $mainRepo,
                    'content' => array(
                        'tree_root' => array( 'location_id' => $rootLocationId2 )
                    )
                ),
                'ezdemo_group3' => array(
                    'repository' => $fooRepo,
                ),
                'ezdemo_site_admin' => array( 'legacy_mode' => true )
            )
        ) + $this->siteaccessConfig;

        // Injecting needed config parsers.
        $refExtension = new ReflectionObject( $this->extension );
        $refParser = $refExtension->getProperty( 'configParser' );
        $refParser->setAccessible( true );
        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser $parser */
        $parser = $refParser->getValue( $this->extension );
        $parser->setConfigParsers( array( new Common(), new Content() ) );

        $this->load( $config );

        $relatedSiteAccesses1 = array( 'ezdemo_site', 'eng', 'fre' );
        $relatedSiteAccesses2 = array( 'ezdemo_site2', 'eng2' );
        $relatedSiteAccesses3 = array( 'ezdemo_site3', 'fre3' );
        $expectedRelationMap = array(
            $mainRepo => array(
                $rootLocationId1 => $relatedSiteAccesses1,
                $rootLocationId2 => $relatedSiteAccesses2
            ),
            $fooRepo => array(
                $rootLocationId3 => $relatedSiteAccesses3
            )
        );
        $this->assertContainerBuilderHasParameter( 'ezpublish.siteaccess.relation_map', $expectedRelationMap );

        $this->assertContainerBuilderHasParameter( 'ezsettings.ezdemo_site.related_siteaccesses', $relatedSiteAccesses1 );
        $this->assertContainerBuilderHasParameter( 'ezsettings.eng.related_siteaccesses', $relatedSiteAccesses1 );
        $this->assertContainerBuilderHasParameter( 'ezsettings.fre.related_siteaccesses', $relatedSiteAccesses1 );

        $this->assertContainerBuilderHasParameter( 'ezsettings.ezdemo_site2.related_siteaccesses', $relatedSiteAccesses2 );
        $this->assertContainerBuilderHasParameter( 'ezsettings.eng2.related_siteaccesses', $relatedSiteAccesses2 );

        $this->assertContainerBuilderHasParameter( 'ezsettings.ezdemo_site3.related_siteaccesses', $relatedSiteAccesses3 );
        $this->assertContainerBuilderHasParameter( 'ezsettings.fre3.related_siteaccesses', $relatedSiteAccesses3 );
    }
}
