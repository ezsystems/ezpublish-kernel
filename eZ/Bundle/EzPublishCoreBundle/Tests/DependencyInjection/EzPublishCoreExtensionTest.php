<?php
/**
 * File containing the EzPublishCoreExtensionTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;

class EzPublishCoreExtensionTest extends AbstractExtensionTestCase
{
    private $minimalConfig = array();

    private $siteaccessConfig = array();

    protected function setUp()
    {
        parent::setUp();
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
            )
        );
    }

    protected function getContainerExtensions()
    {
        return array( new EzPublishCoreExtension() );
    }

    protected function getMinimalConfiguration()
    {
        return $this->minimalConfig = Yaml::parse( __DIR__ . '/Fixtures/ezpublish_minimal_no_siteaccess.yml' );
    }

    public function testSiteAccessConfiguration()
    {
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
    public function testCacheConfiguration( array $customCacheConfig, $expectedPurgeService, $expectedTimeout )
    {
        $this->load( $customCacheConfig );

        $this->assertContainerBuilderHasAlias( 'ezpublish.http_cache.purge_client', $expectedPurgeService );
        $this->assertContainerBuilderHasParameter( 'ezpublish.http_cache.purge_client.http_client.timeout', $expectedTimeout );
    }

    public function cacheConfigurationProvider()
    {
        return array(
            array( array(), 'ezpublish.http_cache.purge_client.local', 1 ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'local', 'timeout' => 12 )
                ),
                'ezpublish.http_cache.purge_client.local',
                12
            ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'multiple_http', 'timeout' => 5 )
                ),
                'ezpublish.http_cache.purge_client.multi_request',
                5
            ),
            array(
                array(
                    'http_cache' => array( 'purge_type' => 'single_http', 'timeout' => 1 )
                ),
                'ezpublish.http_cache.purge_client.single_request',
                1
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
}
